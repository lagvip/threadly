@extends('client.layouts.master')

@section('content')


<section class="section-b-space shop-section">
    <div class="container-fluid-lg">
        <div class="row">
            <!-- filter  -->
            <div class="col-lg-3">
                <div class="shop-left-sidebar">
                    <form method="GET" action="{{ route('client.category.show', $category->id) }}">

                        <!-- GIÁ -->
                        <h5>Khoảng giá</h5>

                        <input type="range" name="min_price"
                            min="0" max="10000000" step="200000"
                            value="{{ request('min_price', 0) }}" id="minPrice">

                        <input type="range" name="max_price"
                            min="0" max="10000000" step="200000"
                            value="{{ request('max_price', 10000000) }}" id="maxPrice">

                        <p>
                            <span id="minPriceValue"></span> -
                            <span id="maxPriceValue"></span>
                        </p>

                        <!-- RATING -->
                        <h5 class="mt-3">Đánh giá</h5>

                        @for($i = 5; $i >= 1; $i--)
                        <div>
                            <input type="radio" name="rating" value="{{ $i }}"
                                {{ request('rating') == $i ? 'checked' : '' }}>
                            {{ $i }} ⭐ trở lên
                        </div>
                        @endfor
                        <div class="d-flex gap-2 mt-2">
                            <button class="btn btn-solid w-100">
                                Lọc
                            </button>

                            <a href="{{ route('client.category.show', $category->id) }}"
                                class="btn btn-outline w-100">
                                Xóa lọc
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end filter -->
            <div class="col-lg-9 col-xl-9">
                <div class="row g-3">
                    @if(isset($products) && $products->count() > 0)
                    @foreach($products as $product)
                    @php
                    $variant = $product->variants->first();
                    $reviews = $product->reviews;

                    $reviewCount = $reviews->count();

                    $averageRating = $reviewCount > 0
                    ? round($reviews->avg('rating'), 1)
                    : 0;
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
                                <h6 class="price theme-color">
                                    {{ number_format($averageRating, 1) }}
                                    <i data-feather="star"></i>
                                </h6>

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
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
</section>
<script>
    const minInput = document.getElementById('minPrice');
    const maxInput = document.getElementById('maxPrice');

    const minText = document.getElementById('minPriceValue');
    const maxText = document.getElementById('maxPriceValue');

    function formatMoney(value) {
        return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
    }

    function updatePrice() {
        minText.innerText = formatMoney(minInput.value);
        maxText.innerText = formatMoney(maxInput.value);
    }

    minInput.addEventListener('input', updatePrice);
    maxInput.addEventListener('input', updatePrice);

    updatePrice();
</script>
@endsection