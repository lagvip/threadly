<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Wishlist\StoreWishlistRequest;
use App\Services\Client\Wishlist\ClientWishlistService;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class WishlistController extends Controller
{
    public function __construct(protected ClientWishlistService $wishlists)
    {
    }

    public function index()
    {
        return view('client.wishlist', $this->wishlists->indexData((int) Auth::id()));
    }

    public function store(StoreWishlistRequest $request)
    {
        try {
            $this->wishlists->add((int) Auth::id(), (int) $request->input('variant_id'));

            return back()->with('success', 'Đã thêm vào danh sách yêu thích.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->wishlists->remove((int) Auth::id(), (int) $id);

        return back()->with('success', 'Đã xóa khỏi danh sách yêu thích.');
    }
}
