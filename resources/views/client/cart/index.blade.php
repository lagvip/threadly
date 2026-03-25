@extends('client.layouts.master')

@section('content')
<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row fw-bold border-bottom pb-2 mb-3 d-none d-md-flex cart-header-row">
                <div class="col-md-5">Sản Phẩm</div>
                <div class="col-md-2 text-center">Giá</div>
                <div class="col-md-3 text-center">Số Lượng</div>
                <div class="col-md-2 text-end">Tổng Tiền</div>
            </div>

            @php $subtotal = 0; @endphp

            @if($cartItems->isNotEmpty())
                <form action="{{ route('client.cart.update') }}" method="POST" id="cart-update-form">
                    @csrf

                    @foreach($cartItems as $item)
                        @php
                            $price = $item->variant->price ?? 0;
                            $qty = $item->quantity ?? 1;
                            $stock = $item->variant->quantity ?? 0;
                            $totalItem = $price * $qty;
                            $subtotal += $totalItem;

                            if (!empty($item->variant->image)) {
                                $image = asset('storage/' . $item->variant->image);
                            } elseif (!empty($item->variant->product->image_primary)) {
                                $image = asset('storage/' . $item->variant->product->image_primary);
                            } else {
                                $image = asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/placeholder.png');
                            }
                        @endphp

                        <div class="row align-items-center border-bottom py-4 g-3 cart-item-row">
                            <div class="col-12 col-md-5">
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
                                            <div>Tồn kho: {{ $stock }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-2 text-md-center">
                                <span class="d-md-none fw-semibold">Giá: </span>
                                <span class="fw-medium item-price" data-price="{{ $price }}">
                                    {{ number_format($price, 0, ',', '.') }} ₫
                                </span>
                            </div>

                            <div class="col-6 col-md-3">
                                <div class="cart-qty-box mx-auto">
                                    <button class="qty-btn btn-decrease" type="button">-</button>

                                    <input
                                        type="number"
                                        min="1"
                                        max="{{ max($stock, 1) }}"
                                        class="quantity-product"
                                        name="quantities[{{ $item->id }}]"
                                        value="{{ min($qty, max($stock, 1)) }}"
                                        data-stock="{{ $stock }}"
                                    >

                                    <button class="qty-btn btn-increase" type="button">+</button>
                                </div>
                            </div>

                            <div class="col-12 col-md-2 text-end">
                                <div class="d-flex justify-content-end align-items-center gap-3">
                                    <span class="fw-semibold item-total-price" data-price="{{ $price }}">
                                        {{ number_format($totalItem, 0, ',', '.') }} ₫
                                    </span>

                                    <button
                                        type="button"
                                        class="remove-cart-btn"
                                        title="Xóa sản phẩm"
                                        onclick="if(confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) document.getElementById('remove-cart-item-{{ $item->id }}').submit();"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                             fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn cart-theme-btn px-4">
                            Cập Nhật Giỏ Hàng
                        </button>
                    </div>
                </form>

                @foreach($cartItems as $item)
                    <form id="remove-cart-item-{{ $item->id }}"
                          action="{{ route('client.cart.remove', $item->id) }}"
                          method="POST"
                          class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            @else
                <div class="alert alert-info">Giỏ hàng của bạn đang trống.</div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border-0 cart-summary-card">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">Tóm Tắt Đơn Hàng</h4>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            Tạm Tính
                            <span id="subtotal-value">{{ number_format($subtotal ?? 0, 0, ',', '.') }} ₫</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            Giảm Giá
                            <span>0 ₫</span>
                        </li>

                        <li class="list-group-item px-0 bg-transparent">
                            <p class="mb-2">Vận Chuyển</p>

                            <div class="form-check d-flex justify-content-between mb-2">
                                <div>
                                    <input class="form-check-input shipping-radio" type="radio" name="shipping" id="free" value="0" checked>
                                    <label class="form-check-label ms-2" for="free">Miễn Phí Vận Chuyển</label>
                                </div>
                                <span>0 ₫</span>
                            </div>

                            <div class="form-check d-flex justify-content-between">
                                <div>
                                    <input class="form-check-input shipping-radio" type="radio" name="shipping" id="local" value="30000">
                                    <label class="form-check-label ms-2" for="local">Vận Chuyển Nội Địa</label>
                                </div>
                                <span>30.000 ₫</span>
                            </div>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent fw-bold">
                            Tổng
                            <span id="grand-total-value">{{ number_format($subtotal ?? 0, 0, ',', '.') }} ₫</span>
                        </li>
                    </ul>

                    <div class="d-grid mt-4">
                        <a href="{{ route('client.checkout.index') }}" class="btn cart-theme-btn w-100">
                            Tiến Hành Thanh Toán
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .cart-summary-card {
        background-color: #f8f9fa;
        border-radius: 12px;
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

    .cart-qty-box {
        width: 132px;
        height: 44px;
        border: 1px solid #dcdcdc;
        border-radius: 8px;
        display: flex;
        align-items: center;
        overflow: hidden;
        background: #fff;
    }

    .cart-qty-box .qty-btn {
        width: 40px;
        height: 44px;
        border: 0;
        background: #f8f8f8;
        color: #222;
        font-size: 20px;
        font-weight: 600;
        line-height: 1;
        transition: all 0.2s ease;
    }

    .cart-qty-box .qty-btn:hover {
        background: #ececec;
    }

    .cart-qty-box input.quantity-product {
        width: 52px;
        height: 44px;
        border: 0;
        text-align: center;
        font-weight: 600;
        color: #222;
        outline: none;
        box-shadow: none;
        background: #fff;
    }

    .cart-qty-box input.quantity-product::-webkit-outer-spin-button,
    .cart-qty-box input.quantity-product::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .cart-qty-box input.quantity-product[type=number] {
        -moz-appearance: textfield;
    }

    .remove-cart-btn {
        border: 0;
        background: transparent;
        color: #ff4d4f;
        padding: 0;
        line-height: 1;
    }

    .remove-cart-btn:hover {
        color: #dc2626;
    }

    .product-title-link:hover {
        color: #0da487 !important;
    }

    @media (max-width: 767.98px) {
        .cart-qty-box {
            margin-left: 0 !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cartForm = document.getElementById('cart-update-form');
        const quantityInputs = document.querySelectorAll('.quantity-product');
        const shippingRadios = document.querySelectorAll('.shipping-radio');
        const subtotalEl = document.getElementById('subtotal-value');
        const grandTotalEl = document.getElementById('grand-total-value');

        function formatMoney(number) {
            return new Intl.NumberFormat('vi-VN').format(number || 0) + ' ₫';
        }

        function getShippingFee() {
            const checked = document.querySelector('.shipping-radio:checked');
            return checked ? parseInt(checked.value || 0, 10) : 0;
        }

        function recalcCart() {
            let subtotal = 0;

            document.querySelectorAll('.cart-item-row').forEach(row => {
                const qtyInput = row.querySelector('.quantity-product');
                const totalEl = row.querySelector('.item-total-price');
                const priceEl = row.querySelector('.item-price');

                let price = parseInt(priceEl?.dataset.price || 0, 10);
                let qty = parseInt(qtyInput?.value || 1, 10);
                let stock = parseInt(qtyInput?.dataset.stock || 1, 10);

                if (isNaN(qty) || qty < 1) qty = 1;
                if (isNaN(stock) || stock < 1) stock = 1;
                if (qty > stock) qty = stock;

                qtyInput.value = qty;

                const lineTotal = price * qty;
                subtotal += lineTotal;

                if (totalEl) {
                    totalEl.textContent = formatMoney(lineTotal);
                }
            });

            const shipping = getShippingFee();
            const grandTotal = subtotal + shipping;

            if (subtotalEl) subtotalEl.textContent = formatMoney(subtotal);
            if (grandTotalEl) grandTotalEl.textContent = formatMoney(grandTotal);
        }

        document.querySelectorAll('.btn-decrease').forEach(btn => {
            btn.addEventListener('click', function () {
                const input = this.closest('.cart-qty-box').querySelector('.quantity-product');
                let value = parseInt(input.value || 1, 10);

                if (isNaN(value) || value <= 1) value = 1;
                else value--;

                input.value = value;
                recalcCart();
            });
        });

        document.querySelectorAll('.btn-increase').forEach(btn => {
            btn.addEventListener('click', function () {
                const input = this.closest('.cart-qty-box').querySelector('.quantity-product');
                let value = parseInt(input.value || 1, 10);
                let stock = parseInt(input.dataset.stock || 1, 10);

                if (isNaN(value) || value < 1) value = 1;
                if (isNaN(stock) || stock < 1) stock = 1;

                if (value < stock) value++;

                input.value = value;
                recalcCart();
            });
        });

        quantityInputs.forEach(input => {
            input.addEventListener('input', function () {
                let value = parseInt(this.value || 1, 10);
                let stock = parseInt(this.dataset.stock || 1, 10);

                if (isNaN(value) || value < 1) value = 1;
                if (isNaN(stock) || stock < 1) stock = 1;
                if (value > stock) value = stock;

                this.value = value;
                recalcCart();
            });
        });

        shippingRadios.forEach(radio => {
            radio.addEventListener('change', recalcCart);
        });

        cartForm?.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Đang cập nhật...';
            }
        });

        recalcCart();
    });
</script>
@endpush
