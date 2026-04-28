@extends('client.layouts.master')

@section('content')
    @php
        $productTotal = method_exists($products, 'total') ? $products->total() : $products->count();
    @endphp

    <section class="section-b-space shop-section">
        <div class="container-fluid-lg">
            <div class="row">

                <!-- Filter -->
                <div class="col-lg-3">
                    <div class="left-box wow fadeInUp">
                        <div class="shop-left-sidebar">
                            <form method="GET" action="{{ route('client.category', $category->id) }}">
                                <div class="accordion custom-accordion" id="accordionExample" style="--bs-accordion-btn-icon: none; --bs-accordion-btn-active-icon: none;">

                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingCategories">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseCategories">
                                                <span>Danh mục</span>
                                                <i data-feather="chevron-down" class="ms-auto"></i>
                                            </button>
                                        </h2>

                                        <div id="collapseCategories" class="accordion-collapse collapse show">
                                            <div class="accordion-body">
                                                @php
                                                    $activeIds = $activeCategoryIds ?? [];

                                                    $renderCategoryTree = function ($items, $level = 0) use (&$renderCategoryTree, $activeIds) {
                                                        if (!isset($items) || $items->count() === 0) {
                                                            return;
                                                        }

                                                        foreach ($items as $item) {
                                                            $children = $item->childrenRecursive ?? collect();
                                                            $hasChildren = $children->count() > 0;
                                                            $isActive = isset($activeIds) && in_array($item->id, $activeIds, true);
                                                            $isCurrent = request()->route('id') == $item->id;

                                                            $collapseId = 'cat_' . $item->id;
                                                @endphp

                                                            <div class="d-flex align-items-center justify-content-between"
                                                                @if($level > 0) style="padding-left: {{ $level * 14 }}px;" @endif>
                                                                <a href="{{ route('client.category', $item->id) }}"
                                                                    class="text-content {{ $isCurrent ? 'fw-bold theme-color' : '' }}">
                                                                    {{ $item->name }}
                                                                </a>

                                                                @if($hasChildren)
                                                                    <button class="btn btn-sm btn-light"
                                                                        type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#{{ $collapseId }}"
                                                                        aria-expanded="{{ $isActive ? 'true' : 'false' }}"
                                                                        aria-controls="{{ $collapseId }}">
                                                                        <i data-feather="chevron-down"></i>
                                                                    </button>
                                                                @endif
                                                            </div>

                                                            @if($hasChildren)
                                                                <div id="{{ $collapseId }}" class="collapse {{ $isActive ? 'show' : '' }}">
                                                                    <div class="mt-2">
                                                                        @php $renderCategoryTree($children, $level + 1); @endphp
                                                                    </div>
                                                                </div>
                                                            @endif

                                                @php
                                                        }
                                                    };
                                                @endphp

                                                @if(isset($categories) && $categories->count() > 0)
                                                    <div class="custom-nav-tab">
                                                        @php $renderCategoryTree($categories, 0); @endphp
                                                    </div>
                                                @else
                                                    <div class="text-content">Không có dữ liệu</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseOne">
                                                <span>Tìm kiếm</span>
                                                <i data-feather="chevron-down" class="ms-auto"></i>
                                            </button>
                                        </h2>

                                        <div id="collapseOne" class="accordion-collapse collapse show">
                                            <div class="accordion-body">
                                                <div class="form-floating theme-form-floating-2 search-box">
                                                    <input type="search" class="form-control" id="search" name="q"
                                                        value="{{ request('q') }}" placeholder="Tìm theo tên, mô tả...">
                                                    <label for="search">Từ khóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingBrands">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseBrands">
                                                <span>Thương hiệu</span>
                                                <i data-feather="chevron-down" class="ms-auto"></i>
                                            </button>
                                        </h2>

                                        <div id="collapseBrands" class="accordion-collapse collapse show">
                                            <div class="accordion-body">
                                                @if(isset($brands) && $brands->count() > 0)
                                                    <div class="custom-nav-tab">
                                                        @foreach($brands as $brand)
                                                            @php
                                                                $selectedBrands = (array) request('brand', []);
                                                                $isChecked = in_array((string) $brand->id, array_map('strval', $selectedBrands), true);
                                                            @endphp

                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="brand[]" value="{{ $brand->id }}"
                                                                    id="brand_{{ $brand->id }}" @checked($isChecked)>
                                                                <label class="form-check-label" for="brand_{{ $brand->id }}">
                                                                    {{ $brand->name }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-content">Không có dữ liệu</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingPrice">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapsePrice">
                                                <span>Khoảng giá</span>
                                                <i data-feather="chevron-down" class="ms-auto"></i>
                                            </button>
                                        </h2>

                                        <div id="collapsePrice" class="accordion-collapse collapse show">
                                            <div class="accordion-body">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <div class="form-floating theme-form-floating-2">
                                                            <input type="number" class="form-control" name="price_min" id="price_min"
                                                                value="{{ request('price_min', isset($priceRangeMin) ? (int) $priceRangeMin : '') }}"
                                                                min="0" placeholder="Từ">
                                                            <label for="price_min">Từ</label>
                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="form-floating theme-form-floating-2">
                                                            <input type="number" class="form-control" name="price_max" id="price_max"
                                                                value="{{ request('price_max', isset($priceRangeMax) ? (int) $priceRangeMax : '') }}"
                                                                min="0" placeholder="Đến">
                                                            <label for="price_max">Đến</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if(isset($priceRangeMin) && isset($priceRangeMax))
                                                    <div class="text-content mt-2">
                                                        Giá hiện có:
                                                        {{ number_format((float) $priceRangeMin, 0, ',', '.') }} đ -
                                                        {{ number_format((float) $priceRangeMax, 0, ',', '.') }} đ
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingSort">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseSort">
                                                <span>Sắp xếp</span>
                                                <i data-feather="chevron-down" class="ms-auto"></i>
                                            </button>
                                        </h2>

                                        <div id="collapseSort" class="accordion-collapse collapse show">
                                            <div class="accordion-body">
                                                <select class="form-select" name="sort">
                                                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>
                                                        Mới nhất
                                                    </option>
                                                    <option value="price_asc" @selected(request('sort') === 'price_asc')>
                                                        Giá tăng dần
                                                    </option>
                                                    <option value="price_desc" @selected(request('sort') === 'price_desc')>
                                                        Giá giảm dần
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 d-grid gap-2">
                                    <button type="submit" class="btn btn-animation w-100">Áp dụng</button>
                                    <a href="{{ route('client.category', $category->id) }}" class="btn btn-light w-100">Xóa lọc</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Product list -->
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

                    @if(method_exists($products, 'links'))
                        <div class="cc-pagination">
                            {{ $products->appends(request()->query())->links() }}
                        </div>
                    @endif
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
        document.addEventListener('DOMContentLoaded', function () {
            if (window.feather) {
                feather.replace();
            }
        });
    </script>
@endsection
