@extends('client.layouts.master')

@section('content')
    @php
        $productTotal = method_exists($products, 'total') ? $products->total() : $products->count();
    @endphp

    <section class="cc-category-page">
        <div class="container-fluid-lg">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="cc-sidebar">
                        <div class="cc-sidebar-top">
                            <h3>Bộ lọc</h3>
                            <a href="{{ route('client.category', $category->id) }}">Xóa tất cả</a>
                        </div>

                        <form method="GET" action="{{ route('client.category', $category->id) }}">
                            <div class="cc-block">
                                <h4>Khoảng giá</h4>

                                <label class="cc-label" for="minPrice">Giá thấp nhất</label>
                                <input
                                    type="range"
                                    name="min_price"
                                    min="0"
                                    max="10000000"
                                    step="200000"
                                    value="{{ request('min_price', 0) }}"
                                    id="minPrice"
                                >

                                <label class="cc-label mt-3" for="maxPrice">Giá cao nhất</label>
                                <input
                                    type="range"
                                    name="max_price"
                                    min="0"
                                    max="10000000"
                                    step="200000"
                                    value="{{ request('max_price', 10000000) }}"
                                    id="maxPrice"
                                >

                                <div class="cc-price-box">
                                    <div id="minPriceValue"></div>
                                    <div id="maxPriceValue"></div>
                                </div>
                            </div>

                            <div class="cc-block">
                                <h4>Đánh giá</h4>

                                <div class="cc-rating-filter">
                                    @for($i = 5; $i >= 1; $i--)
                                        <label class="cc-rating-row">
                                            <span class="cc-rating-left">
                                                <input
                                                    type="radio"
                                                    name="rating"
                                                    value="{{ $i }}"
                                                    {{ request('rating') == $i ? 'checked' : '' }}
                                                >

                                                <ul class="rating">
                                                    @for($j = 1; $j <= 5; $j++)
                                                        <li>
                                                            <i data-feather="star" class="{{ $j <= $i ? 'fill' : '' }}"></i>
                                                        </li>
                                                    @endfor
                                                </ul>
                                            </span>

                                            <span class="cc-rating-text">{{ $i }}+</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            <div class="cc-actions">
                                <button type="submit" class="cc-btn cc-btn-primary">Lọc sản phẩm</button>
                                <a href="{{ route('client.category', $category->id) }}" class="cc-btn cc-btn-light">Đặt lại</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="cc-toolbar">
                        <div class="cc-toolbar-left">
                            <strong>{{ $productTotal }}</strong> sản phẩm
                        </div>

                        <div class="cc-toolbar-right">
                            <span>Danh mục:</span>
                            <div class="cc-pill">{{ $category->name }}</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        @forelse($products as $product)
                            @php
                                $variant = $product->variants->first();
                                $reviews = $product->reviews;
                                $reviewCount = $reviews->count();
                                $averageRating = $reviewCount > 0 ? round($reviews->avg('rating'), 1) : 0;
                            @endphp

                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="cc-card">
                                    <div class="cc-image-wrap">
                                        <a href="{{ route('client.product.detail', $product->id) }}" class="cc-image-link">
                                            <img
                                                src="{{ asset('storage/' . $product->image_primary) }}"
                                                alt="{{ $product->name }}"
                                            >
                                        </a>
                                    </div>

                                    <div class="cc-body">
                                        <div class="cc-category">{{ $category->name }}</div>

                                        <a href="{{ route('client.product.detail', $product->id) }}" class="cc-name">
                                            {{ $product->name }}
                                        </a>

                                        <div class="cc-product-rating">
                                            <ul class="rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <li>
                                                        <i data-feather="star" class="{{ $i <= round($averageRating) ? 'fill' : '' }}"></i>
                                                    </li>
                                                @endfor
                                            </ul>
                                            <span>({{ number_format($averageRating, 1) }})</span>
                                        </div>

                                        <div class="cc-stock">
                                            Tồn kho: {{ $variant->quantity ?? 0 }}
                                        </div>

                                        <div class="cc-price">
                                            {{ number_format($variant->price ?? 0, 0, ',', '.') }} đ
                                        </div>


                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="cc-empty">
                                    <h4>Không có sản phẩm phù hợp</h4>
                                    <p>Thử đổi khoảng giá hoặc mức đánh giá.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="cc-pagination">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .cc-category-page {
            background: #f6f6f6;
            padding: 28px 0 44px;
        }

        .cc-sidebar {
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 8px 26px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 100px;
        }

        .cc-sidebar-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .cc-sidebar-top h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #222;
        }

        .cc-sidebar-top a {
            color: var(--theme-color);
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
        }

        .cc-block + .cc-block {
            margin-top: 26px;
            padding-top: 24px;
            border-top: 1px solid #ececec;
        }

        .cc-block h4 {
            margin: 0 0 16px;
            font-size: 18px;
            font-weight: 700;
            color: #222;
            position: relative;
            display: inline-block;
            padding-bottom: 8px;
        }

        .cc-block h4::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 92px;
            height: 3px;
            border-radius: 999px;
            background: var(--theme-color);
        }

        .cc-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .cc-sidebar input[type="range"] {
            width: 100%;
            accent-color: #3b82f6;
        }

        .cc-price-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 12px;
        }

        .cc-price-box div {
            background: #f3f4f6;
            border-radius: 14px;
            text-align: center;
            padding: 14px 10px;
            font-size: 14px;
            font-weight: 700;
            color: #222;
        }

        .cc-rating-filter {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .cc-rating-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 0;
            cursor: pointer;
        }

        .cc-rating-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cc-rating-left input[type="radio"] {
            accent-color: var(--theme-color);
        }

        .cc-rating-text {
            color: #6b7280;
            font-weight: 700;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 3px;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .rating li i {
            width: 14px;
            height: 14px;
            stroke: #d1d5db;
        }

        .rating li i.fill {
            stroke: #f7b529;
            fill: #f7b529;
        }

        .cc-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 24px;
        }

        .cc-btn {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 13px 16px;
            text-align: center;
            text-decoration: none;
            font-weight: 700;
        }

        .cc-btn-primary {
            background: var(--theme-color);
            color: #fff;
        }

        .cc-btn-light {
            background: #f3f4f6;
            color: #222;
        }

        .cc-toolbar {
            background: #fff;
            border-radius: 18px;
            padding: 18px 22px;
            margin-bottom: 24px;
            box-shadow: 0 8px 26px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .cc-toolbar-left {
            color: #374151;
            font-size: 18px;
        }

        .cc-toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            color: #4b5563;
        }

        .cc-pill {
            background: #f3f4f6;
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 700;
            color: #444;
        }

        .cc-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            height: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: 0.2s ease;
        }

        .cc-card:hover {
            transform: translateY(-4px);
        }

        .cc-image-wrap {
            background: #f8fafc;
            padding: 14px;
            position: relative;
        }

        .cc-image-link {
            display: block;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: inset 0 0 0 1px #f1f1f1;
        }

        .cc-image-link img {
            width: 100%;
            height: 230px;
            object-fit: cover;
            display: block;
        }

        .cc-image-action {
            position: absolute;
            left: 50%;
            bottom: 26px;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.97);
            border-radius: 999px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }
        .cc-body {
            padding: 18px;
        }

        .cc-category {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .cc-name {
            display: block;
            text-decoration: none;
            color: #222;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.45;
            min-height: 46px;
            margin-bottom: 12px;
        }

        .cc-product-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .cc-product-rating span {
            color: #6b7280;
            font-size: 14px;
            font-weight: 700;
        }

        .cc-stock {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .cc-price {
            color: #2c6e9f;
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .cc-add-text {
            flex: 1;
            border: none;
            background: transparent;
            color: #475569;
            font-size: 16px;
            font-weight: 600;
        }

        .cc-add-icon {
            width: 44px;
            height: 44px;
            border: none;
            border-radius: 50%;
            background: #f3f4f6;
            color: #2c6e9f;
        }

        .cc-empty {
            background: #fff;
            border-radius: 18px;
            padding: 42px 20px;
            text-align: center;
            box-shadow: 0 8px 26px rgba(0, 0, 0, 0.04);
        }

        .cc-empty h4 {
            margin-bottom: 8px;
            font-weight: 700;
        }

        .cc-empty p {
            margin: 0;
            color: #6b7280;
        }

        .cc-pagination {
            margin-top: 26px;
            display: flex;
            justify-content: center;
        }

        .cc-pagination .pagination {
            gap: 8px;
        }

        .cc-pagination .page-link {
            border: none;
            border-radius: 12px;
            min-width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #374151;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        .cc-pagination .page-item.active .page-link {
            background: var(--theme-color);
            color: #fff;
        }

        @media (max-width: 991px) {
            .cc-sidebar {
                position: static;
            }
        }

        @media (max-width: 575px) {
            .cc-category-page {
                padding: 18px 0 36px;
            }
            .cc-image-link img {
                height: 170px;
            }

            .cc-name {
                font-size: 15px;
                min-height: 42px;
            }

            .cc-price {
                font-size: 16px;
            }
        }
    </style>

    <script>
        const minInput = document.getElementById('minPrice');
        const maxInput = document.getElementById('maxPrice');
        const minText = document.getElementById('minPriceValue');
        const maxText = document.getElementById('maxPriceValue');

        function formatMoney(value) {
            return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
        }

        function updatePrice() {
            if (parseInt(minInput.value) > parseInt(maxInput.value)) {
                const temp = minInput.value;
                minInput.value = maxInput.value;
                maxInput.value = temp;
            }

            minText.innerText = formatMoney(minInput.value);
            maxText.innerText = formatMoney(maxInput.value);
        }

        minInput.addEventListener('input', updatePrice);
        maxInput.addEventListener('input', updatePrice);

        updatePrice();

        document.addEventListener('DOMContentLoaded', function () {
            if (window.feather) {
                feather.replace();
            }
        });
    </script>
@endsection
