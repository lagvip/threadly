@extends('client.account._layout')

@section('account_content')
<style>
    .order-page-card {
        border: 0;
        box-shadow: 0 6px 24px rgba(15, 23, 42, .06);
        border-radius: 18px;
    }

    .order-btn-main {
        background: #ee4d2d !important;
        border: 1px solid #ee4d2d !important;
        color: #fff !important;
    }

    .order-btn-main:hover {
        background: #d83f21 !important;
        border-color: #d83f21 !important;
        color: #fff !important;
    }

    .order-btn-muted {
        background: #f8fafc !important;
        border: 1px solid #dbe2ea !important;
        color: #334155 !important;
    }

    .order-btn-muted:hover {
        background: #eef2f7 !important;
        border-color: #cfd8e3 !important;
        color: #0f172a !important;
    }

    .order-btn-warning {
        background: #f59e0b !important;
        border: 1px solid #f59e0b !important;
        color: #fff !important;
    }

    .order-btn-warning:hover {
        background: #d97706 !important;
        border-color: #d97706 !important;
        color: #fff !important;
    }

    .order-btn-refund {
        background: #7c3aed !important;
        border: 1px solid #7c3aed !important;
        color: #fff !important;
    }

    .order-btn-refund:hover {
        background: #6d28d9 !important;
        border-color: #6d28d9 !important;
        color: #fff !important;
    }

    .order-btn-review {
        background: #0ea5e9 !important;
        border: 1px solid #0ea5e9 !important;
        color: #fff !important;
    }

    .order-btn-review:hover {
        background: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #fff !important;
    }

    .review-item-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px;
        background: #fff;
    }

    .review-thumb {
        width: 84px;
        height: 84px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .review-thumb-placeholder {
        width: 84px;
        height: 84px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 12px;
    }

    .review-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .review-status-chip.done {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }

    .review-status-chip.pending {
        background: #eff6ff;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }

    .review-stars {
        color: #f59e0b;
        letter-spacing: 2px;
        font-size: 15px;
    }

    .review-admin-reply {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
    }
</style>

@if(session('success'))
    <div class="alert alert-success rounded-4">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
@endif

<div class="card order-page-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h4 class="mb-1">Chi tiết đơn hàng</h4>
                <div class="text-muted">Mã đơn: <strong>{{ $order->order_code }}</strong></div>
            </div>

            <div class="text-end">
                <div class="mb-1">
                    <span class="badge bg-{{ $order->payment_status_badge }}">
                        {{ $order->payment_status_label }}
                    </span>
                </div>
                <div>
                    <span class="badge bg-{{ $order->order_status_badge }}">
                        {{ $order->order_status_label }}
                    </span>
                </div>
            </div>
        </div>

        <hr>

        <div class="row g-3">
            <div class="col-md-6">
                <div><strong>Người nhận:</strong> {{ $order->name }}</div>
                <div><strong>Số điện thoại:</strong> {{ $order->phone }}</div>
                <div><strong>Email:</strong> {{ $order->email ?: 'Không có' }}</div>
                <div><strong>Ngày tạo:</strong> {{ optional($order->created_at)->format('d/m/Y H:i') }}</div>
            </div>

            <div class="col-md-6">
                <div><strong>Thanh toán:</strong> {{ strtoupper($order->payment_method) }}</div>
                <div><strong>Địa chỉ:</strong> {{ $order->address }}</div>

                @if($order->cancel_reason)
                    <div class="mt-2 text-danger">
                        <strong>Lý do hủy:</strong> {{ $order->cancel_reason }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card order-page-card mb-4">
    <div class="card-body p-4">
        <h5 class="mb-3">Sản phẩm trong đơn</h5>

        @foreach($order->details as $item)
            @php
                $variantImage = $item->variant->image ?? null;
                $productImage = optional($item->product)->image_primary;
                $image = $variantImage ?: $productImage;
                $imageUrl = $image ? asset('storage/' . $image) : null;
                $itemReview = $order->reviews->firstWhere('order_detail_id', $item->id);
            @endphp

            <div class="d-flex gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div style="width:84px;flex:0 0 84px;">
                    @if($imageUrl)
                        <img src="{{ $imageUrl }}"
                             alt="{{ $item->product_name }}"
                             class="img-fluid rounded-3 border"
                             style="width:84px;height:84px;object-fit:cover;">
                    @else
                        <div class="rounded-3 border bg-light d-flex align-items-center justify-content-center"
                             style="width:84px;height:84px;">
                            <span class="text-muted small">Không có ảnh</span>
                        </div>
                    @endif
                </div>

                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $item->product_name }}</div>

                    <div class="text-muted small">
                        @if(optional($item->variant)->color?->name)
                            Màu: {{ $item->variant->color->name }}
                        @endif

                        @if(optional($item->variant)->size?->name)
                            @if(optional($item->variant)->color?->name) | @endif
                            Kích cỡ: {{ $item->variant->size->name }}
                        @endif
                    </div>

                    <div class="small text-muted mt-1">
                        SL: {{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', '.') }} đ
                    </div>

                    @if($order->can_review)
                        <div class="mt-2">
                            @if($itemReview)
                                <span class="review-status-chip done">Đã đánh giá</span>
                            @else
                                <span class="review-status-chip pending">Chưa đánh giá</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="fw-bold text-end">
                    {{ number_format($item->total, 0, ',', '.') }} đ
                </div>
            </div>
        @endforeach
    </div>
</div>

@if($order->can_review)
    <div class="card order-page-card mb-4" id="review-section">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">Đánh giá sản phẩm</h5>
                    <p class="text-muted mb-0">Bạn chỉ có thể đánh giá khi đơn đã giao và đã thanh toán thành công.</p>
                </div>
                @if($order->has_pending_review)
                    <span class="review-status-chip pending">Còn {{ $order->pending_review_count }} sản phẩm chưa đánh giá</span>
                @else
                    <span class="review-status-chip done">Đã đánh giá đầy đủ</span>
                @endif
            </div>

            @forelse($reviewItems as $item)
                @php
                    $existingReview = $item->existing_review;
                    $image = optional($item->variant)->image ?: optional($item->product)->image_primary;
                    $imageUrl = $image ? asset('storage/' . $image) : null;
                    $isCurrentForm = (string) old('review_detail_id') === (string) $item->id;
                @endphp

                <div class="review-item-card {{ !$loop->last ? 'mb-3' : '' }}">
                    <div class="d-flex gap-3 align-items-start flex-wrap">
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="review-thumb">
                        @else
                            <div class="review-thumb-placeholder">Không có ảnh</div>
                        @endif

                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1">{{ $item->product_name }}</div>
                            <div class="text-muted small mb-2">
                                @if(optional($item->variant)->color?->name)
                                    Màu: {{ $item->variant->color->name }}
                                @endif
                                @if(optional($item->variant)->size?->name)
                                    @if(optional($item->variant)->color?->name) | @endif
                                    Kích cỡ: {{ $item->variant->size->name }}
                                @endif
                            </div>

                            @if($existingReview)
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="review-status-chip done">Đã đánh giá</span>
                                    <span class="review-stars">{{ str_repeat('★', (int) $existingReview->rating) }}{{ str_repeat('☆', max(5 - (int) $existingReview->rating, 0)) }}</span>
                                    <span class="text-muted small">{{ optional($existingReview->updated_at)->format('d/m/Y H:i') }}</span>
                                </div>
                            @else
                                <span class="review-status-chip pending">Chưa đánh giá</span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('client.orders.reviews.submit', ['id' => $order->id, 'detailId' => $item->id]) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="review_detail_id" value="{{ $item->id }}">

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Đánh giá</label>
                                <select name="rating" class="form-select rounded-3 {{ $isCurrentForm && $errors->has('rating') ? 'is-invalid' : '' }}">
                                    <option value="">-- Chọn số sao --</option>
                                    @for($star = 5; $star >= 1; $star--)
                                        <option value="{{ $star }}"
                                            {{ (string) old('rating', optional($existingReview)->rating) === (string) $star ? 'selected' : '' }}>
                                            {{ $star }} sao
                                        </option>
                                    @endfor
                                </select>
                                @if($isCurrentForm)
                                    @error('rating')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Nội dung đánh giá</label>
                                <textarea name="comment"
                                          rows="4"
                                          class="form-control rounded-3 {{ $isCurrentForm && $errors->has('comment') ? 'is-invalid' : '' }}"
                                          placeholder="Chia sẻ cảm nhận của bạn về sản phẩm này...">{{ old('comment', optional($existingReview)->comment) }}</textarea>
                                @if($isCurrentForm)
                                    @error('comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                            <div class="text-muted small">
                                @if($existingReview)
                                    Bạn có thể cập nhật lại đánh giá nếu muốn.
                                @else
                                    Đánh giá của bạn sẽ được hiển thị cho sản phẩm sau khi lưu.
                                @endif
                            </div>

                            <button type="submit" class="btn order-btn-review rounded-pill px-4">
                                {{ $existingReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
                            </button>
                        </div>

                        @if($existingReview && $existingReview->admin_reply)
                            <div class="review-admin-reply mt-3">
                                <div class="fw-semibold mb-1">Phản hồi từ admin</div>
                                <div class="text-muted">{{ $existingReview->admin_reply }}</div>
                            </div>
                        @endif
                    </form>
                </div>
            @empty
                <div class="text-muted">Không có sản phẩm nào đủ điều kiện để đánh giá trong đơn này.</div>
            @endforelse
        </div>
    </div>
@endif

<div class="card order-page-card">
    <div class="card-body p-4">
        <h5 class="mb-3">Tổng kết</h5>

        <div class="d-flex justify-content-between mb-2">
            <span>Phí ship</span>
            <strong>{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</strong>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span>Giảm giá</span>
            <strong>- {{ number_format($order->discount, 0, ',', '.') }} đ</strong>
        </div>


        @if(($order->refund_status ?? 'none') !== 'none')
            <div class="d-flex justify-content-between mb-2">
                <span>Trạng thái hoàn tiền</span>
                <strong>{{ $order->refund_status_label }}</strong>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>Đã hoàn vào ví demo</span>
                <strong class="text-danger">- {{ number_format($order->refunded_amount, 0, ',', '.') }} đ</strong>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>Còn có thể hoàn sản phẩm</span>
                <strong>{{ number_format($order->refundable_amount, 0, ',', '.') }} đ</strong>
            </div>
        @endif

        <div class="d-flex justify-content-between fs-5 mt-3 pt-3 border-top">
            <span class="fw-bold">Tổng khách đã thanh toán</span>
            <span class="fw-bold text-danger">{{ number_format($order->total_price, 0, ',', '.') }} đ</span>
        </div>

        @if((float) ($order->refunded_amount ?? 0) > 0)
            <div class="d-flex justify-content-between fs-5 mt-2">
                <span class="fw-bold">Thực thu sau hoàn</span>
                <span class="fw-bold text-success">{{ number_format($order->net_paid_amount, 0, ',', '.') }} đ</span>
            </div>
        @endif

        <div class="mt-4 d-flex flex-wrap gap-2">
            <a href="{{ route('client.orders.index') }}" class="btn order-btn-muted rounded-pill px-4">
                Quay lại danh sách
            </a>

            <form action="{{ route('client.orders.reorder', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn order-btn-main rounded-pill px-4">
                    Mua lại
                </button>
            </form>


            @if($order->can_request_refund)
                <a href="{{ route('client.refunds.create', $order->id) }}" class="btn order-btn-refund rounded-pill px-4">
                    Yêu cầu hoàn tiền
                </a>
            @endif

            @if($order->can_review)
                <a href="#review-section" class="btn order-btn-review rounded-pill px-4">
                    {{ $order->has_pending_review ? 'Đánh giá' : 'Xem đánh giá' }}
                </a>
            @endif

            @if($order->can_repay)
                <form action="{{ route('client.orders.repay-vnpay', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn order-btn-warning rounded-pill px-4">
                        Thanh toán lại
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
