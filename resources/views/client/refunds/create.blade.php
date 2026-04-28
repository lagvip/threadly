@extends('client.account._layout')

@section('account_content')
<style>
    .refund-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 6px 24px rgba(15, 23, 42, .06);
    }

    .refund-btn-main {
        background: #7c3aed !important;
        border: 1px solid #7c3aed !important;
        color: #fff !important;
    }

    .refund-btn-main:hover {
        background: #6d28d9 !important;
        border-color: #6d28d9 !important;
        color: #fff !important;
    }

    .refund-btn-muted {
        background: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
    }

    .refund-summary-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
    }

    .refund-item-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 14px;
        background: #fff;
    }

    .refund-item-card.disabled {
        background: #f8fafc;
        opacity: .65;
    }

    .refund-item-money {
        color: #7c3aed;
        font-weight: 700;
    }
</style>

@if(session('success'))
    <div class="alert alert-success rounded-4">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
@endif

@php
    $refundReasons = [
        'Sản phẩm bị lỗi / hư hỏng',
        'Giao sai sản phẩm',
        'Sản phẩm không đúng mô tả',
        'Thiếu sản phẩm trong đơn hàng',
        'Sản phẩm không vừa size / không phù hợp',
        'Đơn hàng giao quá lâu',
        'Không còn nhu cầu sử dụng sản phẩm',
    ];
@endphp

