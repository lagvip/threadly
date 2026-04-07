@extends('client.layouts.master')

@section('content')
<div class="container py-5">
    <form action="{{ route('client.checkout.store') }}" method="POST" id="checkout-form">
        @csrf

        <div class="row g-5">
            <div class="col-lg-8">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="checkout-section-card mb-4">
                    <div class="p-4">
                        <h4 class="section-title mb-4">Thông Tin Nhận Hàng</h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Họ và tên</label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="form-control checkout-input"
                                    value="{{ old('name', auth()->user()->name ?? '') }}"
                                    placeholder="Nhập họ và tên"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    class="form-control checkout-input"
                                    value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                    placeholder="Nhập số điện thoại"
                                >
                            </div>

                            <div class="col-12">
                                <label class="form-label">Địa chỉ nhận hàng</label>

                                <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                                    <button type="button" class="btn btn-sm cart-theme-btn" id="toggle-new-address">
                                        + Thêm địa chỉ mới
                                    </button>
                                </div>

                                <div id="address-select-wrapper">
                                    @if($addresses->count())
                                        <select name="address_id" id="address_id" class="form-select checkout-input">
                                            <option value="">-- Chọn địa chỉ --</option>
                                            @foreach($addresses as $address)
                                                <option
                                                    value="{{ $address->id }}"
                                                    data-province="{{ $address->province }}"
                                                    data-district="{{ $address->district }}"
                                                    data-ward="{{ $address->ward }}"
                                                    data-detail="{{ $address->detailed_address }}"
                                                    data-recipient="{{ $address->recipient_name }}"
                                                    data-phone="{{ $address->phone_number }}"
                                                    data-ghn_district_id="{{ $address->ghn_district_id }}"
                                                    data-ghn_ward_code="{{ $address->ghn_ward_code }}"
                                                    {{ old('address_id', $defaultAddress->id ?? '') == $address->id ? 'selected' : '' }}
                                                >
                                                    {{ $address->detailed_address }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}
                                                    @if(!empty($address->is_default)) (Mặc định) @endif
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="selected-address-box mt-3">
                                            <div class="fw-semibold mb-2">Địa chỉ đã chọn</div>
                                            <div id="selected-address-text" class="text-muted small"></div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ trước khi thanh toán.
                                        </div>

                                        <div class="selected-address-box mt-3">
                                            <div class="fw-semibold mb-2">Địa chỉ đã chọn</div>
                                            <div id="selected-address-text" class="text-muted small">Bạn chưa chọn địa chỉ.</div>
                                        </div>
                                    @endif
                                </div>

                                <div id="new-address-form" class="mt-3" style="display:none;">
                                    <div class="border rounded p-3 bg-light">
                                        <div class="fw-semibold mb-3">Thêm địa chỉ mới</div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <input type="text" id="new_recipient_name" class="form-control checkout-input" placeholder="Tên người nhận">
                                            </div>

                                            <div class="col-md-6">
                                                <input type="text" id="new_phone" class="form-control checkout-input" placeholder="Số điện thoại">
                                            </div>

                                            <div class="col-md-4">
                                                <select id="new_province" class="form-select checkout-input">
                                                    <option value="">Chọn tỉnh / thành</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <select id="new_district" class="form-select checkout-input" disabled>
                                                    <option value="">Chọn quận / huyện</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <select id="new_ward" class="form-select checkout-input" disabled>
                                                    <option value="">Chọn phường / xã</option>
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <input type="text" id="new_detail" class="form-control checkout-input" placeholder="Số nhà, tên đường...">
                                            </div>

                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="new_is_default">
                                                    <label class="form-check-label" for="new_is_default">
                                                        Đặt làm địa chỉ mặc định
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-12 d-flex gap-2">
                                                <button type="button" class="btn cart-theme-btn" id="save-new-address-btn">
                                                    Lưu địa chỉ
                                                </button>

                                                <button type="button" class="btn btn-outline-secondary" id="cancel-new-address">
                                                    Huỷ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="customer_note" class="form-label">Ghi chú đơn hàng</label>
                                <textarea
                                    name="customer_note"
                                    id="customer_note"
                                    rows="3"
                                    class="form-control"
                                    placeholder="Ví dụ: giao giờ hành chính, gọi trước khi giao..."
                                >{{ old('customer_note') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkout-section-card mb-4">
                    <div class="p-4">
                        <h4 class="section-title mb-4">Phương Thức Thanh Toán</h4>

                        <div class="payment-method-list">
                            <label class="payment-method-item">
                                <input
                                    class="form-check-input payment-radio"
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}
                                >
                                <div>
                                    <div class="fw-semibold">Thanh toán khi nhận hàng (COD)</div>
                                    <div class="small text-muted">Bạn thanh toán khi nhận được hàng.</div>
                                </div>
                            </label>

                            <label class="payment-method-item">
                                <input
                                    class="form-check-input payment-radio"
                                    type="radio"
                                    name="payment_method"
                                    value="vnpay"
                                    {{ old('payment_method') === 'vnpay' ? 'checked' : '' }}
                                >
                                <div>
                                    <div class="fw-semibold">Thanh toán VNPay</div>
                                    <div class="small text-muted">Chuyển sang cổng VNPay để thanh toán.</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="checkout-section-card">
                    <div class="p-4">
                        <h4 class="section-title mb-4">Sản Phẩm Trong Đơn</h4>

                        <div class="row fw-bold border-bottom pb-2 mb-3 d-none d-md-flex">
                            <div class="col-md-6">Sản Phẩm</div>
                            <div class="col-md-2 text-center">Giá</div>
                            <div class="col-md-2 text-center">SL</div>
                            <div class="col-md-2 text-end">Tổng</div>
                        </div>

                        @php $subtotal = 0; @endphp

                        @foreach($cartItems as $item)
                            @php
                                $price = $item->variant->price ?? 0;
                                $qty = $item->quantity ?? 1;
                                $lineTotal = $price * $qty;
                                $subtotal += $lineTotal;

                                if (!empty($item->variant->image)) {
                                    $image = asset('storage/' . $item->variant->image);
                                } elseif (!empty($item->variant->product->image_primary)) {
                                    $image = asset('storage/' . $item->variant->product->image_primary);
                                } else {
                                    $image = asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/placeholder.png');
                                }
                            @endphp

                            <div class="row align-items-center border-bottom py-4 g-3">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('client.product.detail', $item->variant->product->id) }}">
                                            <img
                                                src="{{ $image }}"
                                                alt="{{ $item->variant->product->name }}"
                                                style="width: 95px; height: 110px; object-fit: cover;"
                                                class="rounded"
                                            >
                                        </a>

                                        <div class="ms-3">
                                            <a href="{{ route('client.product.detail', $item->variant->product->id) }}"
                                               class="text-dark text-decoration-none fw-bold product-title-link">
                                                {{ $item->variant->product->name }}
                                            </a>

                                            <div class="mt-2 small text-muted">
                                                <div>Màu: {{ $item->variant->color->name ?? 'N/A' }}</div>
                                                <div>Size: {{ $item->variant->size->name ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-4 col-md-2 text-md-center">
                                    {{ number_format($price, 0, ',', '.') }} ₫
                                </div>

                                <div class="col-4 col-md-2 text-md-center">
                                    {{ $qty }}
                                </div>

                                <div class="col-4 col-md-2 text-end fw-semibold">
                                    {{ number_format($lineTotal, 0, ',', '.') }} ₫
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 cart-summary-card">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Tóm Tắt Đơn Hàng</h4>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">Mã giảm giá</label>

                                @if(!empty($availableVouchers) && count($availableVouchers))
                                    <button type="button" class="btn btn-sm cart-theme-btn" id="toggle-voucher-list">
                                        Voucher có thể dùng
                                    </button>
                                @endif
                            </div>

                            @if(!empty($appliedVoucher))
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                    <div class="small text-success">
                                        Đã áp dụng: <strong>{{ $appliedVoucher['voucher_code'] }}</strong>
                                    </div>

                                    <button
                                        type="submit"
                                        class="btn btn-sm cart-theme-btn"
                                        form="remove-voucher-form"
                                    >
                                        Bỏ mã
                                    </button>
                                </div>
                            @endif

                            <div class="d-flex gap-2 mb-2">
                                <input
                                    type="text"
                                    id="voucher_code_input"
                                    name="voucher_code"
                                    class="form-control checkout-input"
                                    placeholder="Nhập mã voucher"
                                    form="apply-voucher-form"
                                    value="{{ $appliedVoucher['voucher_code'] ?? '' }}"
                                >
                                <button
                                    type="submit"
                                    class="btn cart-theme-btn"
                                    form="apply-voucher-form"
                                >
                                    Áp dụng
                                </button>
                            </div>

                            @if(!empty($availableVouchers) && count($availableVouchers))
                                <div id="voucher-list-box" class="voucher-popup-box mt-3 d-none">
                                    <div class="fw-semibold mb-2">Chọn voucher phù hợp</div>

                                    <div class="voucher-list">
                                        @foreach($availableVouchers as $voucher)
                                            <div class="voucher-card {{ !empty($appliedVoucher) && $appliedVoucher['voucher_code'] === $voucher['code'] ? 'active' : '' }}">
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $voucher['code'] }}</div>

                                                        <div class="small text-muted mt-1">
                                                            @if($voucher['type'] === 'percent')
                                                                Giảm {{ rtrim(rtrim(number_format($voucher['value'], 2, '.', ''), '0'), '.') }}%
                                                                @if(!empty($voucher['max_discount']))
                                                                    , tối đa {{ number_format($voucher['max_discount'], 0, ',', '.') }} ₫
                                                                @endif
                                                            @else
                                                                Giảm {{ number_format($voucher['value'], 0, ',', '.') }} ₫
                                                            @endif
                                                        </div>

                                                        <div class="small text-muted">
                                                            Đơn tối thiểu: {{ number_format($voucher['min_order_value'], 0, ',', '.') }} ₫
                                                        </div>

                                                        <div class="small text-success">
                                                            Dự kiến giảm: {{ number_format($voucher['discount_preview'], 0, ',', '.') }} ₫
                                                        </div>

                                                        <div class="small text-muted">
                                                            HSD: {{ \Carbon\Carbon::parse($voucher['end_date'])->format('d/m/Y H:i') }}
                                                        </div>
                                                    </div>

                                                    @if(!empty($appliedVoucher) && $appliedVoucher['voucher_code'] === $voucher['code'])
                                                        <button type="button" class="btn btn-sm cart-theme-btn" disabled>
                                                            Đã chọn
                                                        </button>
                                                    @else
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm cart-theme-btn voucher-select-btn"
                                                            data-code="{{ $voucher['code'] }}"
                                                        >
                                                            Chọn
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                Tạm tính
                                <span id="subtotal-value">{{ number_format($subtotal ?? 0, 0, ',', '.') }} ₫</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                Phí vận chuyển
                                <span id="shipping-fee-text">{{ number_format($shippingFee ?? 0, 0, ',', '.') }} ₫</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                Giảm Giá
                                <span id="discount-value">{{ number_format($discount ?? 0, 0, ',', '.') }} ₫</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent fw-bold">
                                Tổng
                                <span id="grand-total-value">
                                    {{ number_format($grandTotal ?? (($subtotal ?? 0) + ($shippingFee ?? 0) - ($discount ?? 0)), 0, ',', '.') }} ₫
                                </span>
                            </li>
                        </ul>

                        <input type="hidden" name="shipping_fee" id="shipping_fee" value="{{ $shippingFee ?? 0 }}">

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn cart-theme-btn w-100" id="checkout-submit-btn">
                                Tiến Hành Thanh Toán
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <form action="{{ route('client.checkout.voucher.apply') }}" method="POST" id="apply-voucher-form" class="d-none">
        @csrf
    </form>

    <form action="{{ route('client.checkout.voucher.remove') }}" method="POST" id="remove-voucher-form" class="d-none">
        @csrf
    </form>
