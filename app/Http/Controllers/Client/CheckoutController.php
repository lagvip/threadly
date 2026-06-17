<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\ApplyVoucherRequest;
use App\Http\Requests\Checkout\BuyNowRequest;
use App\Http\Requests\Checkout\GetShippingFeeRequest;
use App\Http\Requests\Checkout\GhnDistrictsRequest;
use App\Http\Requests\Checkout\GhnWardsRequest;
use App\Http\Requests\Checkout\RepayVnpayRequest;
use App\Http\Requests\Checkout\SelectCheckoutItemsRequest;
use App\Http\Requests\Checkout\StoreCheckoutAddressRequest;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Http\Requests\Checkout\VnpayCallbackRequest;
use App\Services\Checkout\ApplyCheckoutVoucherService;
use App\Services\Checkout\BuyNowCheckoutService;
use App\Services\Checkout\CheckoutAddressPresenter;
use App\Services\Checkout\CheckoutPageService;
use App\Services\Checkout\CheckoutShippingFeeService;
use App\Services\Checkout\GhnLocationService;
use App\Services\Checkout\PlaceCheckoutOrderService;
use App\Services\Checkout\RemoveCheckoutVoucherService;
use App\Services\Checkout\ReorderService;
use App\Services\Checkout\RepayVnpayService;
use App\Services\Checkout\SelectCheckoutItemsService;
use App\Services\Checkout\StoreCheckoutAddressService;
use App\Services\Checkout\VnpayCallbackService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutPageService $checkoutPage,
        protected CheckoutShippingFeeService $shippingFee,
        protected PlaceCheckoutOrderService $placeCheckoutOrder,
        protected VnpayCallbackService $vnpayCallback,
        protected ReorderService $reorder,
        protected RepayVnpayService $repayVnpay,
        protected SelectCheckoutItemsService $selectCheckoutItems,
        protected BuyNowCheckoutService $buyNowCheckout,
        protected GhnLocationService $ghnLocations,
        protected StoreCheckoutAddressService $storeCheckoutAddress,
        protected CheckoutAddressPresenter $addressPresenter,
        protected ApplyCheckoutVoucherService $applyCheckoutVoucher,
        protected RemoveCheckoutVoucherService $removeCheckoutVoucher,
    ) {}

    public function index()
    {
        try {
            return view('client.checkout.index', $this->checkoutPage->dataFor(Auth::user()));
        } catch (\RuntimeException $e) {
            return redirect()->route('client.cart.index')->with('error', $e->getMessage());
        }
    }

    public function getShippingFee(GetShippingFeeRequest $request)
    {
        try {
            return response()->json([
                'success' => true,
                'shipping_fee' => $this->shippingFee->calculate(Auth::user(), (int) $request->address_id),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(StoreCheckoutRequest $request)
    {
        try {
            $result = $this->placeCheckoutOrder->execute(Auth::user(), $request->toDTO(), $request->ip());

            if ($result->isVnpay()) {
                return redirect()->away($result->paymentUrl);
            }

            return redirect()
                ->route('client.cart.index')
                ->with('success', 'Đặt hàng thành công. Đơn hàng của bạn đang chờ xác nhận.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Checkout store error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tạo đơn hàng.');
        }
    }

    public function paymentReturn(VnpayCallbackRequest $request)
    {
        $result = $this->vnpayCallback->handleReturn($request->toDTO());

        return redirect()
            ->route('client.cart.index')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function paymentIpn(VnpayCallbackRequest $request)
    {
        return response()->json($this->vnpayCallback->handleIpn($request->toDTO()));
    }

    public function reorder($id)
    {
        try {
            $result = $this->reorder->execute(Auth::user(), (int) $id);

            return redirect()->route('client.cart.index')->with('success', $result['message']);
        } catch (\RuntimeException $e) {
            return redirect()->route('client.cart.index')->with('error', $e->getMessage());
        }
    }

    public function repayVnpay(RepayVnpayRequest $request, $id)
    {
        try {
            return redirect()->away($this->repayVnpay->execute(Auth::user(), (int) $id, $request->clientIp()));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function selectItems(SelectCheckoutItemsRequest $request)
    {
        try {
            $this->selectCheckoutItems->execute(Auth::id(), $request->input('selected_items', []));

            return redirect()->route('client.checkout.index');
        } catch (\RuntimeException $e) {
            return redirect()->route('client.cart.index')->with('error', $e->getMessage());
        }
    }

    public function buyNow(BuyNowRequest $request)
    {
        try {
            $this->buyNowCheckout->execute($request->toDTO());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('client.checkout.index');
    }

    public function getProvinces()
    {
        return $this->locationResponse(fn () => $this->ghnLocations->provinceData());
    }

    public function getDistricts(GhnDistrictsRequest $request)
    {
        return $this->locationResponse(fn () => $this->ghnLocations->districtData((int) $request->province_id));
    }

    public function getWards(GhnWardsRequest $request)
    {
        return $this->locationResponse(fn () => $this->ghnLocations->wardData((int) $request->district_id));
    }

    public function storeAddress(StoreCheckoutAddressRequest $request)
    {
        return response()->json([
            'success' => true,
            'address' => $this->addressPresenter->toArray($this->storeCheckoutAddress->execute(Auth::id(), $request->toDTO())),
        ]);
    }

    public function applyVoucher(ApplyVoucherRequest $request)
    {
        try {
            $this->applyCheckoutVoucher->execute(Auth::id(), (string) $request->voucher_code);

            return redirect()->back()->with('success', 'Áp dụng voucher thành công.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function removeVoucher()
    {
        $this->removeCheckoutVoucher->execute();

        return redirect()->back()->with('success', 'Đã bỏ voucher.');
    }

    protected function locationResponse(callable $resolver)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $resolver(),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
