@extends('admin.layouts.layout')

<style>
    .table td,
    .table th {
        vertical-align: middle;
    }

    .filter-bar .form-control,
    .filter-bar .form-select {
        min-width: 200px;
    }

    .review-product-cell {
        max-width: 360px;
    }

    .review-product-box {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .review-product-thumb-wrap {
        width: 68px;
        height: 68px;
        border-radius: 12px;
        overflow: hidden;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        flex: 0 0 68px;
    }

    .review-product-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .review-product-info {
        min-width: 0;
    }

    .review-product-name {
        font-weight: 600;
        color: #212529;
        margin-bottom: 6px;
        line-height: 1.45;
        word-break: break-word;
    }

    .review-variant-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .review-variant-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        font-size: 12px;
        font-weight: 600;
        color: #495057;
    }

    .review-user-name {
        font-weight: 600;
        color: #212529;
        word-break: break-word;
    }

    .review-user-sub {
        font-size: 12px;
        color: #6c757d;
        margin-top: 2px;
    }

    .rating-stars {
        display: inline-flex;
        gap: 2px;
        font-size: 14px;
        white-space: nowrap;
    }

    .review-comment-box,
    .review-reply-box {
        white-space: normal;
        word-break: break-word;
        line-height: 1.6;
        max-width: 320px;
    }

    .reply-meta {
        font-size: 12px;
        color: #6c757d;
        margin-top: 6px;
        display: block;
    }

    .reply-empty {
        color: #dc3545;
        font-weight: 600;
        font-size: 13px;
    }

    .table-action-cell {
        white-space: nowrap;
        width: 150px;
        vertical-align: middle !important;
    }

    .table-action-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .table-action-group form {
        margin: 0;
    }

    .table-action-group .btn {
        min-width: 64px;
    }
</style>

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center gap-1 flex-wrap">
                    <h4 class="card-title flex-grow-1 mb-0">Quản lý đánh giá sản phẩm</h4>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('reviews.trash') }}" class="btn btn-soft-danger btn-sm">
                            Thùng rác
                        </a>
                    </div>
                </div>

                <div class="card-body border-bottom">
                    <form action="{{ route('reviews.index') }}" method="GET">
                        <div class="row g-2 align-items-end filter-bar">
                            <div class="col-md-4">
                                <label class="form-label">Tìm kiếm</label>
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="Tên sản phẩm, người dùng, bình luận..."
                                       value="{{ request('search') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Trạng thái phản hồi</label>
                                <select name="status" class="form-select">
                                    <option value="">-- Tất cả --</option>
                                    <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>
                                        Đã phản hồi
                                    </option>
                                    <option value="unreplied" {{ request('status') == 'unreplied' ? 'selected' : '' }}>
                                        Chưa phản hồi
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Số sao</label>
                                <select name="rating" class="form-select">
                                    <option value="">-- Tất cả --</option>
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ (string) request('rating') === (string) $i ? 'selected' : '' }}>
                                            {{ $i }} sao
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        Lọc
                                    </button>

                                    <a href="{{ route('reviews.index') }}" class="btn btn-light w-100">
                                        Bỏ lọc
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>Người dùng</th>
                                <th>Sản phẩm</th>
                                <th>Đánh giá</th>
                                <th>Bình luận</th>
                                <th>Phản hồi</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($reviews as $review)
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
                                        ?: 'Không có tên';
                                @endphp

                                <tr>
                                    <td>{{ $review->id }}</td>

                                    <td>
                                        <div class="review-user-name">{{ $reviewUserName }}</div>
                                        <div class="review-user-sub">
                                            {{ optional($review->created_at)->format('d/m/Y H:i') }}
                                        </div>
                                    </td>

                                    <td class="review-product-cell">
                                        <div class="review-product-box">
                                            <div class="review-product-thumb-wrap">
                                                <img src="{{ $reviewImageUrl }}"
                                                     alt="{{ $review->product->name ?? 'Sản phẩm' }}"
                                                     class="review-product-thumb">
                                            </div>

                                            <div class="review-product-info">
                                                <div class="review-product-name">
                                                    {{ $review->product->name ?? 'Sản phẩm đã bị xóa' }}
                                                </div>

                                                @if($reviewColor || $reviewSize)
                                                    <div class="review-variant-meta">
                                                        @if($reviewColor)
                                                            <span class="review-variant-chip">Màu: {{ $reviewColor }}</span>
                                                        @endif

                                                        @if($reviewSize)
                                                            <span class="review-variant-chip">Size: {{ $reviewSize }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="rating-stars">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star text-warning"></i>
                                            @endfor
                                        </div>
                                    </td>

                                    <td>
                                        <div class="review-comment-box">
                                            {{ $review->comment ?? 'Không có bình luận' }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="review-reply-box">
                                            @if ($review->admin_reply)
                                                <strong>{{ $review->admin->name ?? 'Admin' }}:</strong>
                                                {{ $review->admin_reply }}
                                                <span class="reply-meta">
                                                    {{ optional($review->updated_at)->format('d/m/Y H:i') }}
                                                </span>
                                            @else
                                                <span class="reply-empty">Chưa phản hồi</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="table-action-cell">
                                        <div class="table-action-group">
                                            @if ($review->admin_reply)
                                                <a href="{{ route('reviews.edit', $review->id) }}"
                                                   class="btn btn-sm btn-warning">
                                                    Sửa
                                                </a>
                                            @else
                                                <a href="{{ route('reviews.edit', $review->id) }}"
                                                   class="btn btn-sm btn-success">
                                                    Phản hồi
                                                </a>
                                            @endif

                                            <form action="{{ route('reviews.destroy', $review->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Chuyển bình luận vào thùng rác?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Xóa</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Không có đánh giá nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $reviews->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