</div>
@endsection

@push('styles')
<style>
    .checkout-section-card,
    .cart-summary-card {
        background-color: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #eee;
    }

    .section-title {
        font-size: 22px;
        font-weight: 700;
        color: #212529;
    }

    .checkout-input {
        border-radius: 8px;
        min-height: 46px;
        border: 1px solid #dcdcdc;
        box-shadow: none !important;
    }

    textarea.checkout-input {
        min-height: 110px;
    }

    .checkout-input:focus {
        border-color: #212529;
    }

    .cart-theme-btn {
        background: #212529;
        color: #fff !important;
        border: 1px solid #212529;
        font-weight: 600;
        padding: 12px 22px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .cart-theme-btn:hover {
        background: #000;
        border-color: #000;
        color: #fff !important;
    }

    .product-title-link:hover {
        color: #0da487 !important;
    }

    .payment-method-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .payment-method-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid #e3e3e3;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .payment-method-item:hover {
        border-color: #212529;
    }

    .payment-method-item input[type="radio"] {
        margin-top: 4px;
    }

    .selected-address-box {
        background: #fff;
        border: 1px dashed #cfcfcf;
        border-radius: 10px;
        padding: 14px;
    }
    .voucher-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 320px;
        overflow-y: auto;
    }

    .voucher-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-radius: 10px;
        padding: 12px;
        transition: all 0.2s ease;
    }

    .voucher-card.active {
        border-color: #212529;
        box-shadow: 0 0 0 1px #212529 inset;
    }
    .voucher-popup-box {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-radius: 10px;
        padding: 12px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggle-new-address');
    const cancelBtn = document.getElementById('cancel-new-address');
    const form = document.getElementById('new-address-form');
    const saveBtn = document.getElementById('save-new-address-btn');

    let addressSelect = document.getElementById('address_id');
    let selectedAddressText = document.getElementById('selected-address-text');

    const shippingFeeInput = document.getElementById('shipping_fee');
    const shippingFeeText = document.getElementById('shipping-fee-text');
    const grandTotalEl = document.getElementById('grand-total-value');
    const submitBtn = document.getElementById('checkout-submit-btn');
    const checkoutForm = document.getElementById('checkout-form');

    const nameInput = document.getElementById('name');
    const phoneInput = document.getElementById('phone');

    const provinceSelect = document.getElementById('new_province');
    const districtSelect = document.getElementById('new_district');
    const wardSelect = document.getElementById('new_ward');

    const toggleVoucherListBtn = document.getElementById('toggle-voucher-list');
    const voucherListBox = document.getElementById('voucher-list-box');
    const voucherCodeInput = document.getElementById('voucher_code_input');
    const applyVoucherForm = document.getElementById('apply-voucher-form');

    toggleVoucherListBtn?.addEventListener('click', function () {
        voucherListBox?.classList.toggle('d-none');
    });

    document.querySelectorAll('.voucher-select-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.dataset.code;
            if (!code || !voucherCodeInput || !applyVoucherForm) return;

            voucherCodeInput.value = code;
            applyVoucherForm.submit();
        });
    });

    const subtotal = {{ (int) round($subtotal ?? 0) }};
    const appliedDiscount = {{ (int) round($discount ?? 0) }};

    function formatMoney(number) {
        return new Intl.NumberFormat('vi-VN').format(Number(number || 0)) + ' ₫';
    }

    function toggleForm(show = null) {
        if (!form) return;

        if (show === true) {
            form.style.display = 'block';
            return;
        }

        if (show === false) {
            form.style.display = 'none';
            return;
        }

        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    function renderSelectedAddress() {
        if (!selectedAddressText) return;

        if (!addressSelect || !addressSelect.value) {
            selectedAddressText.innerHTML = 'Bạn chưa chọn địa chỉ.';
            return;
        }

        const selected = addressSelect.options[addressSelect.selectedIndex];
        if (!selected) {
            selectedAddressText.innerHTML = 'Bạn chưa chọn địa chỉ.';
            return;
        }

        const detail = selected.dataset.detail || '';
        const ward = selected.dataset.ward || '';
        const district = selected.dataset.district || '';
        const province = selected.dataset.province || '';

        selectedAddressText.innerHTML = `${detail}, ${ward}, ${district}, ${province}`;

        if (nameInput && selected.dataset.recipient) {
            nameInput.value = selected.dataset.recipient;
        }

        if (phoneInput && selected.dataset.phone) {
            phoneInput.value = selected.dataset.phone;
        }
    }

    function updateGrandTotal() {
        const shipping = parseInt(shippingFeeInput?.value || 0, 10) || 0;
        const grandTotal = Math.max(0, subtotal + shipping - appliedDiscount);

        if (shippingFeeText) {
            shippingFeeText.textContent = formatMoney(shipping);
        }

        const discountEl = document.getElementById('discount-value');
        if (discountEl) {
            discountEl.textContent = formatMoney(appliedDiscount);
        }

        if (grandTotalEl) {
            grandTotalEl.textContent = formatMoney(grandTotal);
        }
    }

    async function calculateShippingFee() {
        if (!addressSelect || !addressSelect.value) {
            if (shippingFeeInput) shippingFeeInput.value = 0;
            updateGrandTotal();
            return;
        }

        try {
            const response = await fetch("{{ route('client.checkout.shipping-fee') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    address_id: addressSelect.value
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                if (shippingFeeInput) {
                    shippingFeeInput.value = parseInt(data.shipping_fee || 0, 10);
                }
            } else {
                if (shippingFeeInput) shippingFeeInput.value = 0;
                console.error(data.message || 'Không tính được phí ship');
            }
        } catch (e) {
            if (shippingFeeInput) shippingFeeInput.value = 0;
            console.error('Lỗi calculateShippingFee:', e);
        }

        updateGrandTotal();
    }

    async function loadProvinces() {
        try {
            const response = await fetch("{{ route('client.checkout.ghn.provinces') }}", {
                headers: { "Accept": "application/json" }
            });

            const res = await response.json();

            provinceSelect.innerHTML = '<option value="">Chọn tỉnh / thành</option>';

            if (!response.ok || !res.success) return;

            res.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.ProvinceID;
                option.textContent = item.ProvinceName;
                option.dataset.name = item.ProvinceName;
                provinceSelect.appendChild(option);
            });
        } catch (e) {
            console.error('Lỗi loadProvinces:', e);
        }
    }

    async function loadDistricts(provinceId) {
        districtSelect.innerHTML = '<option value="">Chọn quận / huyện</option>';
        wardSelect.innerHTML = '<option value="">Chọn phường / xã</option>';
        districtSelect.disabled = true;
        wardSelect.disabled = true;

        if (!provinceId) return;

        try {
            const response = await fetch(`{{ route('client.checkout.ghn.districts') }}?province_id=${provinceId}`, {
                headers: { "Accept": "application/json" }
            });

            const res = await response.json();

            if (!response.ok || !res.success) return;

            res.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.DistrictID;
                option.textContent = item.DistrictName;
                option.dataset.name = item.DistrictName;
                districtSelect.appendChild(option);
            });

            districtSelect.disabled = false;
        } catch (e) {
            console.error('Lỗi loadDistricts:', e);
        }
    }

    async function loadWards(districtId) {
        wardSelect.innerHTML = '<option value="">Chọn phường / xã</option>';
        wardSelect.disabled = true;

        if (!districtId) return;

        try {
            const response = await fetch(`{{ route('client.checkout.ghn.wards') }}?district_id=${districtId}`, {
                headers: { "Accept": "application/json" }
            });

            const res = await response.json();

            if (!response.ok || !res.success) return;

            res.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.WardCode;
                option.textContent = item.WardName;
                option.dataset.name = item.WardName;
                wardSelect.appendChild(option);
            });

            wardSelect.disabled = false;
        } catch (e) {
            console.error('Lỗi loadWards:', e);
        }
    }

    function ensureAddressSelectExists() {
        if (addressSelect) return addressSelect;

        const wrapper = document.getElementById('address-select-wrapper');
        wrapper.innerHTML = `
            <select name="address_id" id="address_id" class="form-select checkout-input">
                <option value="">-- Chọn địa chỉ --</option>
            </select>
            <div class="selected-address-box mt-3">
                <div class="fw-semibold mb-2">Địa chỉ đã chọn</div>
                <div id="selected-address-text" class="text-muted small">Bạn chưa chọn địa chỉ.</div>
            </div>
        `;

        addressSelect = document.getElementById('address_id');
        selectedAddressText = document.getElementById('selected-address-text');

        addressSelect.addEventListener('change', async function () {
            renderSelectedAddress();
            await calculateShippingFee();
        });

        return addressSelect;
    }

    function resetNewAddressForm() {
        const recipientInput = document.getElementById('new_recipient_name');
        const phoneNewInput = document.getElementById('new_phone');
        const detailInput = document.getElementById('new_detail');
        const defaultInput = document.getElementById('new_is_default');
        if (defaultInput) defaultInput.checked = false;

        if (recipientInput) recipientInput.value = '';
        if (phoneNewInput) phoneNewInput.value = '';
        if (detailInput) detailInput.value = '';

        provinceSelect.value = '';
        districtSelect.innerHTML = '<option value="">Chọn quận / huyện</option>';
        wardSelect.innerHTML = '<option value="">Chọn phường / xã</option>';
        districtSelect.disabled = true;
        wardSelect.disabled = true;
    }

    toggleBtn?.addEventListener('click', async function () {
        toggleForm();

        if (form.style.display === 'block' && provinceSelect && provinceSelect.options.length <= 1) {
            await loadProvinces();
        }
    });

    cancelBtn?.addEventListener('click', function () {
        toggleForm(false);
    });

    provinceSelect?.addEventListener('change', function () {
        loadDistricts(this.value);
    });

    districtSelect?.addEventListener('change', function () {
        loadWards(this.value);
    });

    saveBtn?.addEventListener('click', async function () {
        const selectedProvince = provinceSelect.options[provinceSelect.selectedIndex];
        const selectedDistrict = districtSelect.options[districtSelect.selectedIndex];
        const selectedWard = wardSelect.options[wardSelect.selectedIndex];

        const recipientName = document.getElementById('new_recipient_name')?.value.trim() || '';
        const phone = document.getElementById('new_phone')?.value.trim() || '';
        const detailedAddress = document.getElementById('new_detail')?.value.trim() || '';

        const data = {
            recipient_name: recipientName,
            phone: phone,
            province: selectedProvince?.dataset.name || '',
            district: selectedDistrict?.dataset.name || '',
            ward: selectedWard?.dataset.name || '',
            detailed_address: detailedAddress,
            ghn_province_id: provinceSelect.value || '',
            ghn_district_id: districtSelect.value || '',
            ghn_ward_code: wardSelect.value || ''
        };

        if (!data.recipient_name || !data.phone || !data.province || !data.district || !data.ward || !data.detailed_address) {
            alert('Vui lòng nhập đầy đủ thông tin địa chỉ.');
            return;
        }

        try {
            saveBtn.disabled = true;
            saveBtn.innerText = 'Đang lưu...';

            const response = await fetch("{{ route('client.checkout.address.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify(data)
            });

            const res = await response.json();

            if (!response.ok || !res.success) {
                alert(res.message || 'Không thể thêm địa chỉ.');
                return;
            }

            const a = res.address;
            const currentAddressSelect = ensureAddressSelectExists();

            const option = document.createElement('option');
            option.value = a.id;
            option.textContent = a.text;
            option.dataset.province = a.province;
            option.dataset.district = a.district;
            option.dataset.ward = a.ward;
            option.dataset.detail = a.detail;
            option.dataset.recipient = recipientName;
            option.dataset.phone = phone;
            option.dataset.ghn_province_id = a.ghn_province_id || '';
            option.dataset.ghn_district_id = a.ghn_district_id || '';
            option.dataset.ghn_ward_code = a.ghn_ward_code || '';

            currentAddressSelect.appendChild(option);
            currentAddressSelect.value = String(a.id);

            if (nameInput) nameInput.value = recipientName;
            if (phoneInput) phoneInput.value = phone;

            resetNewAddressForm();
            toggleForm(false);

            currentAddressSelect.dispatchEvent(new Event('change'));

            alert('Thêm địa chỉ thành công!');
        } catch (e) {
            console.error('Lỗi thêm địa chỉ:', e);
            alert('Có lỗi xảy ra khi thêm địa chỉ.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerText = 'Lưu địa chỉ';
        }
    });

    addressSelect?.addEventListener('change', async function () {
        renderSelectedAddress();
        await calculateShippingFee();
    });

    checkoutForm?.addEventListener('submit', function () {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Đang xử lý...';
        }
    });

    renderSelectedAddress();
    updateGrandTotal();
    calculateShippingFee();
});
</script>
@endpush
