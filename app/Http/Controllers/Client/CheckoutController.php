<?php

namespace App\Http\Controllers\Client;

use App\Mail\OrderPlacedMail;
use Illuminate\Support\Facades\Mail;
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

        // Nếu không có sản phẩm nào để thanh toán thì quay về giỏ hàng.
        if ($cartItems->isEmpty()) {
            return redirect()->route('client.cart.index')->with('error', 'Vui lòng chọn sản phẩm cần thanh toán.');
        }

        // Lấy danh sách địa chỉ của user, địa chỉ mặc định đưa lên đầu.
        $addresses = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        // Lấy địa chỉ mặc định, nếu chưa có thì lấy địa chỉ đầu tiên.
        $defaultAddress = $addresses->firstWhere('is_default', 1) ?? $addresses->first();

        $shippingFee = 0;

        // Nếu địa chỉ có đủ mã GHN thì tính phí vận chuyển.
        if ($defaultAddress && $defaultAddress->ghn_district_id && $defaultAddress->ghn_ward_code) {
            $shippingFee = $this->calculateShippingFromCart($cartItems, $defaultAddress);
        }

        // Tính tiền hàng, voucher đang áp dụng, giảm giá và tổng thanh toán.
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

        // Lấy địa chỉ thuộc user hiện tại.
        $address = Address::where('id', $request->address_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        Log::info('Shipping fee selected address', [
            'address_id' => $address->id,
            'ghn_province_id' => $address->ghn_province_id,
            'ghn_district_id' => $address->ghn_district_id,
            'ghn_ward_code' => $address->ghn_ward_code,
        ]);

        // Lấy sản phẩm đang checkout để tính phí ship theo tổng cân nặng.
        $cart = Cart::where('id_user', $user->id)->first();

        $checkoutData = $this->resolveCheckoutItems($cart);
        $cartItems = $checkoutData['items'];

        Log::info('Shipping fee checkout items', [
            'source' => $checkoutData['source'],
            'count' => $cartItems->count(),
        ]);

        // Nếu không có sản phẩm thì không tính được phí ship.
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có sản phẩm để tính phí vận chuyển.',
            ], 422);
        }

        // Tính phí ship bằng GHN.
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
        // Chuẩn hóa số điện thoại trước khi validate.
        $request->merge([
            'phone' => $this->normalizeVietnamPhone($request->phone),
        ]);

        // Validate thông tin nhận hàng và phương thức thanh toán.
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => $this->vietnamPhoneRules(),
            'address_id' => 'required|exists:addresses,id',
            'customer_note' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cod,vnpay',
        ], [
            'address_id.required' => 'Vui lòng chọn địa chỉ nhận hàng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.digits' => 'Số điện thoại phải gồm đúng 10 số.',
            'phone.regex' => 'Số điện thoại phải là số di động Việt Nam hợp lệ.',
        ]);

        $user = Auth::user();

        // Lấy địa chỉ giao hàng, bắt buộc thuộc user hiện tại.
        $address = Address::where('id', $request->address_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Lấy giỏ hàng và danh sách sản phẩm cần checkout.
        $cart = Cart::where('id_user', $user->id)->first();

        $checkoutData = $this->resolveCheckoutItems($cart);
        $checkoutSource = $checkoutData['source'];
        $cartItems = $checkoutData['items'];

        // Nếu checkout từ giỏ nhưng không có giỏ thì báo lỗi.
        if ($checkoutSource === 'cart' && !$cart) {
            return redirect()->back()->with('error', 'Không tìm thấy giỏ hàng.');
        }

        // Không có sản phẩm thì không cho tạo đơn.
        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Không có sản phẩm để thanh toán.');
        }

        // Nếu checkout từ giỏ thì lấy các cart detail được chọn để sau khi đặt hàng sẽ xóa khỏi giỏ.
        $selectedCartItemIds = $checkoutSource === 'cart' && $cart
            ? $this->getSelectedCheckoutItemIds($cart)
            : [];

        $subtotal = 0;

        // Kiểm tra lại tồn kho trước khi tạo đơn.
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

        // Tính phí ship và địa chỉ đầy đủ để lưu vào order.
        $shippingFee = $this->calculateShippingFromCart($cartItems, $address);
        $fullAddress = $this->buildFullAddress($address);

        DB::beginTransaction();

        try {
            // COD là chưa thanh toán, VNPay là đang chờ thanh toán.
            if ($request->payment_method === 'vnpay') {
                $paymentStatus = 'pending';
            } else {
                $paymentStatus = 'unpaid';
            }

            $discount = 0;
            $voucherId = null;
            $voucherCode = null;

            // Nếu có voucher trong session thì lock voucher, kiểm tra lại và trừ lượt dùng.
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

            // Tổng tiền thanh toán = tiền hàng + ship - giảm giá.
            $totalPrice = max(0, $subtotal + $shippingFee - $discount);

            // Tạo đơn hàng chính.
            $order = Order::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $user->email ?? null,
                'address' => $fullAddress,
                'customer_note' => $request->customer_note,
                'shipping_address_id' => $address->id,
                'ghn_to_province_id' => $address->ghn_province_id,
                'ghn_to_district_id' => $address->ghn_district_id,
                'ghn_to_ward_code' => $address->ghn_ward_code,
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

            // Tạo chi tiết đơn hàng theo từng biến thể.
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

            // Tạo đơn xong thì bỏ voucher khỏi session.
            session()->forget('checkout_voucher');

            if ($request->payment_method === 'cod') {
                // COD: trừ kho ngay khi tạo đơn.
                $this->decreaseStockFromOrder($order);

                // Nếu mua ngay thì xóa session mua ngay, nếu từ giỏ thì xóa item đã checkout.
                if ($checkoutSource === 'buy_now') {
                    session()->forget('buy_now_checkout');
                } else {
                    $this->clearSelectedCartItems($cart, $selectedCartItemIds);
                    session()->forget('checkout_selected_items');
                }

                DB::commit();

                // Gửi mail sau khi commit thành công.
                $this->sendOrderPlacedMail($order);

                return redirect()
                    ->route('client.cart.index')
                    ->with('success', 'Đặt hàng thành công. Đơn hàng của bạn đang chờ xác nhận.');
            }

            // VNPay: tạo đơn pending trước, chưa trừ kho, rồi chuyển sang VNPay.
            DB::commit();

            return redirect()->away($this->createVnpayUrl($order));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Checkout store error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tạo đơn hàng.');
        }
    }

    public function paymentReturn(Request $request)
    {
        // Return URL chạy khi khách được VNPay chuyển về website.
        $vnpHashSecret = config('services.vnpay.hash_secret');
        $inputData = $request->all();

        // Lấy chữ ký VNPay gửi về và bỏ khỏi dữ liệu trước khi tự hash lại.
        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? null;
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        // Sắp xếp tham số để tạo chữ ký đúng chuẩn VNPay.
        ksort($inputData);

        $hashData = [];
        foreach ($inputData as $key => $value) {
            $hashData[] = urlencode($key) . '=' . urlencode($value);
        }

        // Tự tạo chữ ký để so sánh với chữ ký VNPay gửi về.
        $secureHash = hash_hmac('sha512', implode('&', $hashData), $vnpHashSecret);

        // Sai chữ ký thì không xử lý đơn.
        if ($secureHash !== $vnpSecureHash) {
            return redirect()->route('client.cart.index')->with('error', 'Chữ ký VNPay không hợp lệ.');
        }

        // Lấy thông tin giao dịch VNPay.
        $orderCode = $request->vnp_TxnRef;
        $responseCode = $request->vnp_ResponseCode;
        $transactionStatus = $request->vnp_TransactionStatus;

        // Tìm đơn theo order_code đã gửi sang VNPay.
        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return redirect()->route('client.cart.index')->with('error', 'Không tìm thấy đơn hàng.');
        }

        // Kiểm tra số tiền VNPay trả về có khớp với đơn không.
        if (!$this->isValidVnpayAmount($order, $request->vnp_Amount)) {
            Log::warning('VNPay return amount mismatch', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'expected_amount' => ((int) $order->total_price) * 100,
                'received_amount' => (int) ($request->vnp_Amount ?? 0),
                'response_code' => $responseCode,
                'transaction_status' => $transactionStatus,
            ]);

            DB::beginTransaction();

            try {
                // Lệch tiền thì cập nhật trạng thái lỗi, không xử lý như đơn thanh toán thành công.
                $this->updateVnpayFailureState($order, '97', $transactionStatus);

                DB::commit();

                return redirect()
                    ->route('client.cart.index')
                    ->with('error', 'Số tiền thanh toán VNPay không khớp.');
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('VNPay return amount mismatch process error: ' . $e->getMessage());

                return redirect()
                    ->route('client.cart.index')
                    ->with('error', 'Phát hiện sai lệch số tiền nhưng xử lý đơn hàng gặp lỗi.');
            }
        }

        DB::beginTransaction();

        try {
            $shouldSendMail = false;

            // VNPay trả 00 nghĩa là thanh toán thành công.
            if ($responseCode === '00' && $transactionStatus === '00') {
                // Chỉ xử lý nếu đơn chưa paid, tránh trừ kho/gửi mail nhiều lần.
                if ($order->payment_status !== 'paid') {
                    // VNPay thành công thì lúc này mới trừ kho.
                    $this->decreaseStockFromOrder($order);

                    // Xóa sản phẩm đã mua khỏi giỏ.
                    $this->clearUserCartByOrder($order);

                    // Cập nhật đơn đã thanh toán và lưu thông tin giao dịch VNPay.
                    $order->update(array_merge(
                        [
                            'payment_status' => 'paid',
                            'order_status'   => 'pending',
                        ],
                        $this->getVnpayPaymentMeta($request)
                    ));

                    $shouldSendMail = true;
                }

                DB::commit();

                // Xóa session mua ngay/chọn item sau khi thanh toán thành công.
                session()->forget('buy_now_checkout');
                session()->forget('checkout_selected_items');

                if ($shouldSendMail) {
                    $this->sendOrderPlacedMail($order);
                }

                return redirect()
                    ->route('client.cart.index')
                    ->with('success', 'Thanh toán VNPay thành công.');
            }

            // VNPay thất bại/hủy/hết hạn thì cập nhật trạng thái tương ứng.
            $this->updateVnpayFailureState($order, $responseCode, $transactionStatus);

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
        // IPN là callback server-to-server từ VNPay.
        $vnpHashSecret = config('services.vnpay.hash_secret');
        $inputData = $request->all();

        // Lấy chữ ký và loại bỏ khỏi data trước khi tự hash.
        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? null;
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);

        $hashData = [];
        foreach ($inputData as $key => $value) {
            $hashData[] = urlencode($key) . '=' . urlencode($value);
        }

        // Kiểm tra chữ ký VNPay.
        $secureHash = hash_hmac('sha512', implode('&', $hashData), $vnpHashSecret);

        if ($secureHash !== $vnpSecureHash) {
            return response()->json([
                'RspCode' => '97',
                'Message' => 'Invalid signature',
            ]);
        }

        // Lấy thông tin giao dịch.
        $orderCode = $request->vnp_TxnRef;
        $responseCode = $request->vnp_ResponseCode;
        $transactionStatus = $request->vnp_TransactionStatus;

        // Tìm đơn theo order_code.
        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return response()->json([
                'RspCode' => '01',
                'Message' => 'Order not found',
            ]);
        }

        // Kiểm tra số tiền thanh toán.
        if (!$this->isValidVnpayAmount($order, $request->vnp_Amount)) {
            Log::warning('VNPay IPN amount mismatch', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'expected_amount' => ((int) $order->total_price) * 100,
                'received_amount' => (int) ($request->vnp_Amount ?? 0),
                'response_code' => $responseCode,
                'transaction_status' => $transactionStatus,
            ]);

            DB::beginTransaction();

            try {
                $this->updateVnpayFailureState($order, '97', $transactionStatus);
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('VNPay IPN amount mismatch process error: ' . $e->getMessage());
            }

            return response()->json([
                'RspCode' => '04',
                'Message' => 'Invalid amount',
            ]);
        }

        DB::beginTransaction();

        try {
            $shouldSendMail = false;

            // Nếu VNPay báo thành công thì xử lý paid.
            if ($responseCode === '00' && $transactionStatus === '00') {
                // Chỉ xử lý khi đơn chưa paid để tránh xử lý trùng.
                if ($order->payment_status !== 'paid') {
                    $this->decreaseStockFromOrder($order);
                    $this->clearUserCartByOrder($order);

                    $order->update(array_merge(
                        [
                            'payment_status' => 'paid',
                            'order_status'   => 'pending',
                        ],
                        $this->getVnpayPaymentMeta($request)
                    ));

                    $shouldSendMail = true;
                }
            } else {
                // Thanh toán không thành công thì cập nhật trạng thái lỗi.
                $this->updateVnpayFailureState($order, $responseCode, $transactionStatus);
            }

            DB::commit();

            if ($shouldSendMail) {
                $this->sendOrderPlacedMail($order);
            }

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

        // Lấy đơn cũ của user để mua lại.
        $order = Order::with('details')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Lấy hoặc tạo giỏ hàng.
        $cart = Cart::firstOrCreate([
            'id_user' => $user->id,
        ]);

        $addedLines = 0;
        $addedQty = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            // Duyệt từng sản phẩm trong đơn cũ để thêm lại vào giỏ.
            foreach ($order->details as $detail) {
                if (!$detail->variant_id) {
                    $skipped++;
                    continue;
                }

                // Lấy biến thể và sản phẩm cha để kiểm tra còn bán được không.
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

                // Kiểm tra biến thể này đã có trong giỏ chưa.
                $cartItem = CartDetail::where('id_cart', $cart->id)
                    ->where('id_variant', $variant->id)
                    ->lockForUpdate()
                    ->first();

                $currentCartQty = $cartItem ? (int) $cartItem->quantity : 0;

                // Chỉ thêm số lượng còn có thể thêm, không vượt tồn kho.
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
        // Lấy cart detail id mà user đã chọn để thanh toán từ session.
        $selectedIds = collect(session('checkout_selected_items', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($selectedIds->isEmpty()) {
            return [];
        }

        // Chỉ giữ lại những id thật sự thuộc giỏ hàng hiện tại.
        return CartDetail::where('id_cart', $cart->id)
            ->whereIn('id', $selectedIds->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();
    }

    protected function getCheckoutCartItems(Cart $cart)
    {
        // Lấy các item đã chọn để checkout.
        $selectedIds = $this->getSelectedCheckoutItemIds($cart);

        if (empty($selectedIds)) {
            return collect();
        }

        // Load biến thể, sản phẩm, màu, size để hiển thị và tạo order detail.
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
        // Chuẩn hóa danh sách cart detail id cần xóa.
        $cartDetailIds = collect($cartDetailIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->toArray();

        if (empty($cartDetailIds)) {
            return;
        }

        // Xóa các item đã checkout khỏi giỏ hàng.
        CartDetail::where('id_cart', $cart->id)
            ->whereIn('id', $cartDetailIds)
            ->delete();
    }

    protected function calculateShippingFromCart($cartItems, Address $address): int
    {
        // Nếu địa chỉ thiếu mã GHN thì không tính được phí ship.
        if (!$address->ghn_district_id || !$address->ghn_ward_code) {
            return 0;
        }

        $totalWeight = 0;

        // Tính tổng cân nặng sản phẩm.
        foreach ($cartItems as $item) {
            $weight = (int) ($item->variant->product->weight ?? 500);
            $qty = (int) $item->quantity;
            $totalWeight += ($weight * $qty);
        }

        // Gọi GhnService tính phí ship.
        return app(GhnService::class)->calculateFee(
            (int) $address->ghn_district_id,
            (string) $address->ghn_ward_code,
            max($totalWeight, 100)
        );
    }

    protected function buildFullAddress(Address $address): string
    {
        // Ghép địa chỉ thành chuỗi đầy đủ để lưu vào đơn.
        return trim(implode(', ', array_filter([
            $address->detailed_address,
            $address->ward,
            $address->district,
            $address->province,
        ])));
    }

    protected function decreaseStockFromOrder(Order $order): void
    {
        // Load chi tiết đơn để biết cần trừ kho biến thể nào.
        $order->load('details');

        foreach ($order->details as $detail) {
            // Khóa dòng biến thể khi trừ kho để tránh âm kho.
            $variant = ProductVariant::lockForUpdate()->find($detail->variant_id);

            if (!$variant) {
                throw new \Exception('Không tìm thấy biến thể sản phẩm.');
            }

            if ($variant->quantity < $detail->quantity) {
                throw new \Exception('Tồn kho không đủ để xử lý đơn hàng.');
            }

            // Trừ tồn kho theo số lượng khách mua.
            $variant->decrement('quantity', $detail->quantity);
        }
    }

    protected function clearCart(Cart $cart): void
    {
        // Xóa toàn bộ item trong giỏ hàng.
        CartDetail::where('id_cart', $cart->id)->delete();
    }

    protected function clearUserCartByOrder(Order $order): void
    {
        // Lấy giỏ hàng của user theo order.
        $cart = Cart::where('id_user', $order->user_id)->first();

        if (!$cart) {
            return;
        }

        // Lấy variant_id trong đơn đã thanh toán.
        $variantIds = $order->details()
            ->whereNotNull('variant_id')
            ->pluck('variant_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        if (empty($variantIds)) {
            return;
        }

        // Xóa các item trong giỏ có variant_id trùng với đơn đã mua.
        CartDetail::where('id_cart', $cart->id)
            ->whereIn('id_variant', $variantIds)
            ->delete();
    }

    protected function updateVnpayFailureState(Order $order, string $responseCode, ?string $transactionStatus = null): void
    {
        // Nếu đã paid thì không ghi đè thành thất bại nữa.
        if ($order->payment_status === 'paid') {
            return;
        }

        // Chỉ xử lý lỗi cho đơn đang pending thanh toán VNPay.
        if ($order->payment_status !== 'pending') {
            return;
        }

        // Hoàn lại voucher nếu VNPay thất bại/hủy/hết hạn.
        $this->restoreVoucherForOrder($order);

        if ($responseCode === '97') {
            $order->update([
                'order_status'                => 'pending',
                'payment_status'              => 'failed',
                'cancel_reason'               => 'Sai lệch số tiền VNPay trả về',
                'payment_response_code'       => $responseCode,
                'payment_transaction_status'  => $transactionStatus,
            ]);

            return;
        }

        if ($responseCode === '24') {
            $order->update([
                'order_status'                => 'pending',
                'payment_status'              => 'cancelled',
                'cancel_reason'               => 'Khách hủy phiên thanh toán VNPay',
                'payment_response_code'       => $responseCode,
                'payment_transaction_status'  => $transactionStatus,
            ]);

            return;
        }

        if ($responseCode === '11') {
            $order->update([
                'previous_status'             => $order->order_status,
                'order_status'                => 'cancelled',
                'payment_status'              => 'expired',
                'cancel_reason'               => 'Quá hạn thanh toán VNPay',
                'payment_response_code'       => $responseCode,
                'payment_transaction_status'  => $transactionStatus,
            ]);

            return;
        }

        // Các mã khác coi là thanh toán thất bại.
        $order->update([
            'order_status'                => 'pending',
            'payment_status'              => 'failed',
            'cancel_reason'               => 'Thanh toán VNPay thất bại',
            'payment_response_code'       => $responseCode,
            'payment_transaction_status'  => $transactionStatus,
        ]);
    }

    protected function createVnpayUrl(Order $order): string
    {
        // Lấy cấu hình VNPay.
        $vnpUrl = trim((string) config('services.vnpay.url'));
        $vnpReturnUrl = trim((string) config('services.vnpay.return_url'));
        $vnpTmnCode = trim((string) config('services.vnpay.tmn_code'));
        $vnpHashSecret = trim((string) config('services.vnpay.hash_secret'));

        // Chuẩn bị dữ liệu đơn hàng gửi sang VNPay.
        $vnpTxnRef = $order->order_code;
        $vnpOrderInfo = 'Thanh toan don hang ' . $order->order_code;
        $vnpOrderType = 'billpayment';
        $vnpAmount = (int) round((float) $order->total_price * 100);
        $vnpLocale = 'vn';
        $vnpCurrCode = 'VND';
        $vnpIpAddr = request()->ip() ?: '127.0.0.1';
        $vnpCreateDate = now()->format('YmdHis');
        $vnpExpireDate = now()->addMinutes(15)->format('YmdHis');

        // Lưu thời điểm tạo và hết hạn phiên thanh toán.
        $this->saveVnpayRequestMeta($order, $vnpCreateDate, $vnpExpireDate);

        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $vnpTmnCode,
            "vnp_Amount"     => $vnpAmount,
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => $vnpCreateDate,
            "vnp_CurrCode"   => $vnpCurrCode,
            "vnp_IpAddr"     => $vnpIpAddr,
            "vnp_Locale"     => $vnpLocale,
            "vnp_OrderInfo"  => $vnpOrderInfo,
            "vnp_OrderType"  => $vnpOrderType,
            "vnp_ReturnUrl"  => $vnpReturnUrl,
            "vnp_TxnRef"     => $vnpTxnRef,
            "vnp_ExpireDate" => $vnpExpireDate,
        ];

        // Sắp xếp tham số trước khi tạo chữ ký.
        ksort($inputData);

        $query = "";
        $i = 0;
        $hashdata = "";

        // Tạo query string và chuỗi dùng để hash.
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }

            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        // Tạo chữ ký bảo mật VNPay.
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnpHashSecret);

        // Ghép URL thanh toán.
        $vnpUrl = $vnpUrl . "?" . $query . 'vnp_SecureHash=' . $vnpSecureHash;

        return $vnpUrl;
    }

    protected function generateOrderCode(): string
    {
        // Sinh mã đơn và lặp lại nếu bị trùng.
        do {
            $orderCode = 'OD' . now()->format('ymdhis') . Str::upper(Str::random(2));
        } while (Order::where('order_code', $orderCode)->exists());

        return $orderCode;
    }

    protected function getBuyNowItems()
    {
        // Lấy dữ liệu mua ngay từ session.
        $buyNow = session('buy_now_checkout');

        if (!$buyNow || empty($buyNow['variant_id']) || empty($buyNow['quantity'])) {
            return collect();
        }

        // Lấy biến thể mua ngay.
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

        // Trả object giả giống cart item để checkout xử lý chung.
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
        // Ưu tiên checkout từ mua ngay.
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

        // Nếu không mua ngay thì lấy item được chọn trong giỏ.
        return [
            'source' => 'cart',
            'items' => $this->getCheckoutCartItems($cart),
        ];
    }

    protected function normalizeVietnamPhone(?string $phone): string
    {
        // Xóa ký tự không phải số trong số điện thoại.
        return preg_replace('/\D+/', '', trim((string) $phone));
    }

    protected function vietnamPhoneRules(): array
    {
        // Rule số điện thoại di động Việt Nam.
        return [
            'required',
            'digits:10',
            'regex:/^0(3|5|7|8|9)\d{8}$/',
        ];
    }

    public function getProvinces()
    {
        // Gọi API GHN lấy danh sách tỉnh/thành.
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
        // Validate province_id trước khi lấy quận/huyện.
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
        // Validate district_id trước khi lấy phường/xã.
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
        // Chuẩn hóa số điện thoại trước khi validate.
        $request->merge([
            'phone' => $this->normalizeVietnamPhone($request->phone),
        ]);

        // Validate địa chỉ tạo nhanh trong checkout.
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone' => $this->vietnamPhoneRules(),
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'detailed_address' => 'required|string|max:255',
            'ghn_province_id' => 'required|integer',
            'ghn_district_id' => 'required|integer',
            'ghn_ward_code' => 'required|string|max:50',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.digits' => 'Số điện thoại phải gồm đúng 10 số.',
            'phone.regex' => 'Số điện thoại phải là số di động Việt Nam hợp lệ.',
        ]);

        $user = Auth::user();

        // Tạo địa chỉ mới, lưu cả mã GHN để tính phí ship.
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

        // Trả JSON để frontend thêm địa chỉ mới vào checkout.
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

        // Lấy đơn VNPay cũ của user để thanh toán lại.
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
            // Kiểm tra lại tồn kho trước khi tạo phiên thanh toán lại.
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

            // Nếu đơn có voucher thì giữ/trừ lại voucher.
            $this->reserveVoucherForRepay($order);

            // Đưa đơn về trạng thái chờ thanh toán lại.
            $order->update([
                'previous_status' => $order->order_status,
                'order_status'    => 'pending',
                'payment_status'  => 'pending',
                'cancel_reason'   => null,
            ]);

            DB::commit();

            return redirect()->away($this->createVnpayUrl($order));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Repay VNPay error: ' . $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage() ?: 'Có lỗi xảy ra khi tạo thanh toán lại.');
        }
    }

    public function selectItems(Request $request)
    {
        // Validate danh sách item được chọn để checkout.
        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer',
        ]);

        $cart = Cart::where('id_user', Auth::id())->first();

        if (!$cart) {
            return redirect()->route('client.cart.index')->with('error', 'Không tìm thấy giỏ hàng.');
        }

        // Chỉ giữ item thật sự thuộc giỏ hàng của user.
        $selectedIds = CartDetail::where('id_cart', $cart->id)
            ->whereIn('id', $request->selected_items)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        if (empty($selectedIds)) {
            return redirect()->route('client.cart.index')->with('error', 'Không có sản phẩm hợp lệ để thanh toán.');
        }

        // Lưu item được chọn vào session.
        session(['checkout_selected_items' => $selectedIds]);

        return redirect()->route('client.checkout.index');
    }

    public function buyNow(Request $request)
    {
        // Validate biến thể và số lượng khi mua ngay.
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Lấy biến thể kèm sản phẩm, màu, size.
        $variant = ProductVariant::with(['product', 'color', 'size'])->findOrFail($request->variant_id);
        $qty = (int) $request->quantity;

        if ($variant->status !== 'active') {
            return redirect()->back()->with('error', 'Biến thể sản phẩm hiện không khả dụng.');
        }

        if ((int) $variant->quantity < $qty) {
            return redirect()->back()->with('error', 'Số lượng vượt quá tồn kho.');
        }

        // Lưu mua ngay vào session, không tạo cart detail.
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

        // Tính tổng tiền hàng.
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
        // Lấy voucher đang áp dụng trong session.
        $voucherId = session('checkout_voucher.voucher_id');

        if (!$voucherId || !Auth::check()) {
            return null;
        }

        $voucher = Voucher::find($voucherId);

        if (!$voucher) {
            session()->forget('checkout_voucher');
            return null;
        }

        // Kiểm tra voucher còn hợp lệ không.
        $currentUses = $this->getUserVoucherUsage($voucher, Auth::id());

        if (!$voucher->isValid($subtotal, $currentUses, 1)) {
            session()->forget('checkout_voucher');
            return null;
        }

        // Tính số tiền giảm, không vượt quá subtotal.
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

        // Lấy voucher active, còn lượt, còn hạn và hợp lệ với đơn hiện tại.
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
        // Đếm số lần user đã dùng voucher trong các đơn hợp lệ.
        return Order::where('user_id', $userId)
            ->where('voucher_id', $voucher->id)
            ->where('order_status', '!=', 'cancelled')
            ->whereNotIn('payment_status', ['failed', 'expired', 'cancelled'])
            ->count();
    }

    protected function restoreVoucherForOrder(Order $order): void
    {
        // Không có voucher thì không cần hoàn.
        if (!$order->voucher_id) {
            return;
        }

        // Khóa voucher rồi cộng lại số lượng.
        $voucher = Voucher::lockForUpdate()->find($order->voucher_id);

        if (!$voucher) {
            return;
        }

        $voucher->increment('quantity');
    }

    protected function isValidVnpayAmount(Order $order, $vnpAmount): bool
    {
        // VNPay gửi số tiền nhân 100.
        $expectedAmount = ((int) $order->total_price) * 100;
        $receivedAmount = (int) ($vnpAmount ?? 0);

        return $receivedAmount === $expectedAmount;
    }

    protected function reserveVoucherForRepay(Order $order): void
    {
        // Nếu đơn không dùng voucher thì không cần giữ lại voucher.
        if (!$order->voucher_id) {
            return;
        }

        $order->loadMissing('details');

        // Khóa voucher để kiểm tra và trừ lượt khi thanh toán lại.
        $voucher = Voucher::lockForUpdate()->find($order->voucher_id);

        if (!$voucher) {
            throw new \Exception('Voucher của đơn hàng không còn tồn tại.');
        }

        $subtotal = (float) $order->details->sum(fn ($detail) => (float) $detail->total);
        $currentUses = $this->getUserVoucherUsage($voucher, (int) $order->user_id);

        if (!$voucher->isValid($subtotal, $currentUses, 1)) {
            throw new \Exception('Voucher của đơn hàng không còn khả dụng để thanh toán lại.');
        }

        $voucher->decreaseQuantity();
    }

    protected function saveVnpayRequestMeta(Order $order, string $createDate, string $expireDate): void
    {
        // Lưu thời gian tạo và hết hạn phiên thanh toán VNPay.
        $order->update([
            'payment_request_date' => $createDate,
            'payment_expire_date'  => $expireDate,
        ]);
    }

    protected function getVnpayPaymentMeta(Request $request): array
    {
        // Lấy thông tin giao dịch VNPay để lưu vào order.
        return [
            'payment_transaction_no'     => $request->vnp_TransactionNo,
            'payment_bank_code'          => $request->vnp_BankCode,
            'payment_response_code'      => $request->vnp_ResponseCode,
            'payment_transaction_status' => $request->vnp_TransactionStatus,
            'payment_pay_date'           => $request->vnp_PayDate,
            'paid_at'                    => now(),
        ];
    }

    protected function sendOrderPlacedMail(Order $order): void
    {
        // Không có email thì không gửi mail.
        if (empty($order->email)) {
            return;
        }

        try {
            // Gửi mail xác nhận đơn hàng, load thêm biến thể màu-size.
            Mail::to($order->email)->send(
                new OrderPlacedMail($order->fresh([
                    'details.variant.color',
                    'details.variant.size',
                ]))
            );
        } catch (\Throwable $e) {
            // Gửi mail lỗi thì chỉ log, không rollback đơn hàng.
            Log::error('Send order mail error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'email' => $order->email,
            ]);
        }
    }

    public function applyVoucher(Request $request)
    {
        // Validate mã voucher.
        $request->validate([
            'voucher_code' => 'required|string|max:255',
        ], [
            'voucher_code.required' => 'Vui lòng nhập mã giảm giá.',
        ]);

        $user = Auth::user();

        // Lấy giỏ hàng để xác định sản phẩm đang checkout.
        $cart = Cart::firstOrCreate([
            'id_user' => $user->id,
        ]);

        $checkoutData = $this->resolveCheckoutItems($cart);
        $cartItems = $checkoutData['items'];

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Không có sản phẩm để áp dụng voucher.');
        }

        $subtotal = $this->calculateSubtotal($cartItems);

        // Tìm voucher không phân biệt hoa/thường.
        $voucherCode = trim($request->voucher_code);

        $voucher = Voucher::whereRaw('UPPER(code) = ?', [Str::upper($voucherCode)])->first();

        if (!$voucher) {
            return redirect()->back()->with('error', 'Mã voucher không tồn tại.');
        }

        // Kiểm tra voucher còn hợp lệ với đơn hàng và user.
        $currentUses = $this->getUserVoucherUsage($voucher, $user->id);

        if (!$voucher->isValid($subtotal, $currentUses, 1)) {
            return redirect()->back()->with('error', 'Voucher không hợp lệ hoặc đã vượt giới hạn sử dụng.');
        }

        // Lưu voucher vào session, khi tạo đơn sẽ kiểm tra lại trong transaction.
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
        // Xóa voucher khỏi session checkout.
        session()->forget('checkout_voucher');

        return redirect()->back()->with('success', 'Đã bỏ voucher.');
    }
}
