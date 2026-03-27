<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Services\GhnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\Voucher;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $cart = Cart::firstOrCreate([
            'id_user' => $user->id,
        ]);

        $checkoutData = $this->resolveCheckoutItems($cart);
        $cartItems = $checkoutData['items'];

        if ($cartItems->isEmpty()) {
            return redirect()->route('client.cart.index')->with('error', 'Vui lòng chọn sản phẩm cần thanh toán.');
        }

        $addresses = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        $defaultAddress = $addresses->firstWhere('is_default', 1) ?? $addresses->first();

        $shippingFee = 0;

        if ($defaultAddress && $defaultAddress->ghn_district_id && $defaultAddress->ghn_ward_code) {
            $shippingFee = $this->calculateShippingFromCart($cartItems, $defaultAddress);
        }

        $subtotal = $this->calculateSubtotal($cartItems);
        $appliedVoucher = $this->getAppliedVoucherPreview($subtotal);
        $discount = $appliedVoucher['discount'] ?? 0;
        $grandTotal = max(0, $subtotal + $shippingFee - $discount);
        $availableVouchers = $this->getAvailableVouchersForCheckout($subtotal);

        return view('client.checkout.index', compact(
            'cartItems',
            'addresses',
            'defaultAddress',
            'shippingFee',
            'subtotal',
            'appliedVoucher',
            'discount',
            'grandTotal',
            'availableVouchers'
        ));
    }
    public function getShippingFee(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        $user = Auth::user();

        $address = Address::where('id', $request->address_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        Log::info('Shipping fee selected address', [
            'address_id' => $address->id,
            'ghn_province_id' => $address->ghn_province_id,
            'ghn_district_id' => $address->ghn_district_id,
            'ghn_ward_code' => $address->ghn_ward_code,
        ]);

        $cart = Cart::where('id_user', $user->id)->first();

        $checkoutData = $this->resolveCheckoutItems($cart);
        $cartItems = $checkoutData['items'];

        Log::info('Shipping fee checkout items', [
            'source' => $checkoutData['source'],
            'count' => $cartItems->count(),
        ]);

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có sản phẩm để tính phí vận chuyển.',
            ], 422);
        }

        $shippingFee = $this->calculateShippingFromCart($cartItems, $address);

        Log::info('Shipping fee result', [
            'shipping_fee' => $shippingFee,
        ]);

        return response()->json([
            'success' => true,
            'shipping_fee' => $shippingFee,
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_id' => 'required|exists:addresses,id',
            'note' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cod,vnpay',
        ], [
            'address_id.required' => 'Vui lòng chọn địa chỉ nhận hàng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        $user = Auth::user();

        $address = Address::where('id', $request->address_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $cart = Cart::where('id_user', $user->id)->first();

        $checkoutData = $this->resolveCheckoutItems($cart);
        $checkoutSource = $checkoutData['source'];
        $cartItems = $checkoutData['items'];

        if ($checkoutSource === 'cart' && !$cart) {
            return redirect()->back()->with('error', 'Không tìm thấy giỏ hàng.');
        }

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Không có sản phẩm để thanh toán.');
        }

        $selectedCartItemIds = $checkoutSource === 'cart' && $cart
            ? $this->getSelectedCheckoutItemIds($cart)
            : [];

        $subtotal = 0;

        foreach ($cartItems as $item) {
            $variant = $item->variant;

            if (!$variant) {
                return redirect()->back()->with('error', 'Có sản phẩm không hợp lệ.');
            }

            if ($variant->quantity < $item->quantity) {
                return redirect()->back()->with(
                    'error',
                    'Sản phẩm "' . ($variant->product->name ?? 'N/A') . '" không đủ tồn kho.'
                );
            }

            $subtotal += ((float) $variant->price * (int) $item->quantity);
        }

        $shippingFee = $this->calculateShippingFromCart($cartItems, $address);
        $fullAddress = $this->buildFullAddress($address);

        DB::beginTransaction();

        try {
            $paymentStatus = $request->payment_method === 'vnpay' ? 'pending' : 'unpaid';

            $discount = 0;
            $voucherId = null;
            $voucherCode = null;

            $sessionVoucherId = session('checkout_voucher.voucher_id');

            if ($sessionVoucherId) {
                $voucher = Voucher::lockForUpdate()->find($sessionVoucherId);

                if (!$voucher) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Voucher không còn tồn tại.');
                }

                $currentUses = $this->getUserVoucherUsage($voucher, $user->id);

                if (!$voucher->isValid($subtotal, $currentUses, 1)) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Voucher không hợp lệ hoặc đã vượt giới hạn sử dụng.');
                }

                $discount = (float) $voucher->getDiscount($subtotal);
                $discount = min($discount, $subtotal);

                $voucherId = $voucher->id;
                $voucherCode = $voucher->code;

                $voucher->decreaseQuantity();
            }

            $totalPrice = max(0, $subtotal + $shippingFee - $discount);

            $order = Order::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $user->email ?? null,
                'address' => $fullAddress,
                'note' => $request->note, // chỉ lưu được nếu DB/orders có cột note
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'order_status' => 'pending',
                'shipping_fee' => $shippingFee,
                'discount' => $discount,
                'voucher_id' => $voucherId,
                'voucher_code' => $voucherCode,
                'total_price' => $totalPrice,
                'order_code' => $this->generateOrderCode(),
            ]);

            foreach ($cartItems as $item) {
                $variant = $item->variant;
                $unitPrice = (float) $variant->price;
                $quantity = (int) $item->quantity;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product->id,
                    'variant_id' => $variant->id,
                    'product_name' => $variant->product->name ?? 'N/A',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $quantity,
                ]);
            }

            session()->forget('checkout_voucher');

            if ($request->payment_method === 'cod') {
                $this->decreaseStockFromOrder($order);

                if ($checkoutSource === 'buy_now') {
                    session()->forget('buy_now_checkout');
                } else {
                    $this->clearSelectedCartItems($cart, $selectedCartItemIds);
                    session()->forget('checkout_selected_items');
                }

                DB::commit();

                return redirect()
                    ->route('client.cart.index')
                    ->with('success', 'Đặt hàng thành công. Đơn hàng của bạn đang chờ xác nhận.');
            }

            DB::commit();

            return redirect($this->createVnpayUrl($order));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Checkout store error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tạo đơn hàng.');
        }
    }

    public function paymentReturn(Request $request)
    {
        $vnpHashSecret = config('services.vnpay.hash_secret');
        $inputData = $request->all();

        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? null;
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);

        $hashData = [];
        foreach ($inputData as $key => $value) {
            $hashData[] = urlencode($key) . '=' . urlencode($value);
        }

        $secureHash = hash_hmac('sha512', implode('&', $hashData), $vnpHashSecret);

        if ($secureHash !== $vnpSecureHash) {
            return redirect()->route('client.cart.index')->with('error', 'Chữ ký VNPay không hợp lệ.');
        }

        $orderCode = $request->vnp_TxnRef;
        $responseCode = $request->vnp_ResponseCode;
        $transactionStatus = $request->vnp_TransactionStatus;

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return redirect()->route('client.cart.index')->with('error', 'Không tìm thấy đơn hàng.');
        }

        DB::beginTransaction();

        try {
            if ($responseCode === '00' && $transactionStatus === '00') {
                if ($order->payment_status !== 'paid') {
                    $this->decreaseStockFromOrder($order);
                    $this->clearUserCartByOrder($order);

                    $order->update([
                        'payment_status' => 'paid',
                        'order_status' => 'pending',
                    ]);
                }

                DB::commit();
                session()->forget('buy_now_checkout');
                session()->forget('checkout_selected_items');

                return redirect()
                    ->route('client.cart.index')
                    ->with('success', 'Thanh toán VNPay thành công.');
            }

            $this->updateVnpayFailureState($order, $responseCode);

            DB::commit();

            return redirect()
                ->route('client.cart.index')
                ->with('error', 'Thanh toán VNPay thất bại hoặc bị hủy.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('VNPay return success process error: ' . $e->getMessage());

            return redirect()
                ->route('client.cart.index')
                ->with('error', 'Thanh toán thành công nhưng xử lý đơn hàng gặp lỗi.');
        }
    }
    public function paymentIpn(Request $request)
    {
        $vnpHashSecret = config('services.vnpay.hash_secret');
        $inputData = $request->all();

        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? null;
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);

        $hashData = [];
        foreach ($inputData as $key => $value) {
            $hashData[] = urlencode($key) . '=' . urlencode($value);
        }

        $secureHash = hash_hmac('sha512', implode('&', $hashData), $vnpHashSecret);

        if ($secureHash !== $vnpSecureHash) {
            return response()->json([
                'RspCode' => '97',
                'Message' => 'Invalid signature',
            ]);
        }

        $orderCode = $request->vnp_TxnRef;
        $responseCode = $request->vnp_ResponseCode;
        $transactionStatus = $request->vnp_TransactionStatus;

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return response()->json([
                'RspCode' => '01',
                'Message' => 'Order not found',
            ]);
        }

        DB::beginTransaction();

        try {
            if ($responseCode === '00' && $transactionStatus === '00') {
                if ($order->payment_status !== 'paid') {
                    $this->decreaseStockFromOrder($order);
                    $this->clearUserCartByOrder($order);

                    $order->update([
                        'payment_status' => 'paid',
                        'order_status' => 'pending',
                    ]);
                }
            } else {
                $this->updateVnpayFailureState($order, $responseCode);
            }

            DB::commit();

            return response()->json([
                'RspCode' => '00',
                'Message' => 'Confirm Success',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('VNPay IPN error: ' . $e->getMessage());

            return response()->json([
                'RspCode' => '99',
                'Message' => 'Unknown error',
            ]);
        }
    }
    public function reorder($id)
    {
        $user = Auth::user();

        $order = Order::with('details')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $cart = Cart::firstOrCreate([
            'id_user' => $user->id,
        ]);

        $addedLines = 0;
        $addedQty = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($order->details as $detail) {
                if (!$detail->variant_id) {
                    $skipped++;
                    continue;
                }

                $variant = ProductVariant::with('product')->find($detail->variant_id);

                if (!$variant || !$variant->product || $variant->status !== 'active') {
                    $skipped++;
                    continue;
                }

                $stock = (int) $variant->quantity;
                $wantedQty = (int) $detail->quantity;

                if ($stock <= 0 || $wantedQty <= 0) {
                    $skipped++;
                    continue;
                }

                $cartItem = CartDetail::where('id_cart', $cart->id)
                    ->where('id_variant', $variant->id)
                    ->lockForUpdate()
                    ->first();

                $currentCartQty = $cartItem ? (int) $cartItem->quantity : 0;

                // Chỉ được thêm phần còn lại so với tồn kho
                $canAdd = min($wantedQty, max($stock - $currentCartQty, 0));

                if ($canAdd <= 0) {
                    $skipped++;
                    continue;
                }

                if ($cartItem) {
                    $cartItem->update([
                        'quantity' => $currentCartQty + $canAdd,
                    ]);
                } else {
                    CartDetail::create([
                        'id_cart' => $cart->id,
                        'id_variant' => $variant->id,
                        'quantity' => $canAdd,
                    ]);
                }

                $addedLines++;
                $addedQty += $canAdd;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reorder error: ' . $e->getMessage());

            return redirect()->route('client.cart.index')
                ->with('error', 'Có lỗi xảy ra khi mua lại đơn hàng.');
        }

        if ($addedQty === 0) {
            return redirect()->route('client.cart.index')
                ->with('error', 'Không có sản phẩm hợp lệ để mua lại.');
        }

        $message = "Đã thêm {$addedQty} sản phẩm từ đơn cũ vào giỏ hàng.";

        if ($skipped > 0) {
            $message .= " Có {$skipped} sản phẩm không thêm được vì hết hàng, ngừng bán hoặc giỏ đã đủ số lượng.";
        }

        return redirect()->route('client.cart.index')->with('success', $message);
    }
    protected function getSelectedCheckoutItemIds(Cart $cart): array
    {
        $selectedIds = collect(session('checkout_selected_items', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($selectedIds->isEmpty()) {
            return [];
        }

        return CartDetail::where('id_cart', $cart->id)
            ->whereIn('id', $selectedIds->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();
    }

    protected function getCheckoutCartItems(Cart $cart)
    {
        $selectedIds = $this->getSelectedCheckoutItemIds($cart);

        if (empty($selectedIds)) {
            return collect();
        }

        return CartDetail::with([
            'variant.product',
            'variant.color',
            'variant.size',
        ])->where('id_cart', $cart->id)
            ->whereIn('id', $selectedIds)
            ->get();
    }

    protected function clearSelectedCartItems(Cart $cart, array $cartDetailIds): void
    {
        $cartDetailIds = collect($cartDetailIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->toArray();

        if (empty($cartDetailIds)) {
            return;
        }

        CartDetail::where('id_cart', $cart->id)
            ->whereIn('id', $cartDetailIds)
            ->delete();
    }

    protected function clearUserCartItemsByOrder(Order $order): void
    {
        $cart = Cart::where('id_user', $order->user_id)->first();

        if (!$cart) {
            return;
        }

        $variantIds = $order->details()
            ->whereNotNull('variant_id')
            ->pluck('variant_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        if (empty($variantIds)) {
            return;
        }

        CartDetail::where('id_cart', $cart->id)
            ->whereIn('id_variant', $variantIds)
            ->delete();
    }
    protected function calculateShippingFromCart($cartItems, Address $address): int
    {
        if (!$address->ghn_district_id || !$address->ghn_ward_code) {
            return 0;
        }

        $totalWeight = 0;

        foreach ($cartItems as $item) {
            $weight = (int) ($item->variant->product->weight ?? 500);
            $qty = (int) $item->quantity;
            $totalWeight += ($weight * $qty);
        }

        return app(GhnService::class)->calculateFee(
            (int) $address->ghn_district_id,
            (string) $address->ghn_ward_code,
            max($totalWeight, 100)
        );
    }

    protected function buildFullAddress(Address $address): string
    {
        return trim(implode(', ', array_filter([
            $address->detailed_address,
            $address->ward,
            $address->district,
            $address->province,
        ])));
    }

    protected function decreaseStockFromOrder(Order $order): void
    {
        $order->load('details');

        foreach ($order->details as $detail) {
            $variant = ProductVariant::lockForUpdate()->find($detail->variant_id);

            if (!$variant) {
                throw new \Exception('Không tìm thấy biến thể sản phẩm.');
            }

            if ($variant->quantity < $detail->quantity) {
                throw new \Exception('Tồn kho không đủ để xử lý đơn hàng.');
            }

            $variant->decrement('quantity', $detail->quantity);
        }
    }

    protected function clearCart(Cart $cart): void
    {
        CartDetail::where('id_cart', $cart->id)->delete();
    }

    protected function clearUserCartByOrder(Order $order): void
    {
        $cart = Cart::where('id_user', $order->user_id)->first();

        if (!$cart) {
            return;
        }

        $variantIds = $order->details()
            ->whereNotNull('variant_id')
            ->pluck('variant_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        if (empty($variantIds)) {
            return;
        }

        CartDetail::where('id_cart', $cart->id)
            ->whereIn('id_variant', $variantIds)
            ->delete();
    }
    protected function updateVnpayFailureState(Order $order, string $responseCode): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        // Khách thoát / hủy ở cổng VNPay: giữ đơn ở trạng thái chờ để còn thanh toán lại hoặc hủy đơn
        if ($responseCode === '24') {
            $order->update([
                'order_status'   => 'pending',
                'payment_status' => 'failed',
                'cancel_reason'  => 'Khách hủy phiên thanh toán VNPay',
            ]);

            return;
        }

        // Hết hạn thanh toán: đóng đơn nhưng vẫn cho phép thanh toán lại theo rule expired
        if ($responseCode === '11') {
            $order->update([
                'previous_status' => $order->order_status,
                'order_status'    => 'cancelled',
                'payment_status'  => 'expired',
                'cancel_reason'   => 'Quá hạn thanh toán VNPay',
            ]);

            return;
        }

        // Các lỗi thanh toán khác
        $order->update([
            'order_status'   => 'pending',
            'payment_status' => 'failed',
        ]);
    }
    protected function createVnpayUrl(Order $order): string
    {
        $vnpUrl = config('services.vnpay.url');
        $vnpReturnUrl = config('services.vnpay.return_url');
        $vnpTmnCode = config('services.vnpay.tmn_code');
        $vnpHashSecret = config('services.vnpay.hash_secret');

        $vnpTxnRef = $order->order_code;
        $vnpOrderInfo = 'Thanh toan don hang ' . $order->order_code;
        $vnpOrderType = 'billpayment';
        $vnpAmount = ((int) $order->total_price) * 100;
        $vnpLocale = 'vn';
        $vnpCurrCode = 'VND';
        $vnpIpAddr = request()->ip();
        $vnpCreateDate = now()->format('YmdHis');
        $vnpExpireDate = now()->addMinutes(15)->format('YmdHis');

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnpTmnCode,
            'vnp_Amount' => $vnpAmount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => $vnpCreateDate,
            'vnp_ExpireDate' => $vnpExpireDate,
            'vnp_CurrCode' => $vnpCurrCode,
            'vnp_IpAddr' => $vnpIpAddr,
            'vnp_Locale' => $vnpLocale,
            'vnp_OrderInfo' => $vnpOrderInfo,
            'vnp_OrderType' => $vnpOrderType,
            'vnp_ReturnUrl' => $vnpReturnUrl,
            'vnp_TxnRef' => $vnpTxnRef,
        ];

        ksort($inputData);

        $query = [];
        foreach ($inputData as $key => $value) {
            $query[] = urlencode($key) . '=' . urlencode($value);
        }

        $queryString = implode('&', $query);
        $vnpSecureHash = hash_hmac('sha512', $queryString, $vnpHashSecret);

        return $vnpUrl . '?' . $queryString . '&vnp_SecureHash=' . $vnpSecureHash;
    }

    protected function generateOrderCode(): string
    {
        do {
            $orderCode = 'OD' . now()->format('ymdhis') . Str::upper(Str::random(2));
        } while (Order::where('order_code', $orderCode)->exists());

        return $orderCode;
    }
    protected function getBuyNowItems()
    {
        $buyNow = session('buy_now_checkout');

        if (!$buyNow || empty($buyNow['variant_id']) || empty($buyNow['quantity'])) {
            return collect();
        }

        $variant = ProductVariant::with([
            'product',
            'color',
            'size',
        ])->find($buyNow['variant_id']);

        if (!$variant) {
            return collect();
        }

        $qty = (int) $buyNow['quantity'];

        if ($qty < 1) {
            return collect();
        }

        return collect([
            (object) [
                'id' => null,
                'quantity' => $qty,
                'variant' => $variant,
            ]
        ]);
    }
    protected function resolveCheckoutItems(?Cart $cart = null): array
    {
        $buyNowItems = $this->getBuyNowItems();

        if ($buyNowItems->isNotEmpty()) {
            return [
                'source' => 'buy_now',
                'items' => $buyNowItems,
            ];
        }

        if (!$cart) {
            return [
                'source' => 'cart',
                'items' => collect(),
            ];
        }

        return [
            'source' => 'cart',
            'items' => $this->getCheckoutCartItems($cart),
        ];
    }

    public function getProvinces()
    {
        $response = Http::withHeaders([
            'Token' => config('services.ghn.token'),
        ])->get(config('services.ghn.base_url') . '/master-data/province');

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Không lấy được danh sách tỉnh/thành.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $response->json('data', []),
        ]);
    }

    public function getDistricts(Request $request)
    {
        $request->validate([
            'province_id' => 'required|integer',
        ]);

        $response = Http::withHeaders([
            'Token' => config('services.ghn.token'),
        ])->post(config('services.ghn.base_url') . '/master-data/district', [
            'province_id' => (int) $request->province_id,
        ]);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Không lấy được danh sách quận/huyện.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $response->json('data', []),
        ]);
    }

    public function getWards(Request $request)
    {
        $request->validate([
            'district_id' => 'required|integer',
        ]);

        $response = Http::withHeaders([
            'Token' => config('services.ghn.token'),
        ])->post(config('services.ghn.base_url') . '/master-data/ward', [
            'district_id' => (int) $request->district_id,
        ]);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Không lấy được danh sách phường/xã.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $response->json('data', []),
        ]);
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'detailed_address' => 'required|string|max:255',
            'ghn_province_id' => 'required|integer',
            'ghn_district_id' => 'required|integer',
            'ghn_ward_code' => 'required|string|max:50',
        ]);

        $user = Auth::user();

        $address = Address::create([
            'user_id' => $user->id,
            'recipient_name' => $request->recipient_name,
            'phone_number' => $request->phone,
            'province' => $request->province,
            'district' => $request->district,
            'ward' => $request->ward,
            'detailed_address' => $request->detailed_address,
            'ghn_province_id' => $request->ghn_province_id,
            'ghn_district_id' => $request->ghn_district_id,
            'ghn_ward_code' => $request->ghn_ward_code,
            'address_type' => 'Home',
            'is_default' => Address::where('user_id', $user->id)->count() === 0 ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'address' => [
                'id' => $address->id,
                'text' => $address->detailed_address . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->province,
                'province' => $address->province,
                'district' => $address->district,
                'ward' => $address->ward,
                'detail' => $address->detailed_address,
                'ghn_province_id' => $address->ghn_province_id,
                'ghn_district_id' => $address->ghn_district_id,
                'ghn_ward_code' => $address->ghn_ward_code,
            ]
        ]);
    }
    public function repayVnpay($id)
    {
        $user = Auth::user();

        $order = Order::with('details')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($order->payment_method !== 'vnpay') {
            return redirect()->back()->with('error', 'Đơn này không phải thanh toán VNPay.');
        }

        if (! $order->can_repay) {
            return redirect()->back()->with('error', 'Đơn này không thuộc trạng thái cho phép thanh toán lại.');
        }

        if ($order->details->isEmpty()) {
            return redirect()->back()->with('error', 'Đơn hàng không có sản phẩm để thanh toán lại.');
        }

        DB::beginTransaction();

        try {
            foreach ($order->details as $detail) {
                if (! $detail->variant_id) {
                    throw new \Exception('Có sản phẩm trong đơn không còn biến thể hợp lệ.');
                }

                $variant = ProductVariant::lockForUpdate()->find($detail->variant_id);

                if (! $variant) {
                    throw new \Exception('Có sản phẩm trong đơn không còn tồn tại.');
                }

                if ((int) $variant->quantity < (int) $detail->quantity) {
                    throw new \Exception('Sản phẩm "' . ($detail->product_name ?? 'N/A') . '" không đủ tồn kho để thanh toán lại.');
                }
            }

            $order->update([
                'previous_status' => $order->order_status,
                'order_status'    => 'pending',
                'payment_status'  => 'pending',
                'cancel_reason'   => null,
            ]);

            DB::commit();

            return redirect($this->createVnpayUrl($order));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Repay VNPay error: ' . $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage() ?: 'Có lỗi xảy ra khi tạo thanh toán lại.');
        }
    }
    public function selectItems(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer',
        ]);

        $cart = Cart::where('id_user', Auth::id())->first();

        if (!$cart) {
            return redirect()->route('client.cart.index')->with('error', 'Không tìm thấy giỏ hàng.');
        }

        $selectedIds = CartDetail::where('id_cart', $cart->id)
            ->whereIn('id', $request->selected_items)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        if (empty($selectedIds)) {
            return redirect()->route('client.cart.index')->with('error', 'Không có sản phẩm hợp lệ để thanh toán.');
        }

        session(['checkout_selected_items' => $selectedIds]);

        return redirect()->route('client.checkout.index');
    }
    public function buyNow(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::with(['product', 'color', 'size'])->findOrFail($request->variant_id);
        $qty = (int) $request->quantity;

        if ($variant->status !== 'active') {
            return redirect()->back()->with('error', 'Biến thể sản phẩm hiện không khả dụng.');
        }

        if ((int) $variant->quantity < $qty) {
            return redirect()->back()->with('error', 'Số lượng vượt quá tồn kho.');
        }

        session([
            'buy_now_checkout' => [
                'variant_id' => $variant->id,
                'quantity' => $qty,
            ]
        ]);

        return redirect()->route('client.checkout.index');
    }
    protected function calculateSubtotal($cartItems): float
    {
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $variant = $item->variant;

            if (!$variant) {
                continue;
            }

            $subtotal += ((float) $variant->price * (int) $item->quantity);
        }

        return (float) $subtotal;
    }

    protected function getAppliedVoucherPreview(float $subtotal): ?array
    {
        $voucherId = session('checkout_voucher.voucher_id');

        if (!$voucherId || !Auth::check()) {
            return null;
        }

        $voucher = Voucher::find($voucherId);

        if (!$voucher) {
            session()->forget('checkout_voucher');
            return null;
        }

        $currentUses = $this->getUserVoucherUsage($voucher, Auth::id());

        if (!$voucher->isValid($subtotal, $currentUses, 1)) {
            session()->forget('checkout_voucher');
            return null;
        }

        $discount = min((float) $voucher->getDiscount($subtotal), $subtotal);

        return [
            'voucher_id'   => $voucher->id,
            'voucher_code' => $voucher->code,
            'discount'     => $discount,
        ];
    }

    protected function getAvailableVouchersForCheckout(float $subtotal)
    {
        $userId = Auth::id();

        return Voucher::query()
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderByDesc('value')
            ->get()
            ->filter(function ($voucher) use ($subtotal, $userId) {
                $currentUses = $this->getUserVoucherUsage($voucher, $userId);
                return $voucher->isValid($subtotal, $currentUses, 1);
            })
            ->map(function ($voucher) use ($subtotal) {
                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'type' => $voucher->type,
                    'value' => $voucher->value,
                    'max_discount' => $voucher->max_discount,
                    'min_order_value' => $voucher->min_order_value,
                    'discount_preview' => (float) $voucher->getDiscount($subtotal),
                    'end_date' => $voucher->end_date,
                ];
            })
            ->values();
    }
    protected function getUserVoucherUsage(Voucher $voucher, int $userId): int
    {
        return Order::where('user_id', $userId)
            ->where('voucher_id', $voucher->id)
            ->whereNotIn('order_status', ['cancelled'])
            ->count();
    }

    public function applyVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string|max:255',
        ], [
            'voucher_code.required' => 'Vui lòng nhập mã giảm giá.',
        ]);

        $user = Auth::user();

        $cart = Cart::firstOrCreate([
            'id_user' => $user->id,
        ]);

        $checkoutData = $this->resolveCheckoutItems($cart);
        $cartItems = $checkoutData['items'];

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Không có sản phẩm để áp dụng voucher.');
        }

        $subtotal = $this->calculateSubtotal($cartItems);

        $voucherCode = trim($request->voucher_code);

        $voucher = Voucher::whereRaw('UPPER(code) = ?', [Str::upper($voucherCode)])->first();

        if (!$voucher) {
            return redirect()->back()->with('error', 'Mã voucher không tồn tại.');
        }

        $currentUses = $this->getUserVoucherUsage($voucher, $user->id);

        if (!$voucher->isValid($subtotal, $currentUses, 1)) {
            return redirect()->back()->with('error', 'Voucher không hợp lệ hoặc đã vượt giới hạn sử dụng.');
        }

        session([
            'checkout_voucher' => [
                'voucher_id'   => $voucher->id,
                'voucher_code' => $voucher->code,
            ]
        ]);

        return redirect()->back()->with('success', 'Áp dụng voucher thành công.');
    }

    public function removeVoucher()
    {
        session()->forget('checkout_voucher');

        return redirect()->back()->with('success', 'Đã bỏ voucher.');
    }
}
