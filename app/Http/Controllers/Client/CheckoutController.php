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


class CheckoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $cart = Cart::firstOrCreate([
            'id_user' => $user->id,
        ]);

        $cartItems = CartDetail::with([
            'variant.product',
            'variant.color',
            'variant.size',
        ])->where('id_cart', $cart->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('client.cart.index')->with('error', 'Giỏ hàng đang trống.');
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

        return view('client.checkout.index', compact(
            'cartItems',
            'addresses',
            'defaultAddress',
            'shippingFee'
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

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giỏ hàng.',
            ], 422);
        }

        $cartItems = CartDetail::with('variant.product')
            ->where('id_cart', $cart->id)
            ->get();

        Log::info('Shipping fee cart items', [
            'count' => $cartItems->count(),
        ]);

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng trống.',
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

        if (!$cart) {
            return redirect()->back()->with('error', 'Không tìm thấy giỏ hàng.');
        }

        $cartItems = CartDetail::with([
            'variant.product',
            'variant.color',
            'variant.size',
        ])->where('id_cart', $cart->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Giỏ hàng đang trống.');
        }

        $subtotal = 0;

        foreach ($cartItems as $item) {
            $variant = $item->variant;

            if (!$variant) {
                return redirect()->back()->with('error', 'Có sản phẩm không hợp lệ trong giỏ hàng.');
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
        $discount = 0;
        $totalPrice = $subtotal + $shippingFee - $discount;

        $fullAddress = $this->buildFullAddress($address);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $user->email ?? null,
                'address' => $fullAddress,
                'note' => $request->note,
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'shipping_fee' => $shippingFee,
                'discount' => $discount,
                'total_price' => $totalPrice,
                'order_code' => 'OD' . now()->format('YmdHis') . Str::upper(Str::random(2)),
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

            if ($request->payment_method === 'cod') {
                $this->decreaseStockFromOrder($order);
                $this->clearCart($cart);

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

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return redirect()->route('client.cart.index')->with('error', 'Không tìm thấy đơn hàng.');
        }

        if ($responseCode === '00') {
            DB::beginTransaction();

            try {
                if ($order->payment_status !== 'paid') {
                    $this->decreaseStockFromOrder($order);
                    $this->clearUserCartByOrder($order);

                    $order->update([
                        'payment_status' => 'paid',
                        'order_status' => 'pending',
                    ]);
                }

                DB::commit();

                return redirect()
                    ->route('client.cart.index')
                    ->with('success', 'Thanh toán VNPay thành công.');
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('VNPay return success process error: ' . $e->getMessage());

                return redirect()
                    ->route('client.cart.index')
                    ->with('error', 'Thanh toán thành công nhưng xử lý đơn hàng gặp lỗi.');
            }
        }

        $order->update([
            'payment_status' => 'failed',
        ]);

        return redirect()
            ->route('client.cart.index')
            ->with('error', 'Thanh toán VNPay thất bại hoặc bị hủy.');
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

        if ($cart) {
            $this->clearCart($cart);
        }
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

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnpTmnCode,
            'vnp_Amount' => $vnpAmount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => $vnpCreateDate,
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
}