<div class="card refund-card">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h4 class="mb-1">Yêu cầu hoàn tiền</h4>
                <p class="text-muted mb-0">Hoàn tiền demo vào ví website cho đơn VNPay/COD đã thanh toán. Hệ thống chỉ hoàn giá trị sản phẩm, không hoàn phí vận chuyển.</p>
            </div>
            <a href="{{ route('client.orders.index') }}" class="btn refund-btn-muted rounded-pill px-4">Quay lại</a>
        </div>

        <div class="refund-summary-box mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div><strong>Mã đơn:</strong> {{ $order->order_code }}</div>
                    <div><strong>Thanh toán:</strong> {{ strtoupper($order->payment_method) }} - {{ $order->payment_status_label }}</div>
                    <div><strong>Trạng thái:</strong> {{ $order->order_status_label }}</div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div><strong>Tổng đơn:</strong> {{ number_format($order->total_price, 0, ',', '.') }} đ</div>
                    <div><strong>Đã hoàn:</strong> {{ number_format($order->refunded_amount, 0, ',', '.') }} đ</div>
                    <div class="text-danger fs-5"><strong>Còn có thể hoàn sản phẩm:</strong> {{ number_format($order->refundable_amount, 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('client.refunds.store', $order->id) }}" enctype="multipart/form-data" id="refundForm">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Hình thức hoàn tiền</label>
                <select name="type" id="refund_type" class="form-select rounded-3 @error('type') is-invalid @enderror" required>
                    <option value="full" {{ old('type', 'full') === 'full' ? 'selected' : '' }}>Hoàn toàn bộ giá trị sản phẩm còn lại</option>
                    <option value="partial" {{ old('type') === 'partial' ? 'selected' : '' }}>Hoàn một phần theo sản phẩm</option>
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4" id="refund_items_wrap">
                <label class="form-label fw-semibold">Chọn sản phẩm cần hoàn</label>
                <div class="text-muted small mb-2">
                    Khi chọn hoàn một phần, số tiền hoàn được hệ thống tự tính theo sản phẩm và số lượng còn có thể hoàn. Khách hàng không tự nhập số tiền. Phí vận chuyển không được hoàn.
                </div>

                <div class="row g-3">
                    @foreach($refundableItems as $item)
                        @php
                            $detailId = $item['order_detail_id'];
                            $availableQty = (int) $item['available_quantity'];
                            $oldSelected = old("items.$detailId.selected") === '1';
                            $oldQty = old("items.$detailId.quantity", 1);
                        @endphp

                        <div class="col-12">
                            <div class="refund-item-card {{ $availableQty <= 0 ? 'disabled' : '' }}">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div class="flex-grow-1">
                                        <div class="form-check">
                                            <input class="form-check-input refund-item-checkbox"
                                                   type="checkbox"
                                                   name="items[{{ $detailId }}][selected]"
                                                   value="1"
                                                   id="refund_item_{{ $detailId }}"
                                                   data-unit-amount="{{ $item['unit_amount'] }}"
                                                   data-detail-id="{{ $detailId }}"
                                                   {{ $oldSelected ? 'checked' : '' }}
                                                   {{ $availableQty <= 0 ? 'disabled' : '' }}>
                                            <label class="form-check-label fw-semibold" for="refund_item_{{ $detailId }}">
                                                {{ $item['product_name_snapshot'] }}
                                            </label>
                                        </div>

                                        @if($item['variant_snapshot'])
                                            <div class="text-muted small ms-4">{{ $item['variant_snapshot'] }}</div>
                                        @endif

                                        <div class="text-muted small ms-4">
                                            Đã mua: {{ $item['ordered_quantity'] }} • Đã hoàn: {{ $item['refunded_quantity'] }} • Còn hoàn: {{ $availableQty }}
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="small text-muted">Đơn giá hoàn</div>
                                        <div class="refund-item-money">{{ number_format($item['unit_amount'], 0, ',', '.') }} đ</div>
                                    </div>
                                </div>

                                <div class="row g-2 align-items-center mt-3 ms-1">
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Số lượng hoàn</label>
                                        <input type="number"
                                               name="items[{{ $detailId }}][quantity]"
                                               value="{{ $oldQty }}"
                                               min="1"
                                               max="{{ max($availableQty, 1) }}"
                                               class="form-control form-control-sm refund-item-quantity"
                                               data-unit-amount="{{ $item['unit_amount'] }}"
                                               data-detail-id="{{ $detailId }}"
                                               {{ $availableQty <= 0 ? 'disabled' : '' }}>
                                    </div>

                                    <div class="col-md-9">
                                        <div class="small text-muted">Tạm tính sản phẩm này</div>
                                        <div class="fw-bold text-danger" id="refund_item_total_{{ $detailId }}">0 đ</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('items')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </div>

            <div class="refund-summary-box mb-3" id="partial_total_box">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Tổng tiền hoàn theo sản phẩm đã chọn</span>
                    <span class="fs-5 fw-bold text-danger" id="selected_refund_total">0 đ</span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Lý do hoàn tiền</label>
                <select name="reason"
                        class="form-select rounded-3 @error('reason') is-invalid @enderror"
                        required>
                    <option value="">-- Chọn lý do hoàn tiền --</option>
                    @foreach($refundReasons as $reason)
                        <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>
                            {{ $reason }}
                        </option>
                    @endforeach
                </select>
                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Ảnh hoặc video bằng chứng</label>
                <input type="file"
                       name="evidences[]"
                       class="form-control rounded-3 @error('evidences') is-invalid @enderror @error('evidences.*') is-invalid @enderror"
                       accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"
                       multiple
                       required>
                <div class="form-text">Tải 1-5 file. Hỗ trợ ảnh jpg, jpeg, png, webp hoặc video mp4, mov, webm. Mỗi file tối đa 50MB.</div>
                @error('evidences')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('evidences.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn refund-btn-main rounded-pill px-4">
                    Gửi yêu cầu hoàn tiền
                </button>
                <a href="{{ route('client.orders.index') }}" class="btn refund-btn-muted rounded-pill px-4">Hủy</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('refund_type');
    const itemsWrap = document.getElementById('refund_items_wrap');
    const partialTotalBox = document.getElementById('partial_total_box');
    const totalEl = document.getElementById('selected_refund_total');
    const form = document.getElementById('refundForm');

    function formatMoney(value) {
        return new Intl.NumberFormat('vi-VN').format(Math.max(value, 0)) + ' đ';
    }

    function clampQuantity(input) {
        const min = parseInt(input.getAttribute('min') || '1', 10);
        const max = parseInt(input.getAttribute('max') || '1', 10);
        let value = parseInt(input.value || min, 10);

        if (Number.isNaN(value) || value < min) {
            value = min;
        }

        if (value > max) {
            value = max;
        }

        input.value = value;

        return value;
    }

    function updateTotals() {
        let total = 0;

        document.querySelectorAll('.refund-item-checkbox').forEach(function (checkbox) {
            const detailId = checkbox.dataset.detailId;
            const quantityInput = document.querySelector('.refund-item-quantity[data-detail-id="' + detailId + '"]');
            const lineTotalEl = document.getElementById('refund_item_total_' + detailId);
            const unitAmount = parseFloat(checkbox.dataset.unitAmount || '0');

            let quantity = 0;

            if (quantityInput && !quantityInput.disabled) {
                quantity = clampQuantity(quantityInput);
            }

            const lineTotal = checkbox.checked ? unitAmount * quantity : 0;

            if (lineTotalEl) {
                lineTotalEl.textContent = formatMoney(lineTotal);
            }

            total += lineTotal;
        });

        totalEl.textContent = formatMoney(total);
    }

    function toggleMode() {
        const isPartial = typeSelect.value === 'partial';
        itemsWrap.style.display = isPartial ? 'block' : 'none';
        partialTotalBox.style.display = isPartial ? 'block' : 'none';
        updateTotals();
    }

    typeSelect.addEventListener('change', toggleMode);

    document.querySelectorAll('.refund-item-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', updateTotals);
    });

    document.querySelectorAll('.refund-item-quantity').forEach(function (input) {
        input.addEventListener('input', function () {
            clampQuantity(input);
            updateTotals();
        });

        input.addEventListener('change', function () {
            clampQuantity(input);
            updateTotals();
        });

        input.addEventListener('blur', function () {
            clampQuantity(input);
            updateTotals();
        });
    });

    form.addEventListener('submit', function (event) {
        if (typeSelect.value !== 'partial') {
            return;
        }

        let hasSelected = false;
        let hasInvalidQuantity = false;

        document.querySelectorAll('.refund-item-checkbox').forEach(function (checkbox) {
            if (!checkbox.checked || checkbox.disabled) {
                return;
            }

            hasSelected = true;

            const detailId = checkbox.dataset.detailId;
            const quantityInput = document.querySelector('.refund-item-quantity[data-detail-id="' + detailId + '"]');

            if (!quantityInput || quantityInput.disabled) {
                hasInvalidQuantity = true;
                return;
            }

            const quantity = clampQuantity(quantityInput);
            const max = parseInt(quantityInput.getAttribute('max') || '1', 10);

            if (quantity < 1 || quantity > max) {
                hasInvalidQuantity = true;
            }
        });

        updateTotals();

        if (!hasSelected) {
            event.preventDefault();
            alert('Vui lòng chọn ít nhất một sản phẩm cần hoàn tiền.');
            return;
        }

        if (hasInvalidQuantity) {
            event.preventDefault();
            alert('Số lượng hoàn không hợp lệ.');
        }
    });

    toggleMode();
});
</script>
@endsection
