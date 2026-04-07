@extends('admin.layouts.layout')

<style>
    .review-detail-box {
        border: 1px solid #e9ecef;
        border-radius: 16px;
        padding: 18px;
        background: #fff;
        margin-bottom: 20px;
    }

    .review-detail-product {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .review-detail-thumb-wrap {
        width: 96px;
        height: 96px;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        background: #f8f9fa;
        flex: 0 0 96px;
    }

    .review-detail-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .review-detail-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #212529;
        line-height: 1.45;
    }

    .review-detail-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }

    .review-meta-chip {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
    }

    .review-comment-view {
        padding: 14px 16px;
        border-radius: 12px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        line-height: 1.65;
        color: #495057;
    }

    .reply-form-card textarea.form-control {
        min-height: 140px;
        resize: vertical;
    }

    .review-rating-stars {
        display: inline-flex;
        gap: 3px;
        font-size: 15px;
    }

    .review-user-line {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 10px;
    }
</style>

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-10 col-lg-11">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <h4 class="card-title mb-0">
                        {{ $review->admin_reply ? 'Sửa phản hồi đánh giá' : 'Phản hồi đánh giá' }}
                    </h4>
                </div>

                <div class="card-body">
                    @php
                        $reviewColor = $review->color_snapshot ?? optional($review->variant)->color?->name;
                        $reviewSize = $review->size_snapshot ?? optional($review->variant)->size?->name;

                        $reviewImage = optional($review->variant)->image
                            ?: optional($review->product)->image_primary;

                        $reviewImageUrl = $reviewImage
                            ? asset('storage/' . $reviewImage)
                            : asset('images/placeholder-80x80.png');

                        $reviewUserName = $review->user->name
                            ?? trim(($review->user->first_name ?? '') . ' ' . ($review->user->last_name ?? ''))
                            ?: 'Khách hàng';
                    @endphp

                    <div class="review-detail-box">
                        <div class="review-detail-product">
                            <div class="review-detail-thumb-wrap">
                                <img src="{{ $reviewImageUrl }}"
                                     alt="{{ $review->product->name ?? 'Sản phẩm' }}"
                                     class="review-detail-thumb">
                            </div>

                            <div class="flex-grow-1">
                                <div class="review-detail-title">
                                    {{ $review->product->name ?? 'Sản phẩm đã bị xóa' }}
                                </div>

                                <div class="review-detail-meta">
                                    @if($reviewColor)
                                        <span class="review-meta-chip">Màu: {{ $reviewColor }}</span>
                                    @endif

                                    @if($reviewSize)
                                        <span class="review-meta-chip">Size: {{ $reviewSize }}</span>
                                    @endif

                                    <span class="review-meta-chip">
                                        {{ optional($review->created_at)->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                <div class="review-user-line">
                                    <strong>Người dùng:</strong> {{ $reviewUserName }}
                                </div>

                                <div class="mb-3">
                                    <strong class="d-block mb-2">Đánh giá</strong>
                                    <div class="review-rating-stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star text-warning"></i>
                                        @endfor
                                    </div>
                                </div>

                                <div>
                                    <strong class="d-block mb-2">Nội dung bình luận</strong>
                                    <div class="review-comment-view">
                                        {{ $review->comment ?? 'Không có bình luận' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('reviews.update', $review->id) }}" method="POST" class="reply-form-card">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="admin_reply" class="form-label fw-semibold">
                                Phản hồi của admin
                            </label>
                            <textarea name="admin_reply"
                                      id="admin_reply"
                                      rows="5"
                                      class="form-control @error('admin_reply') is-invalid @enderror"
                                      placeholder="Nhập nội dung phản hồi cho khách hàng...">{{ old('admin_reply', $review->admin_reply) }}</textarea>

                            @error('admin_reply')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('reviews.index') }}" class="btn btn-light">
                                Quay lại
                            </a>

                            <button class="btn btn-primary">
                                {{ $review->admin_reply ? 'Cập nhật phản hồi' : 'Lưu phản hồi' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
