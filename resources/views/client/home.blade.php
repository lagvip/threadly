@extends('client.layouts.master')

@section('title', 'Giao hàng chặng cuối theo yêu cầu')

@section('content')
<!-- mobile fix menu start -->
    <div class="mobile-menu d-md-none d-block mobile-cart">
        <ul>
            <li class="active">
                <a href="index.html">
                    <i class="iconly-Home icli"></i>
                    <span>Trang chủ</span>
                </a>
            </li>

            <li class="mobile-category">
                <a href="javascript:void(0)">
                    <i class="iconly-Category icli js-link"></i>
                    <span>Danh mục</span>
                </a>
            </li>

            <li>
                <a href="search.html" class="search-box">
                    <i class="iconly-Search icli"></i>
                    <span>Tìm kiếm</span>
                </a>
            </li>

            <li>
                <a href="wishlist.html" class="notifi-wishlist">
                    <i class="iconly-Heart icli"></i>
                    <span>Yêu thích</span>
                </a>
            </li>

            <li>
                <a href="cart.html">
                    <i class="iconly-Bag-2 icli fly-cate"></i>
                    <span>Giỏ hàng</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- mobile fix menu end -->

    <!-- Home Section Start -->
    <section class="home-section pt-2 ratio_50">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-12">
                @if($banners->count() > 0)
                    <div id="mainBannerCarousel" class="carousel slide" data-bs-ride="carousel">

                        <div class="carousel-inner">
                            @foreach($banners as $key => $banner)
                                <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                    <div class="home-contain furniture-contain-2">
                                        <img src="{{ asset('storage/' . $banner->image) }}"
                                            class="bg-img blur-up lazyload w-100" alt="{{ $banner->title }}">

                                        <div class="home-detail p-top-left mend-auto w-100">
                                            <div>
                                                <h6>Ưu đãi độc quyền <span> {{ $banner->title }}</span></h6>
                                                @if(!empty($banner->link))
                                                    <button onclick="location.href='{{ $banner->link }}';"
                                                        class="btn theme-bg-color mt-sm-4 mt-2 btn-md text-white fw-bold">
                                                        Mua ngay
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#mainBannerCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>

                        <button class="carousel-control-next" type="button" data-bs-target="#mainBannerCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>

                        <div class="carousel-indicators">
                            @foreach($banners as $key => $banner)
                                <button type="button"
                                    data-bs-target="#mainBannerCarousel"
                                    data-bs-slide-to="{{ $key }}"
                                    class="{{ $key == 0 ? 'active' : '' }}">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
    <!-- Home Section End -->

    <!-- Service Section Start -->
    <section class="service-section">
        <div class="container-fluid-lg">

            <div class="row g-3 row-cols-xxl-5 row-cols-lg-3 row-cols-md-2">
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#shipping"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Miễn phí vận chuyển</h3>
                            <h6 class="text-content">Miễn phí vận chuyển toàn quốc</h6>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#service"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Hỗ trợ 24/7</h3>
                            <h6 class="text-content">Phục vụ trực tuyến 24/7</h6>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#pay"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Thanh toán online</h3>
                            <h6 class="text-content">Hỗ trợ thanh toán trực tuyến</h6>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#offer"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Ưu đãi lễ hội</h3>
                            <h6 class="text-content">Siêu sale giảm tới 50%</h6>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#return"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Hàng chính hãng 100%</h3>
                            <h6 class="text-content">Hoàn tiền 100%</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Service Section End -->


    <!-- Product Section Start -->
    <section class="product-section">
        <div class="container-fluid-lg">
             <div class="row g-sm-4 g-3">
                <div class="title">
                    <h2>Mua sắm theo danh mục</h2>
                    <span class="title-leaf">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/leaf.svg#leaf"></use>
                        </svg>
                    </span>
                    <p>Danh mục nổi bật trong tuần</p>
                </div>

                <div class="category-slider-2 product-wrapper no-arrow">
                    @if(isset($categories) && $categories->count() > 0)
                        @foreach($categories as $category)
                            <div>
                                <a href="{{ route('home') }}" class="category-box category-dark">
                                    <div>
                                        <img src="{{ $category->image ? asset('storage/' . $category->image) : 'https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/decorations.svg' }}"
                                            class="blur-up lazyload"
                                            alt="{{ $category->name }}">
                                        <h5>{{ $category->name }}</h5>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="col-xxl-9 col-xl-8">
                    <div class="title title-flex">

                        <div>
                            <h2>Ưu đãi nổi bật hôm nay</h2>
                            <span class="title-leaf">
                                <svg class="icon-width">
                                    <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/leaf.svg#leaf"></use>
                                </svg>
                            </span>
                            <p>Đừng bỏ lỡ cơ hội nhận mức giá đặc biệt chỉ trong tuần này.</p>
                        </div>
                        <div class="timing-box">
                            <div class="timing">
                                <i data-feather="clock"></i>
                                <h6 class="name">Kết thúc sau :</h6>
                                <div class="time" id="clockdiv-1" data-hours="1" data-minutes="2" data-seconds="3">
                                    <ul>
                                        <li>
                                            <div class="counter">
                                                <div class="days">
                                                    <h6></h6>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="counter">
                                                <div class="hours">
                                                    <h6></h6>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="counter">
                                                <div class="minutes">
                                                    <h6></h6>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="counter">
                                                <div class="seconds">
                                                    <h6></h6>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-b-space">
                        <div class="row row-cols-xxl-5 row-cols-md-4 row-cols-sm-3 row-cols-2 g-sm-4 g-3 no-arrow">
                            @foreach($featuredProducts as $product)
                                @php
                                    $variant = $product->variants->first();
                                @endphp

                                <div>
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
                        </div>
                    </div>



                    <div class="section-t-space section-b-space">
                    </div>

                    <div class="title d-block">
                        <h2>Tủ bếp thực phẩm</h2>
                        <span class="title-leaf">
                            <svg class="icon-width">
                                <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/leaf.svg#leaf"></use>
                            </svg>
                        </span>
                        <p>Trợ lý ảo sẽ gom các sản phẩm từ danh sách của bạn</p>
                    </div>

                    <div class="row row-cols-xxl-5 row-cols-md-4 row-cols-sm-3 row-cols-2 g-sm-4 g-3 no-arrow">
                        <div>
                            <div class="product-box product-white-bg wow fadeIn">
                                <div class="product-image">
                                    <a href="product-left-thumbnail.html">
                                        <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/13.png') }}" class="img-fluid blur-up lazyload"
                                            alt="">
                                    </a>
                                    <ul class="product-option">
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view">
                                                <i data-feather="eye"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                            <a href="compare.html">
                                                <i data-feather="refresh-cw"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                            <a href="wishlist.html" class="notifi-wishlist">
                                                <i data-feather="heart"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product-detail position-relative">
                                    <a href="product-left-thumbnail.html">
                                        <h6 class="name">Bộ chén đĩa tròn bóng Elama cao cấp</h6>
                                    </a>

                                    <h6 class="sold weight text-content fw-normal">1 KG</h6>

                                    <h6 class="price theme-color">$ 80.00</h6>

                                    <div class="add-to-cart-btn-2 addtocart_btn">
                                        <button class="btn addcart-button btn buy-button"><i
                                                class="fa-solid fa-plus"></i></button>
                                        <div class="cart_qty qty-box-2">
                                            <div class="input-group">
                                                <button type="button" class="qty-left-minus" data-type="minus"
                                                    data-field="">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                                <input class="form-control input-number qty-input" type="text"
                                                    name="quantity" value="1">
                                                <button type="button" class="qty-right-plus" data-type="plus"
                                                    data-field="">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box product-white-bg wow fadeIn" data-wow-delay="0.1s">
                                <div class="product-image">
                                    <a href="product-left-thumbnail.html">
                                        <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/8.png') }}" class="img-fluid blur-up lazyload"
                                            alt="">
                                    </a>
                                    <ul class="product-option">
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view">
                                                <i data-feather="eye"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                            <a href="compare.html">
                                                <i data-feather="refresh-cw"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                            <a href="wishlist.html" class="notifi-wishlist">
                                                <i data-feather="heart"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product-detail position-relative">
                                    <a href="product-left-thumbnail.html">
                                        <h6 class="name">Đĩa tiệc lục giác vân đá sang trọng</h6>
                                    </a>

                                    <h6 class="sold weight text-content fw-normal">1 KG</h6>

                                    <h6 class="price theme-color">$ 80.00</h6>

                                    <div class="add-to-cart-btn-2 addtocart_btn">
                                        <button class="btn addcart-button btn buy-button"><i
                                                class="fa-solid fa-plus"></i></button>
                                        <div class="cart_qty qty-box-2">
                                            <div class="input-group">
                                                <button type="button" class="qty-left-minus" data-type="minus"
                                                    data-field="">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                                <input class="form-control input-number qty-input" type="text"
                                                    name="quantity" value="1">
                                                <button type="button" class="qty-right-plus" data-type="plus"
                                                    data-field="">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box product-white-bg wow fadeIn">
                                <div class="product-image">
                                    <a href="product-left-thumbnail.html">
                                        <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/10.png') }}" class="img-fluid blur-up lazyload"
                                            alt="">
                                    </a>
                                    <ul class="product-option">
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view">
                                                <i data-feather="eye"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                            <a href="compare.html">
                                                <i data-feather="refresh-cw"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                            <a href="wishlist.html" class="notifi-wishlist">
                                                <i data-feather="heart"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product-detail position-relative">
                                    <a href="product-left-thumbnail.html">
                                        <h6 class="name">Giỏ mây tròn chịu lực tốt</h6>
                                    </a>

                                    <h6 class="sold weight text-content fw-normal">1 KG</h6>

                                    <h6 class="price theme-color">$ 80.00</h6>

                                    <div class="add-to-cart-btn-2 addtocart_btn">
                                        <button class="btn addcart-button btn buy-button"><i
                                                class="fa-solid fa-plus"></i></button>
                                        <div class="cart_qty qty-box-2">
                                            <div class="input-group">
                                                <button type="button" class="qty-left-minus" data-type="minus"
                                                    data-field="">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                                <input class="form-control input-number qty-input" type="text"
                                                    name="quantity" value="1">
                                                <button type="button" class="qty-right-plus" data-type="plus"
                                                    data-field="">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box product-white-bg wow fadeIn" data-wow-delay="0.1s">
                                <div class="product-image">
                                    <a href="product-left-thumbnail.html">
                                        <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/6.png') }}" class="img-fluid blur-up lazyload"
                                            alt="">
                                    </a>
                                    <ul class="product-option">
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view">
                                                <i data-feather="eye"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                            <a href="compare.html">
                                                <i data-feather="refresh-cw"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                            <a href="wishlist.html" class="notifi-wishlist">
                                                <i data-feather="heart"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product-detail position-relative">
                                    <a href="product-left-thumbnail.html">
                                        <h6 class="name">Chăn cũi dệt len Merino cho bé</h6>
                                    </a>

                                    <h6 class="sold weight text-content fw-normal">1 KG</h6>

                                    <h6 class="price theme-color">$ 80.00</h6>

                                    <div class="add-to-cart-btn-2 addtocart_btn">
                                        <button class="btn addcart-button btn buy-button"><i
                                                class="fa-solid fa-plus"></i></button>
                                        <div class="cart_qty qty-box-2">
                                            <div class="input-group">
                                                <button type="button" class="qty-left-minus" data-type="minus"
                                                    data-field="">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                                <input class="form-control input-number qty-input" type="text"
                                                    name="quantity" value="1">
                                                <button type="button" class="qty-right-plus" data-type="plus"
                                                    data-field="">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box product-white-bg wow fadeIn">
                                <div class="product-image">
                                    <a href="product-left-thumbnail.html">
                                        <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/4.png') }}" class="img-fluid blur-up lazyload"
                                            alt="">
                                    </a>
                                    <ul class="product-option">
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view">
                                                <i data-feather="eye"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                            <a href="compare.html">
                                                <i data-feather="refresh-cw"></i>
                                            </a>
                                        </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                            <a href="wishlist.html" class="notifi-wishlist">
                                                <i data-feather="heart"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product-detail position-relative">
                                    <a href="product-left-thumbnail.html">
                                        <h6 class="name">Khăn tắm ELSTONE HOME màu trắng</h6>
                                    </a>

                                    <h6 class="sold weight text-content fw-normal">1 KG</h6>

                                    <h6 class="price theme-color">$ 80.00</h6>

                                    <div class="add-to-cart-btn-2 addtocart_btn">
                                        <button class="btn addcart-button btn buy-button"><i
                                                class="fa-solid fa-plus"></i></button>
                                        <div class="cart_qty qty-box-2">
                                            <div class="input-group">
                                                <button type="button" class="qty-left-minus" data-type="minus"
                                                    data-field="">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                                <input class="form-control input-number qty-input" type="text"
                                                    name="quantity" value="1">
                                                <button type="button" class="qty-right-plus" data-type="plus"
                                                    data-field="">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-xl-4 d-none d-xl-block">
                    <div class="p-sticky">
                        <div class="category-menu">
                            <h3>Mua sắm theo sản phẩm</h3>
                            <ul class="border-bottom-0">
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/decorations.svg"
                                            class="blur-up lazyload" alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Đồ trang trí</a>
                                        </h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/pillows.svg" class="blur-up lazyload"
                                            alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Chăn ga gối</a>
                                        </h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/cushions.svg" class="blur-up lazyload"
                                            alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Gối tựa</a>
                                        </h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/blankets.svg" class="blur-up lazyload"
                                            alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Chăn mền</a>
                                        </h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/gift.svg" class="blur-up lazyload"
                                            alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Gói quà</a>
                                        </h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/sleepware.svg"
                                            class="blur-up lazyload" alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Đồ ngủ</a>
                                        </h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/bakeware.svg" class="blur-up lazyload"
                                            alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Đồ nấu nướng & làm bánh</a>
                                        </h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/room-fragrance.svg"
                                            class="blur-up lazyload" alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Hương thơm phòng</a>
                                        </h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/tableware.svg"
                                            class="blur-up lazyload" alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Đồ dùng bàn ăn</a>
                                        </h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="category-list">
                                        <img src="https://themes.pixelstrap.com/fastkart/assets/images/furniture/icon/shower.svg" class="blur-up lazyload"
                                            alt="">
                                        <h5>
                                            <a href="shop-left-sidebar.html">Tắm & vòi sen</a>
                                        </h5>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="ratio_156 section-t-space">
                            <div class="home-contain hover-effect">
                                <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/banner/3.jpg') }}" class="bg-img blur-up lazyload"
                                    alt="">
                                <div class="home-detail p-top-left home-p-medium">
                                    <div>
                                        <h4 class="text-yellow home-banner text-kaushan">Hàng mới về</h4>
                                        <h3 class="text-uppercase theme-color fw-bold mb-1">Bàn làm việc</h3>
                                        <p class="text-content mb-3">Bán chạy nhất tuần! Ưu đãi độc quyền!</p>
                                        <button onclick="location.href = 'shop-left-sidebar.html';"
                                            class="btn btn-furniture btn-md mend-auto" >Mua ngay <i
                                                class="fa-solid fa-arrow-right icon"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-t-space">
                            <div class="category-menu">
                                <h3>Sản phẩm xu hướng</h3>

                                <ul class="product-list border-0 p-0 d-block">
                                    <li>
                                        <div class="offer-product">
                                            <a href="product-left-thumbnail.html" class="offer-image">
                                                <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/2.png') }}" class="blur-up lazyload"
                                                    alt="">
                                            </a>

                                            <div class="offer-detail">
                                                <div>
                                                    <a href="product-left-thumbnail.html" class="text-title">
                                                        <h6 class="name">Cà ri dê cao cấp Meatigo</h6>
                                                    </a>
                                                    <span>450 G</span>
                                                    <h6 class="price theme-color">$ 70.00</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="offer-product">
                                            <a href="product-left-thumbnail.html" class="offer-image">
                                                <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/3.png') }}" class="blur-up lazyload"
                                                    alt="">
                                            </a>

                                            <div class="offer-detail">
                                                <div>
                                                    <a href="product-left-thumbnail.html" class="text-title">
                                                        <h6 class="name">Ghế lười Coral</h6>
                                                    </a>
                                                    <span>450 G</span>
                                                    <h6 class="price theme-color">$ 40.00</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="mb-0">
                                        <div class="offer-product">
                                            <a href="product-left-thumbnail.html" class="offer-image">
                                                <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/5.png') }}" class="blur-up lazyload"
                                                    alt="">
                                            </a>

                                            <div class="offer-detail">
                                                <div>
                                                    <a href="product-left-thumbnail.html" class="text-title">
                                                        <h6 class="name">Lợi ích của việc sử dụng sàn đá tự nhiên
                                                        </h6>
                                                    </a>
                                                    <span>1 KG</span>
                                                    <h6 class="price theme-color">$ 80.00</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Product Section End -->

    <!-- Banner Section Start -->
    <section class="banner-section">
        <div class="container-fluid-lg">
            <div class="row">
            </div>
        </div>
    </section>
    <!-- Banner Section End -->

    <!-- Best Seller Section Start -->
    <section>
        <div class="container-fluid-lg">
            <div class="title d-block">
                <div>
                    <h2>Sản phẩm bán chạy nhất</h2>
                    <span class="title-leaf">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/leaf.svg#leaf"></use>
                        </svg>
                    </span>
                    <p>Trợ lý ảo sẽ gom các sản phẩm từ danh sách của bạn</p>
                </div>
            </div>
            <div class="banner-slider product-wrapper wow fadeInUp">
                <div>
                    <ul class="product-list">
                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/1.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Tượng hươu may mắn hoàn thiện mờ</h6>
                                        </a>
                                        <span>500 G</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/2.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Khoai tây</h6>
                                        </a>
                                        <span>500 G</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/3.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Ghế lười Coral</h6>
                                        </a>
                                        <span>200 G</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/4.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Khăn tắm ELSTONE HOME màu trắng</h6>
                                        </a>
                                        <span>150 G</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div>
                    <ul class="product-list">
                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/5.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Lợi ích của việc sử dụng sàn đá tự nhiên</h6>
                                        </a>
                                        <span>500 G</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/6.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Chăn cũi dệt len Merino cho bé</h6>
                                        </a>
                                        <span>1 L</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/7.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Đế lót cốc gỗ</h6>
                                        </a>
                                        <span>1 KG</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/8.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Đĩa lục giác vân đá</h6>
                                        </a>
                                        <span>150 G</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div>
                    <ul class="product-list">
                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/9.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Khay gỗ xoài thủ công màu nâu hình vuông</h6>
                                        </a>
                                        <span>1 L</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/10.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Giỏ mây tròn chịu lực tốt</h6>
                                        </a>
                                        <span>500 G</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/11.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Bình hoa sợi cổ điển WaahKart</h6>
                                        </a>
                                        <span>1 KG</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/12.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Khăn bông mềm mại</h6>
                                        </a>
                                        <span>160 ML</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div>
                    <ul class="product-list">
                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/13.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Bộ chén đĩa bóng đẹp</h6>
                                        </a>
                                        <span>500 G</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/14.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Khay tiện ích nhỏ vân đá</h6>
                                        </a>
                                        <span>1 L</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/5.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Lợi ích của việc sử dụng sàn đá tự nhiên</h6>
                                        </a>
                                        <span>1 KG</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="offer-product">
                                <a href="product-left-thumbnail.html" class="offer-image">
                                    <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/furniture/9.png') }}" class="blur-up lazyload" alt="">
                                </a>

                                <div class="offer-detail">
                                    <div>
                                        <a href="product-left-thumbnail.html" class="text-title">
                                            <h6 class="name">Khay gỗ xoài thủ công màu nâu hình vuông</h6>
                                        </a>
                                        <span>150 G</span>
                                        <h6 class="price theme-color">$ 10.00</h6>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- Best Seller Section End -->

    <!-- Newsletter Section Start -->
    <section class="newsletter-section section-b-space">
        <div class="container-fluid-lg">
            <div class="newsletter-box newsletter-box-2">

            </div>
        </div>
    </section>
    <!-- Newsletter Section End -->

    <!-- Footer Section Start -->
    <footer class="section-t-space footer-section-2 footer-color-3">
@endsection
<style>
    .category-box img {
    width: 80px !important;
    height: 80px !important;
    object-fit: contain;
    margin: 0 auto 12px;
}

.category-box.category-dark {
    padding: 20px 15px;
}
</style>
