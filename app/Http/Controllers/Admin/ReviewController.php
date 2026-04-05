<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with([
            'user',
            'product',
            'variant.color',
            'variant.size',
            'orderDetail',
            'admin',
        ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhere('admin_reply', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->status === 'replied') {
            $query->whereNotNull('admin_reply');
        } elseif ($request->status === 'unreplied') {
            $query->whereNull('admin_reply');
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        $reviews = $query->latest()->paginate(10);

        return view('admin.comments.index', compact('reviews'));
    }

    public function trash()
    {
        $trashedReviews = Review::onlyTrashed()
            ->with([
                'user',
                'product',
                'variant.color',
                'variant.size',
                'orderDetail',
                'admin',
            ])
            ->latest('deleted_at')
            ->paginate(10);

        return view('admin.comments.trash', compact('trashedReviews'));
    }

    public function edit(Review $review)
    {
        $review->loadMissing([
            'user',
            'product',
            'variant.color',
            'variant.size',
            'orderDetail',
            'admin',
        ]);

        return view('admin.comments.edit-reply', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'admin_reply' => 'required|string|max:1000',
        ]);

        $review->admin_reply = trim((string) $request->admin_reply);
        $review->admin_id = Auth::id();
        $review->save();

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Phản hồi đã được lưu.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Đã chuyển bình luận vào thùng rác.');
    }

    public function restore($id)
    {
        $review = Review::onlyTrashed()->findOrFail($id);
        $review->restore();

        return back()->with('success', 'Đã khôi phục bình luận.');
    }

    public function bulkRestore(Request $request)
    {
        $ids = collect($request->input('ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Vui lòng chọn ít nhất 1 bình luận để khôi phục.');
        }

        Review::onlyTrashed()
            ->whereIn('id', $ids)
            ->restore();

        return back()->with('success', 'Đã khôi phục các bình luận đã chọn.');
    }

    public function forceDelete($id)
    {
        $review = Review::onlyTrashed()->findOrFail($id);
        $review->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn bình luận.');
    }
}
