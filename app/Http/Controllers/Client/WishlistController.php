<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::with([
                'variant.product.category',
                'variant.product.brand',
                'variant.color',
                'variant.size',
            ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->filter(function ($item) {
                return $item->variant && $item->variant->product;
            })
            ->values();

        return view('client.wishlist', compact('wishlists'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
        ], [
            'variant_id.required' => 'Vui lòng chọn màu và kích thước trước khi thêm vào yêu thích.',
            'variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
        ]);

        $variant = ProductVariant::with('product')->findOrFail($request->variant_id);

        if ($variant->status !== 'active') {
            return back()->with('error', 'Biến thể này hiện không khả dụng.');
        }

        Wishlist::firstOrCreate([
            'user_id' => Auth::id(),
            'product_variant_id' => $variant->id,
        ]);

        return back()->with('success', 'Đã thêm vào danh sách yêu thích.');
    }

    public function destroy($id)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())->findOrFail($id);
        $wishlist->delete();

        return back()->with('success', 'Đã xóa khỏi danh sách yêu thích.');
    }
}
