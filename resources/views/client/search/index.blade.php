@extends('client.layouts.master')

@section('content')
    @php
        $productTotal = method_exists($products, 'total') ? $products->total() : $products->count();
        $keywordText = request('q');
    @endphp

    <section class="section-b-space search-page-section">
        <div class="container-fluid-lg">
            <div class="search-page-heading">
                <div>
                    <h2>Tìm kiếm sản phẩm</h2>

                    @if($keywordText)
                        <p>
                            Kết quả cho từ khóa:
                            <strong>"{{ $keywordText }}"</strong>
                        </p>
                    @else
                        <p>Nhập từ khóa để tìm sản phẩm phù hợp.</p>
                    @endif
                </div>

                <div class="search-result-count">
                    {{ $productTotal }} sản phẩm
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="search-filter-box">
                        <form method="GET" action="{{ route('client.products.search') }}">
                            <div class="filter-block">
                                <h4>Từ khóa</h4>

                                <div class="search-input-box">
                                    <input type="search"
                                           name="q"
                                           value="{{ request('q') }}"
                                           placeholder="Tên sản phẩm, thương hiệu..."
                                           autocomplete="off">
                                </div>
                            </div>

                            <div class="filter-block">
                                <h4>Danh mục</h4>

                                <select name="category_id" class="form-select">
                                    <option value="">Tất cả danh mục</option>

                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                            {{ $category->name }}
                                        </option>

                                        @foreach($category->childrenRecursive ?? [] as $child)
                                            <option value="{{ $child->id }}" @selected((string) request('category_id') === (string) $child->id)>
                                                — {{ $child->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-block">
                                <h4>Thương hiệu</h4>

                                <div class="brand-list">
                                    @forelse($brands as $brand)
                                        @php
                                            $selectedBrands = (array) request('brand', []);
                                            $isChecked = in_array((string) $brand->id, array_map('strval', $selectedBrands), true);
                                        @endphp

                                        <label class="brand-check">
                                            <input type="checkbox"
                                                   name="brand[]"
                                                   value="{{ $brand->id }}"
                                                   @checked($isChecked)>
                                            <span>{{ $brand->name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-muted mb-0">Không có thương hiệu.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="filter-block">
                                <h4>Khoảng giá</h4>

                                <div class="price-grid">
                                    <input type="number"
                                           name="price_min"
                                           min="0"
                                           value="{{ request('price_min') }}"
                                           placeholder="Từ">

                                    <input type="number"
                                           name="price_max"
                                           min="0"
                                           value="{{ request('price_max') }}"
                                           placeholder="Đến">
                                </div>

                                @if(isset($priceRangeMin) && isset($priceRangeMax))
                                    <div class="price-note">
                                        Giá hiện có:
                                        {{ number_format((float) $priceRangeMin, 0, ',', '.') }} đ -
                                        {{ number_format((float) $priceRangeMax, 0, ',', '.') }} đ
                                    </div>
                                @endif
                            </div>

                            <div class="filter-block">
                                <h4>Sắp xếp</h4>

                                <select name="sort" class="form-select">
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

                            <div class="filter-actions">
                                <button type="submit" class="btn-search-filter">
                                    Áp dụng
                                </button>

                                <a href="{{ route('client.products.search') }}" class="btn-clear-filter">
                                    Xóa lọc
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="search-toolbar">
                        <div>
                            <strong>{{ $productTotal }}</strong> sản phẩm được tìm thấy
                        </div>

                        @if($keywordText)
                            <div class="keyword-pill">
                                {{ $keywordText }}
                            </div>
                        @endif
                    </div>

                    <div class="row g-4">
                        @forelse($products as $product)
                            @php
                                $variant = $product->variants->first();
                                $reviews = $product->reviews ?? collect();
                                $reviewCount = $reviews->count();
                                $averageRating = $reviewCount > 0 ? round($reviews->avg('rating'), 1) : 0;

                                $rawImage = $product->image_primary ?? null;
                                $image = $rawImage
                                    ? asset('storage/' . ltrim($rawImage, '/'))
                                    : asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/placeholder.png');
                            @endphp

                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="search-product-card">
                                    <a href="{{ route('client.product.detail', $product->id) }}" class="search-product-img">
                                        <img src="{{ $image }}" alt="{{ $product->name }}">
                                    </a>

                                    <div class="search-product-body">
                                        <div class="search-product-category">
                                            {{ $product->category->name ?? 'Sản phẩm' }}
                                        </div>

                                        <a href="{{ route('client.product.detail', $product->id) }}" class="search-product-name">
                                            {{ $product->name }}
                                        </a>

                                        <div class="search-product-rating">
                                            <ul class="rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <li>
                                                        <i data-feather="star" class="{{ $i <= round($averageRating) ? 'fill' : '' }}"></i>
                                                    </li>
                                                @endfor
                                            </ul>
                                            <span>({{ number_format($averageRating, 1) }})</span>
                                        </div>

                                        <div class="search-product-stock">
                                            Tồn kho: {{ $variant->quantity ?? 0 }}
                                        </div>

                                        <div class="search-product-price">
                                            {{ number_format($variant->price ?? 0, 0, ',', '.') }} đ
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="search-empty-box">
                                    <h4>Không tìm thấy sản phẩm phù hợp</h4>
                                    <p>Thử nhập từ khóa khác hoặc bỏ bớt bộ lọc.</p>
                                    <a href="{{ route('client.products.search') }}" class="btn-clear-filter d-inline-flex mt-3">
                                        Xóa bộ lọc
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if(method_exists($products, 'links'))
                        <div class="search-pagination">
                            {{ $products->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <style>
        .search-page-section {
            background: #f7f7f7;
            padding-top: 34px;
            padding-bottom: 48px;
        }

        .search-page-heading {
            background: #fff;
            border-radius: 20px;
            padding: 24px 28px;
            margin-bottom: 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        .search-page-heading h2 {
            margin: 0 0 6px;
            color: #222;
            font-size: 28px;
            font-weight: 800;
        }

        .search-page-heading p {
            margin: 0;
            color: #64748b;
            font-size: 15px;
        }

        .search-result-count,
        .keyword-pill {
            background: #fff3ed;
            color: var(--theme-color, #ff6b35);
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 800;
            white-space: nowrap;
        }

        .search-filter-box,
        .search-toolbar,
        .search-empty-box {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        .search-filter-box {
            padding: 22px;
            position: sticky;
            top: 100px;
        }

        .filter-block + .filter-block {
            margin-top: 24px;
            padding-top: 22px;
            border-top: 1px solid #eef2f7;
        }

        .filter-block h4 {
            margin-bottom: 14px;
            font-size: 17px;
            font-weight: 800;
            color: #222;
        }

        .search-input-box input,
        .price-grid input,
        .search-filter-box .form-select {
            width: 100%;
            height: 44px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 0 14px;
            color: #334155;
            background: #f8fafc;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-input-box input:focus,
        .price-grid input:focus,
        .search-filter-box .form-select:focus {
            border-color: var(--theme-color, #ff6b35);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        .brand-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 210px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .brand-check {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #475569;
            cursor: pointer;
            margin: 0;
        }

        .brand-check input {
            accent-color: var(--theme-color, #ff6b35);
        }

        .price-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .price-note {
            margin-top: 10px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        .filter-actions {
            display: grid;
            gap: 10px;
            margin-top: 24px;
        }

        .btn-search-filter,
        .btn-clear-filter {
            min-height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-weight: 800;
            border: none;
        }

        .btn-search-filter {
            background: var(--theme-color, #ff6b35);
            color: #fff;
        }

        .btn-clear-filter {
            background: #f1f5f9;
            color: #334155;
        }

        .search-toolbar {
            padding: 18px 22px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            color: #475569;
        }

        .search-product-card {
            height: 100%;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
            transition: all 0.2s ease;
        }

        .search-product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
        }

        .search-product-img {
            display: block;
            background: #f8fafc;
            padding: 14px;
        }

        .search-product-img img {
            width: 100%;
            height: 230px;
            object-fit: cover;
            border-radius: 16px;
            background: #fff;
        }

        .search-product-body {
            padding: 16px 18px 18px;
        }

        .search-product-category {
            color: #64748b;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .search-product-name {
            display: block;
            color: #222;
            text-decoration: none;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.45;
            min-height: 44px;
            margin-bottom: 10px;
        }

        .search-product-name:hover {
            color: var(--theme-color, #ff6b35);
        }

        .search-product-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
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

        .search-product-rating span,
        .search-product-stock {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        .search-product-stock {
            margin-bottom: 10px;
        }

        .search-product-price {
            color: #2c6e9f;
            font-size: 18px;
            font-weight: 900;
        }

        .search-empty-box {
            padding: 48px 24px;
            text-align: center;
        }

        .search-empty-box h4 {
            margin-bottom: 8px;
            color: #222;
            font-weight: 800;
        }

        .search-empty-box p {
            margin: 0;
            color: #64748b;
        }

        .search-pagination {
            margin-top: 28px;
            display: flex;
            justify-content: center;
        }

        .search-pagination .pagination {
            gap: 8px;
        }

        .search-pagination .page-link {
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

        .search-pagination .page-item.active .page-link {
            background: var(--theme-color, #ff6b35);
            color: #fff;
        }

        @media (max-width: 991.98px) {
            .search-filter-box {
                position: static;
            }
        }

        @media (max-width: 575.98px) {
            .search-page-heading {
                align-items: flex-start;
                flex-direction: column;
                padding: 20px;
            }

            .search-page-heading h2 {
                font-size: 24px;
            }

            .search-product-img img {
                height: 170px;
            }

            .search-product-body {
                padding: 14px;
            }

            .search-product-name {
                font-size: 14px;
                min-height: 40px;
            }

            .search-product-price {
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
