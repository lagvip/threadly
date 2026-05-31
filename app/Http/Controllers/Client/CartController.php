<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Cart\AddCartItemRequest;
use App\Http\Requests\Client\Cart\UpdateCartRequest;
use App\Services\Client\Cart\ClientCartService;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(protected ClientCartService $cart)
    {
    }

    public function index()
    {
        return view('client.cart.index', $this->cart->indexData((int) Auth::id()));
    }

    public function add(AddCartItemRequest $request)
    {
        try {
            $this->cart->add((int) Auth::id(), (int) $request->input('variant_id'), (int) $request->input('quantity'));

            return redirect()->route('client.cart.index')->with('success', 'Đã thêm vào giỏ hàng.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(UpdateCartRequest $request)
    {
        try {
            $this->cart->update((int) Auth::id(), $request->quantities());

            return redirect()->route('client.cart.index')->with('success', 'Đã cập nhật giỏ hàng.');
        } catch (RuntimeException $e) {
            return redirect()->route('client.cart.index')->with('error', $e->getMessage());
        }
    }

    public function remove($id)
    {
        try {
            $this->cart->remove((int) Auth::id(), (int) $id);

            return redirect()->route('client.cart.index')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
        } catch (RuntimeException $e) {
            return redirect()->route('client.cart.index')->with('error', $e->getMessage());
        }
    }
}
