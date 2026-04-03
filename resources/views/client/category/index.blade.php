@extends('client.layouts.master')

@section('content')


<section class="section-b-space shop-section">
    <div class="container-fluid-lg">
        <div class="row">
            <!-- filter  -->
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
                                                        <div class="d-flex align-items-center justify-content-between" @if($level > 0) style="padding-left: {{ $level * 14 }}px;" @endif>
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
                                                            <label class="form-check-label" for="brand_{{ $brand->id }}">{{ $brand->name }}</label>
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
                                                    Giá hiện có: {{ number_format((float) $priceRangeMin, 0, ',', '.') }} đ - {{ number_format((float) $priceRangeMax, 0, ',', '.') }} đ
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
                                                <option value="newest" @selected(request('sort', 'newest') === 'newest')>Mới nhất</option>
                                                <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá tăng dần</option>
                                                <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá giảm dần</option>
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
            <!-- end filter -->
            <div class="col-lg-9 col-xl-9">
                <div class="row g-3">
                    @if(isset($products) && $products->count() > 0)
                    @foreach($products as $product)
                    @php
                    $variant = $product->variants->first();
                    @endphp
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="product-box product-white-bg wow fadeIn">
                            <div class="product-image">
                                <a href="{{ route('client.product.detail', $product->id) }}">
                                    <img src="{{ asset('storage/' . $product->image_primary) }}" alt="{{ $product->name }}">
                                </a>

                                <ul class="product-option">
                                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view">
                                            <i data-feather="eye"></i>
                                        </a>
                                    </li>

                                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                        <a href="#">
                                            <i data-feather="refresh-cw"></i>
                                        </a>
                                    </li>

                                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                        <a href="#" class="notifi-wishlist">
                                            <i data-feather="heart"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="product-detail position-relative">
                                <a href="{{ route('client.product.detail', $product->id) }}">
                                    <h6 class="name">{{ $product->name }}</h6>
                                </a>

                                <h6 class="sold weight text-content fw-normal">
                                    Tồn kho: {{ $variant->quantity ?? 0 }}
                                </h6>

                                <h6 class="price theme-color">
                                    {{ number_format($variant->price ?? 0, 0, ',', '.') }} đ
                                </h6>

                                <div class="add-to-cart-btn-2 addtocart_btn">
                                    <button class="btn addcart-button btn buy-button">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>

                                    <div class="cart_qty qty-box-2">
                                        <div class="input-group">
                                            <button type="button" class="qty-left-minus">
                                                <i class="fa fa-minus"></i>
                                            </button>

                                            <input class="form-control input-number qty-input"
                                                type="text"
                                                name="quantity"
                                                value="1">

                                            <button type="button" class="qty-right-plus">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>
</section>
@endsection