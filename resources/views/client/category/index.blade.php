@extends('client.layouts.master')

@section('content')


<section class="section-b-space shop-section">
    <div class="container-fluid-lg">
        <div class="row">
            <!-- filter  -->
            <div class="col-lg-3">
                <div class="left-box wow fadeInUp">
                    <div class="shop-left-sidebar">
                        <div class="accordion custom-accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseOne">
                                        <span>Categories</span>
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="form-floating theme-form-floating-2 search-box">
                                            <input type="search" class="form-control" id="search"
                                                placeholder="Search ..">
                                            <label for="search">Search</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                        <span>Price</span>
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <div class="range-slider">
                                            <input type="text" class="js-range-slider" value="1">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
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
                {{ $products->links() }}
            </div>
        </div>
    </div>
</section>
@endsection