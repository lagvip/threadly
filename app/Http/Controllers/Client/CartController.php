<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('id_user', Auth::id())->first();

        $cartItems = collect();
        $selectedCartItemIds = [];

        if ($cart) {
            $cartItems = CartDetail::with([
                'variant.product',
                'variant.color',
                'variant.size'
            ])->where('id_cart', $cart->id)->get();

            $selectedCartItemIds = session('checkout_selected_items', []);
            $selectedCartItemIds = collect($selectedCartItemIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $cartItems->contains('id', $id))
                ->values()
                ->toArray();

            if (empty($selectedCartItemIds) && $cartItems->isNotEmpty()) {
                $selectedCartItemIds = $cartItems->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->toArray();
            }
        }

        return view('client.cart.index', compact('cartItems', 'cart', 'selectedCartItemIds'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);

        if ($variant->quantity < 1) {
            return redirect()
                ->back()
                ->with('error', 'Biến thể này đã hết hàng.');
        }

        $cart = Cart::firstOrCreate([
            'id_user' => Auth::id(),
        ]);

        $cartDetail = CartDetail::where('id_cart', $cart->id)
            ->where('id_variant', $variant->id)
            ->first();

        $newQty = ($cartDetail ? $cartDetail->quantity : 0) + (int) $request->quantity;

        if ($newQty > $variant->quantity) {
            return redirect()
                ->back()
                ->with('error', 'Số lượng vượt quá tồn kho.');
        }

        if ($cartDetail) {
            $cartDetail->update([
                'quantity' => $newQty
            ]);
        } else {
            CartDetail::create([
                'id_cart'    => $cart->id,
                'id_variant' => $variant->id,
                'quantity'   => (int) $request->quantity,
            ]);
        }

        return redirect()
            ->route('client.cart.index')
            ->with('success', 'Đã thêm vào giỏ hàng.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'quantities'   => 'required|array',
            'quantities.*' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('id_user', Auth::id())->first();

        if (!$cart) {
            return redirect()
                ->route('client.cart.index')
                ->with('error', 'Không tìm thấy giỏ hàng.');
        }

        $cartItems = CartDetail::with(['variant.product'])
            ->where('id_cart', $cart->id)
            ->whereIn('id', array_keys($request->quantities))
            ->get();

        foreach ($cartItems as $item) {
            $newQty = (int) ($request->quantities[$item->id] ?? 1);
            $stock  = (int) ($item->variant->quantity ?? 0);

            if ($stock < 1) {
                $item->delete();
                continue;
            }

            if ($newQty > $stock) {
                return redirect()
                    ->route('client.cart.index')
                    ->with('error', 'Sản phẩm "' . ($item->variant->product->name ?? 'N/A') . '" vượt quá tồn kho.');
            }

            $item->update([
                'quantity' => $newQty
            ]);
        }

        $this->syncSelectedCheckoutItems($cart);

        return redirect()
            ->route('client.cart.index')
            ->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function remove($id)
    {
        $cart = Cart::where('id_user', Auth::id())->first();

        if (!$cart) {
            return redirect()
                ->route('client.cart.index')
                ->with('error', 'Không tìm thấy giỏ hàng.');
        }

        $item = CartDetail::where('id_cart', $cart->id)
            ->where('id', $id)
            ->firstOrFail();

        $item->delete();

        $selectedIds = collect(session('checkout_selected_items', []))
            ->map(fn ($itemId) => (int) $itemId)
            ->reject(fn ($itemId) => $itemId === (int) $id)
            ->values()
            ->toArray();

        if (empty($selectedIds)) {
            session()->forget('checkout_selected_items');
        } else {
            session(['checkout_selected_items' => $selectedIds]);
        }

        return redirect()
            ->route('client.cart.index')
            ->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }
    protected function syncSelectedCheckoutItems(Cart $cart): void
    {
        $selectedIds = collect(session('checkout_selected_items', []))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($selectedIds->isEmpty()) {
            return;
        }

        $validIds = CartDetail::where('id_cart', $cart->id)
            ->whereIn('id', $selectedIds->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        if (empty($validIds)) {
            session()->forget('checkout_selected_items');
            return;
        }

        session(['checkout_selected_items' => $validIds]);
    }
}
