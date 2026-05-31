<?php

namespace App\Http\Controllers\Client;

use App\Actions\Checkout\BuyNowCheckoutAction;
use App\Actions\Checkout\ApplyCheckoutVoucherAction;
use App\Actions\Checkout\RemoveCheckoutVoucherAction;
use App\Actions\Checkout\SelectCheckoutItemsAction;
use App\Actions\Checkout\StoreCheckoutAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\ApplyVoucherRequest;
use App\Http\Requests\Checkout\BuyNowRequest;
use App\Http\Requests\Checkout\GetShippingFeeRequest;
use App\Http\Requests\Checkout\GhnDistrictsRequest;
use App\Http\Requests\Checkout\GhnWardsRequest;
use App\Http\Requests\Checkout\SelectCheckoutItemsRequest;
use App\Http\Requests\Checkout\StoreCheckoutAddressRequest;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Services\Checkout\CheckoutAddressPresenter;
use App\Services\Checkout\CheckoutPageService;
use App\Services\Checkout\CheckoutShippingFeeService;
use App\Services\Checkout\GhnLocationService;
use App\Services\Checkout\PlaceCheckoutOrderService;
use App\Services\Checkout\ReorderService;
use App\Services\Checkout\RepayVnpayService;
use App\Services\Checkout\VnpayCallbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CheckoutController extends Controller
{
    public function index(CheckoutPageService $checkoutPage)
    {
        try {
            return view('client.checkout.index', $checkoutPage->dataFor(Auth::user()));
        } catch (\RuntimeException $e) {
            return redirect()->route('client.cart.index')->with('error', $e->getMessage());
        }
    }

    public function getShippingFee(GetShippingFeeRequest $request, CheckoutShippingFeeService $shippingFee)
    {
        try {
            return response()->json([
                'success' => true,
                'shipping_fee' => $shippingFee->calculate(Auth::user(), (int) $request->address_id),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(StoreCheckoutRequest $request, PlaceCheckoutOrderService $placeCheckoutOrder)
    {
        try {
            $result = $placeCheckoutOrder->execute(Auth::user(), $request->toDTO());

            if ($result->isVnpay()) {
                return redirect()->away($result->paymentUrl);
            }

            return redirect()
                ->route('client.cart.index')
                ->with('success', 'Đặt hàng thành công. Đơn hàng của bạn đang chờ xác nhận.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Checkout store error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tạo đơn hàng.');
        }
    }

    public function paymentReturn(Request $request, VnpayCallbackService $vnpayCallback)
    {
        $result = $vnpayCallback->handleReturn($request);

        return redirect()
            ->route('client.cart.index')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function paymentIpn(Request $request, VnpayCallbackService $vnpayCallback)
    {
        return response()->json($vnpayCallback->handleIpn($request));
    }

    public function reorder($id, ReorderService $reorder)
    {
        try {
            $result = $reorder->execute(Auth::user(), (int) $id);

            return redirect()->route('client.cart.index')->with('success', $result['message']);
        } catch (\RuntimeException $e) {
            return redirect()->route('client.cart.index')->with('error', $e->getMessage());
        }
    }

    public function repayVnpay($id, RepayVnpayService $repayVnpay)
    {
        try {
            return redirect()->away($repayVnpay->execute(Auth::user(), (int) $id));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function selectItems(SelectCheckoutItemsRequest $request, SelectCheckoutItemsAction $selectCheckoutItems)
    {
        try {
            $selectCheckoutItems->execute(Auth::id(), $request->input('selected_items', []));

            return redirect()->route('client.checkout.index');
        } catch (\RuntimeException $e) {
            return redirect()->route('client.cart.index')->with('error', $e->getMessage());
        }
    }

    public function buyNow(BuyNowRequest $request, BuyNowCheckoutAction $buyNowCheckout)
    {
        try {
            $buyNowCheckout->execute($request->toDTO());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('client.checkout.index');
    }

    public function getProvinces(GhnLocationService $ghnLocations)
    {
        return $this->locationResponse(fn () => $ghnLocations->provinceData());
    }

    public function getDistricts(GhnDistrictsRequest $request, GhnLocationService $ghnLocations)
    {
        return $this->locationResponse(fn () => $ghnLocations->districtData((int) $request->province_id));
    }

    public function getWards(GhnWardsRequest $request, GhnLocationService $ghnLocations)
    {
        return $this->locationResponse(fn () => $ghnLocations->wardData((int) $request->district_id));
    }

    public function storeAddress(
        StoreCheckoutAddressRequest $request,
        StoreCheckoutAddressAction $storeCheckoutAddress,
        CheckoutAddressPresenter $presenter
    ) {
        return response()->json([
            'success' => true,
            'address' => $presenter->toArray($storeCheckoutAddress->execute(Auth::id(), $request->toDTO())),
        ]);
    }

    public function applyVoucher(ApplyVoucherRequest $request, ApplyCheckoutVoucherAction $applyCheckoutVoucher)
    {
        try {
            $applyCheckoutVoucher->execute(Auth::id(), (string) $request->voucher_code);

            return redirect()->back()->with('success', 'Áp dụng voucher thành công.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function removeVoucher(RemoveCheckoutVoucherAction $removeCheckoutVoucher)
    {
        $removeCheckoutVoucher->execute();

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
