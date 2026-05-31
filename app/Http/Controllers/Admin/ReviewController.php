<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reviews\BulkReviewIdsRequest;
use App\Http\Requests\Admin\Reviews\IndexReviewsRequest;
use App\Http\Requests\Admin\Reviews\UpdateReviewReplyRequest;
use App\Models\Review;
use App\Services\Admin\Reviews\AdminReviewService;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(protected AdminReviewService $reviews)
    {
    }

    public function index(IndexReviewsRequest $request)
    {
        $this->authorize('viewAny', Review::class);

        return view('admin.comments.index', $this->reviews->indexData($request->filters()));
    }

    public function trash()
    {
        $this->authorize('viewAny', Review::class);

        return view('admin.comments.trash', $this->reviews->trashData());
    }

    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        return view('admin.comments.edit-reply', ['review' => $this->reviews->loadForEdit($review)]);
    }

    public function update(UpdateReviewReplyRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $this->reviews->reply($review, (string) $request->input('admin_reply'), (int) Auth::id());

        return redirect()->route('reviews.index')->with('success', 'Phản hồi đã được lưu.');
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $this->reviews->softDelete($review);

        return back()->with('success', 'Đã chuyển bình luận vào thùng rác.');
    }

    public function restore($id)
    {
        $this->authorize('restore', Review::class);

        $this->reviews->restore((int) $id);

        return back()->with('success', 'Đã khôi phục bình luận.');
    }

    public function bulkRestore(BulkReviewIdsRequest $request)
    {
        $this->authorize('restore', Review::class);

        $ids = $request->ids();

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất 1 bình luận để khôi phục.');
        }

        $this->reviews->bulkRestore($ids);

        return back()->with('success', 'Đã khôi phục các bình luận đã chọn.');
    }

    public function forceDelete($id)
    {
        $this->authorize('forceDelete', Review::class);

        $this->reviews->forceDelete((int) $id);

        return back()->with('success', 'Đã xóa vĩnh viễn bình luận.');
    }
}
