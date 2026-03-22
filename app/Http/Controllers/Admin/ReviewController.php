<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Danh sách đánh giá
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product']);

        if ($request->filled('search')) {
            $query->where('comment', 'like', "%{$request->search}%");
        }

        if ($request->status === 'replied') {
            $query->whereNotNull('admin_reply');
        } elseif ($request->status === 'unreplied') {
            $query->whereNull('admin_reply');
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->paginate(10);

        return view('admin.comments.index', compact('reviews'));
    }

    // Hiển thị form phản hồi hoặc sửa phản hồi
    public function edit(Review $review)
    {
        return view('admin.comments.edit-reply', compact('review'));
    }

    // Xử lý lưu phản hồi
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'admin_reply' => 'required|string|max:1000'
        ]);

        $review->admin_reply = $request->admin_reply;
        $review->admin_id = Auth::id();
        $review->save();

        return redirect()->route('reviews.index')->with('success', 'Phản hồi đã được lưu.');
    }

    // Xóa đánh giá
    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Đã xóa đánh giá.');
    }
}
