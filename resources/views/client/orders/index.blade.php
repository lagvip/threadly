@extends('client.account._layout')

@section('account_content')
<style>
    .orders-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 6px 24px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .order-item {
        border: 1px solid #eef2f7;
        border-radius: 18px;
        padding: 18px;
        background: #fff;
    }

    .order-thumb {
        width: 88px;
        height: 88px;
        border-radius: 14px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        flex-shrink: 0;
    }

    .order-thumb-placeholder {
        width: 88px;
        height: 88px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 12px;
        flex-shrink: 0;
    }

    .order-product-name {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .order-product-meta {
        color: #64748b;
        font-size: 13px;
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
        background: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
    }

    .order-btn-muted:hover {
        background: #e2e8f0 !important;
        border-color: #94a3b8 !important;
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

    .order-btn-danger {
        background: #ef4444 !important;
        border: 1px solid #ef4444 !important;
        color: #fff !important;
    }

    .order-btn-danger:hover {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
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

    .order-btn-confirm {
        background: #16a34a !important;
        border: 1px solid #16a34a !important;
        color: #fff !important;
    }

    .order-btn-confirm:hover {
        background: #15803d !important;
        border-color: #15803d !important;
        color: #fff !important;
    }

    .order-total {
        color: #ee4d2d;
    }

    .order-review-hint {
        color: #0ea5e9;
        font-size: 13px;
        font-weight: 600;
    }

    .order-confirmed-hint {
        color: #16a34a;
        font-size: 13px;
        font-weight: 600;
    }

    .order-ghn-hint {
        color: #2563eb;
        font-size: 13px;
        font-weight: 600;
    }

    .order-action-row .btn {
        min-height: 32px;
    }
</style>

<div class="card orders-card">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Đơn hàng của tôi</h4>
                <p class="text-muted mb-0">
                    Theo dõi trạng thái, mua lại, thanh toán lại, xác nhận nhận hàng và đánh giá sau khi đơn đã giao thành công.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
        @endif

        @if($orders->isEmpty())
            <div class="text-muted">Bạn chưa có đơn hàng nào.</div>
        @else
            <div class="row g-3">
                @foreach($orders as $order)
                    @php
                        $firstItem = $order->details->first();

                        $variantImage = optional(optional($firstItem)->variant)->image;
                        $productImage = optional(optional($firstItem)->product)->image_primary;
                        $thumb = $variantImage ?: $productImage;

                        $thumbUrl = $thumb ? asset('storage/' . $thumb) : null;

                        $otherCount = max(($order->details->count() - 1), 0);
                    @endphp

                    <div class="col-12">
                        <div class="order-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <div class="fw-bold fs-6">{{ $order->order_code }}</div>

                                    <div class="text-muted small">
                                        Ngày tạo: {{ optional($order->created_at)->format('d/m/Y H:i') }}
                                    </div>

                                    <div class="text-muted small">
                                        Phương thức: {{ strtoupper($order->payment_method) }}
                                    </div>

                                    @if($order->ghn_order_code)
                                        <div class="order-ghn-hint mt-1">
                                            GHN: {{ $order->ghn_order_code }}
                                            @if($order->ghn_status_group)
                                                • {{ $order->ghn_status_group }}
                                            @endif
                                        </div>
                                    @endif

                                    @if($order->customer_confirmed_at)
                                        <div class="order-confirmed-hint mt-1">
                                            Đã xác nhận nhận hàng lúc {{ $order->customer_confirmed_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif

                                    @if($order->can_review)
                                        <div class="order-review-hint mt-1">
                                            @if($order->has_pending_review)
                                                Chờ đánh giá {{ $order->pending_review_count }} sản phẩm
                                            @else
                                                Bạn đã đánh giá hết sản phẩm trong đơn này
                                            @endif
                                        </div>
                                    @endif
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

                            <div class="d-flex gap-3 align-items-start mb-3">
                                @if($thumbUrl)
                                    <img src="{{ $thumbUrl }}"
                                         alt="{{ optional($firstItem)->product_name ?? 'Sản phẩm' }}"
                                         class="order-thumb">
                                @else
                                    <div class="order-thumb-placeholder">No image</div>
                                @endif

                                <div class="flex-grow-1 min-w-0">
                                    <div class="order-product-name">
                                        {{ optional($firstItem)->product_name ?? 'Không có thông tin sản phẩm' }}
                                    </div>

                                    <div class="order-product-meta mb-1">
                                        @if($firstItem)
                                            SL: {{ $firstItem->quantity }}

                                            @if($otherCount > 0)
                                                • và {{ $otherCount }} sản phẩm khác
                                            @endif
                                        @endif
                                    </div>

                                    <div class="order-product-meta">
                                        {{ $order->address }}
                                    </div>
                                </div>

                                <div class="text-md-end text-nowrap">
                                    <div class="small text-muted mb-1">Tổng tiền</div>
                                    <div class="fs-5 fw-bold order-total">
                                        {{ number_format($order->total_price, 0, ',', '.') }} đ
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 pt-2 border-top order-action-row">
                                <a href="{{ route('client.orders.show', $order->id) }}"
                                   class="btn order-btn-muted btn-sm rounded-pill px-3">
                                    Xem chi tiết
                                </a>

                                <form action="{{ route('client.orders.reorder', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn order-btn-main btn-sm rounded-pill px-3">
                                        Mua lại
                                    </button>
                                </form>

                                @if($order->can_confirm_received)
                                    <form method="POST"
                                          action="{{ route('client.orders.confirm-received', $order->id) }}"
                                          onsubmit="return confirm('Bạn xác nhận đã nhận được hàng?')">
                                        @csrf
                                        <button type="submit" class="btn order-btn-confirm btn-sm rounded-pill px-3">
                                            Xác nhận đã nhận hàng
                                        </button>
                                    </form>
                                @endif

                                @if($order->can_review)
                                    <a href="{{ route('client.orders.show', $order->id) }}#review-section"
                                       class="btn order-btn-review btn-sm rounded-pill px-3">
                                        {{ $order->has_pending_review ? 'Đánh giá' : 'Xem đánh giá' }}
                                    </a>
                                @endif

                                @if($order->can_repay)
                                    <form action="{{ route('client.orders.repay-vnpay', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn order-btn-warning btn-sm rounded-pill px-3">
                                            Thanh toán lại
                                        </button>
                                    </form>
                                @endif

                                @if($order->can_cancel)
                                    <button type="button"
                                            class="btn order-btn-danger btn-sm rounded-pill px-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#cancelOrderModal"
                                            data-cancel-url="{{ route('client.orders.cancel', $order->id) }}"
                                            data-order-code="{{ $order->order_code }}"
                                            data-cancel-type="{{ $order->cancel_action_type }}">
                                        Hủy đơn
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalTitle">Hủy đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <form id="cancelOrderForm" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="mb-2 text-muted small">
                        Mã đơn: <strong id="cancelOrderCode"></strong>
                    </div>

                    <div id="cancelOrderNote" class="alert alert-warning py-2 small d-none mb-3"></div>

                    <div class="mb-3">
                        <label for="cancel_reason_select" class="form-label fw-semibold">
                            Chọn lý do hủy đơn
                        </label>

                        <select id="cancel_reason_select" class="form-select" required>
                            <option value="">-- Chọn lý do --</option>
                            <option value="Tôi muốn đổi sản phẩm khác">Tôi muốn đổi sản phẩm khác</option>
                            <option value="Tôi đặt nhầm sản phẩm">Tôi đặt nhầm sản phẩm</option>
                            <option value="Tôi thay đổi nhu cầu mua hàng">Tôi thay đổi nhu cầu mua hàng</option>
                            <option value="Thời gian giao hàng quá lâu">Thời gian giao hàng quá lâu</option>
                            <option value="Tìm thấy giá tốt hơn ở nơi khác">Tìm thấy giá tốt hơn ở nơi khác</option>
                            <option value="Muốn thay đổi địa chỉ hoặc thông tin nhận hàng">Muốn thay đổi địa chỉ hoặc thông tin nhận hàng</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    <div class="mb-0 d-none" id="cancel_reason_other_wrap">
                        <label for="cancel_reason_other" class="form-label fw-semibold">
                            Nhập lý do khác
                        </label>

                        <textarea id="cancel_reason_other"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Nhập lý do hủy đơn..."></textarea>
                    </div>

                    <input type="hidden" name="cancel_reason" id="cancel_reason_final">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn order-btn-muted btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                        Đóng
                    </button>

                    <button type="submit" id="cancelOrderSubmitBtn" class="btn order-btn-danger btn-sm rounded-pill px-3">
                        Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cancelModal = document.getElementById('cancelOrderModal');
    const cancelForm = document.getElementById('cancelOrderForm');
    const cancelOrderCode = document.getElementById('cancelOrderCode');
    const cancelOrderTitle = document.getElementById('cancelOrderModalTitle');
    const cancelOrderNote = document.getElementById('cancelOrderNote');
    const cancelSubmitBtn = document.getElementById('cancelOrderSubmitBtn');

    const reasonSelect = document.getElementById('cancel_reason_select');
    const reasonOtherWrap = document.getElementById('cancel_reason_other_wrap');
    const reasonOther = document.getElementById('cancel_reason_other');
    const reasonFinal = document.getElementById('cancel_reason_final');

    cancelModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        const cancelUrl = button.getAttribute('data-cancel-url');
        const orderCode = button.getAttribute('data-order-code');
        const cancelType = button.getAttribute('data-cancel-type');

        cancelForm.setAttribute('action', cancelUrl);
        cancelOrderCode.textContent = orderCode;

        reasonSelect.value = '';
        reasonOther.value = '';
        reasonFinal.value = '';
        reasonOtherWrap.classList.add('d-none');
        reasonOther.removeAttribute('required');

        if (cancelType === 'request') {
            cancelOrderTitle.textContent = 'Gửi yêu cầu hủy đơn';
            cancelSubmitBtn.textContent = 'Gửi yêu cầu hủy';
            cancelOrderNote.classList.remove('d-none');
            cancelOrderNote.innerHTML = 'Đơn này đã thanh toán qua <strong>VNPay</strong>, nên hệ thống sẽ ghi nhận <strong>yêu cầu hủy</strong>, chưa hủy ngay.';
        } else {
            cancelOrderTitle.textContent = 'Hủy đơn hàng';
            cancelSubmitBtn.textContent = 'Xác nhận hủy';
            cancelOrderNote.classList.add('d-none');
            cancelOrderNote.innerHTML = '';
        }
    });

    reasonSelect.addEventListener('change', function () {
        if (this.value === 'Khác') {
            reasonOtherWrap.classList.remove('d-none');
            reasonOther.setAttribute('required', 'required');
            reasonFinal.value = '';
        } else {
            reasonOtherWrap.classList.add('d-none');
            reasonOther.removeAttribute('required');
            reasonOther.value = '';
            reasonFinal.value = this.value;
        }
    });

    cancelForm.addEventListener('submit', function (e) {
        if (!reasonSelect.value) {
            e.preventDefault();
            alert('Vui lòng chọn lý do hủy đơn.');
            return;
        }

        if (reasonSelect.value === 'Khác') {
            const otherValue = reasonOther.value.trim();

            if (!otherValue) {
                e.preventDefault();
                alert('Vui lòng nhập lý do hủy đơn.');
                return;
            }

            reasonFinal.value = otherValue;
        } else {
            reasonFinal.value = reasonSelect.value;
        }
    });
});
</script>
@endsection
