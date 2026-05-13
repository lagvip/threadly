@extends('client.layouts.master')

@section('content')
    @php
        $defaultVariant = $variant ?? $product->variants->first();

        // Ảnh lớn mặc định luôn là ảnh chính của sản phẩm trong DB
        $mainImage = asset('storage/' . $product->image_primary);

        $colors = $product->variants
            ->filter(fn($v) => !empty($v->id_color) && !empty($v->color))
            ->unique('id_color')
            ->values();

        $sizes = $product->variants
            ->filter(fn($v) => !empty($v->id_size) && !empty($v->size))
            ->unique('id_size')
            ->values();

        // Load toàn bộ ảnh sản phẩm: ảnh chính + tất cả ảnh variant
        $galleryImages = collect([
            [
                'type' => 'primary',
                'src' => asset('storage/' . $product->image_primary),
                'variant_id' => null,
            ]
        ])->merge(
            $product->variants
                ->filter(fn($v) => !empty($v->image))
                ->map(fn($v) => [
                    'type' => 'variant',
                    'src' => asset('storage/' . $v->image),
                    'variant_id' => $v->id,
                ])
        )->unique('src')->values();
    @endphp

    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                </div>
            </div>
        </div>
    </section>

    <section class="product-section">
        <div class="container-fluid-lg">
            <div class="row g-4">
                <div class="col-xl-9 col-lg-8 wow fadeInUp">
                    <div class="row g-4">
                        <div class="col-xl-6 wow fadeInUp">
                            <div class="product-left-box">
                                <div class="row g-sm-4 g-2">
                                    <div class="col-xxl-2 col-lg-12 col-md-2 order-xxl-1 order-lg-2 order-md-1">
                                        <div class="left-slider-image-2 left-slider no-arrow slick-top" id="variant-thumbs">
                                            @foreach ($galleryImages as $index => $image)
                                                <div data-index="{{ $index }}" data-variant-id="{{ $image['variant_id'] }}">
                                                    <div class="sidebar-image">
                                                        <img src="{{ $image['src'] }}"
                                                            class="img-fluid"
                                                            alt="{{ $product->name }}">
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-xxl-10 col-lg-12 col-md-10 order-xxl-2 order-lg-1 order-md-2">
                                        <div class="product-main-2 no-arrow" id="product-main-slider">
                                            @foreach ($galleryImages as $index => $image)
                                                <div data-index="{{ $index }}" data-variant-id="{{ $image['variant_id'] }}">
                                                    <div class="slider-image">
                                                        <img src="{{ $image['src'] }}"
                                                            class="img-fluid"
                                                            alt="{{ $product->name }}"
                                                            @if($index === 0) id="main-product-image" @endif>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="right-box-contain">
                                <h6 class="offer-top">Sản phẩm nổi bật</h6>
                                <h2 class="name">{{ $product->name }}</h2>

                                <div class="price-rating">
                                    <h3 class="theme-color price" id="product-price">
                                        {{ number_format($defaultVariant->price ?? 0, 0, ',', '.') }} đ
                                    </h3>
                                </div>

                                <div class="product-contain">
                                </div>

                                <div class="product-package">
                                    <div class="product-title">
                                        <h4>Màu sắc</h4>
                                    </div>

                                    <ul class="select-package" id="color-options">
                                        @forelse($colors as $colorVariant)
                                            <li>
                                                <a href="javascript:void(0)"
                                                   class="color-option"
                                                   data-color-id="{{ $colorVariant->id_color }}">
                                                    {{ $colorVariant->color->name }}
                                                </a>
                                            </li>
                                        @empty
                                            <li>
                                                <a href="javascript:void(0)" class="active">Mặc định</a>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>

                                <div class="product-package">
                                    <div class="product-title">
                                        <h4>Kích thước</h4>
                                    </div>
                                    <div class="selected-variant-info mt-2">
                                        <span id="selected-variant-label">Bạn chưa chọn biến thể</span>
                                    </div>

                                    <ul class="select-package" id="size-options">
                                        @forelse($sizes as $sizeVariant)
                                            <li>
                                                <a href="javascript:void(0)"
                                                   class="size-option"
                                                   data-size-id="{{ $sizeVariant->id_size }}">
                                                    {{ $sizeVariant->size->name }}
                                                </a>
                                            </li>
                                        @empty
                                            <li>
                                                <a href="javascript:void(0)" class="active">Mặc định</a>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>

                                <div class="note-box product-package">
                                    <form action="{{ route('client.cart.add') }}" method="POST" id="add-to-cart-form">
                                        @csrf
                                        <input type="hidden" name="variant_id" id="selected-variant-id">

                                        <div class="cart_qty qty-box product-qty mb-3">
                                            <div class="input-group">
                                                <button type="button" class="my-qty-minus">
                                                    <i class="fa fa-minus"></i>
                                                </button>

                                                <input
                                                    class="form-control qty-input"
                                                    type="number"
                                                    name="quantity"
                                                    id="product-quantity-input"
                                                    value="1"
                                                    min="1"
                                                    step="1"
                                                >

                                                <button type="button" class="my-qty-plus">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit"
                                                    formaction="{{ route('client.cart.add') }}"
                                                    class="btn btn-md bg-dark cart-button text-white w-100">
                                                Thêm vào giỏ hàng
                                            </button>

                                            <button type="submit"
                                                    formaction="{{ route('client.checkout.buyNow') }}"
                                                    class="btn btn-md theme-bg-color text-white w-100">
                                                Mua ngay
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div class="progress-sec">
                                    <div class="left-progressbar">
                                        <h6 id="product-stock">
                                            Tồn kho: {{ $defaultVariant->quantity ?? 0 }}
                                        </h6>
                                        <div class="progress warning-progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                 id="product-stock-bar"
                                                 role="progressbar"
                                                 style="width: {{ min(($defaultVariant->quantity ?? 0), 100) }}%"
                                                 aria-valuenow="{{ min(($defaultVariant->quantity ?? 0), 100) }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="buy-box">
                                    <form action="{{ route('client.wishlist.store') }}" method="POST" id="wishlist-form" class="m-0">
                                        @csrf
                                        <input type="hidden" name="variant_id" id="wishlist-variant-id">

                                        <button type="submit" class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-2">
                                            <i data-feather="heart"></i>
                                            <span>Thêm vào yêu thích</span>
                                        </button>
                                    </form>
                                </div>
                                <div class="pickup-box">
                                    <div class="product-title">
                                        <h4>Thông tin sản phẩm</h4>
                                    </div>

                                    <div class="product-info">
                                        <ul class="product-info-list product-info-list-2">
                                            <li>Danh mục :
                                                <a href="javascript:void(0)">
                                                    {{ $product->category->name ?? 'Đang cập nhật' }}
                                                </a>
                                            </li>
                                            <li>Thương hiệu :
                                                <a href="javascript:void(0)">
                                                    {{ $product->brand->name ?? 'Đang cập nhật' }}
                                                </a>
                                            </li>

                                            <li>Kho :
                                                <a href="javascript:void(0)" id="product-stock-text">
                                                    {{ $defaultVariant->quantity ?? 0 }} sản phẩm
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="payment-option">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0)">
                                                <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/product/payment/1.svg') }}"
                                                     class="blur-up lazyload" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">
                                                <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/product/payment/2.svg') }}"
                                                     class="blur-up lazyload" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">
                                                <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/product/payment/3.svg') }}"
                                                     class="blur-up lazyload" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">
                                                <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/product/payment/4.svg') }}"
                                                     class="blur-up lazyload" alt="">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="product-section-box m-0 product-tab-wrapper">
                                <ul class="nav nav-tabs custom-nav" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab"
                                                data-bs-target="#description" type="button" role="tab">
                                            Mô tả
                                        </button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="info-tab" data-bs-toggle="tab"
                                                data-bs-target="#info" type="button" role="tab">
                                            Thông tin thêm
                                        </button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="review-tab" data-bs-toggle="tab"
                                                data-bs-target="#review" type="button" role="tab">
                                            Đánh giá
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content custom-tab" id="myTabContent">
                                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                                        <div class="product-description">
                                            <div class="nav-desh">
                                                <p>{!! nl2br(e($product->description ?? 'Đang cập nhật nội dung mô tả.')) !!}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="info" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table info-table">
                                                <tbody>
                                                    <tr>
                                                        <td>Tên sản phẩm</td>
                                                        <td>{{ $product->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Danh mục</td>
                                                        <td>{{ $product->category->name ?? 'Đang cập nhật' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Thương hiệu</td>
                                                        <td>{{ $product->brand->name ?? 'Đang cập nhật' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Giá</td>
                                                        <td id="product-price-table">{{ number_format($defaultVariant->price ?? 0, 0, ',', '.') }} đ</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tồn kho</td>
                                                        <td id="product-quantity-table">{{ $defaultVariant->quantity ?? 0 }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="care" role="tabpanel">
                                        <div class="information-box">
                                            <ul>
                                                <li>Tránh để sản phẩm ở nơi ẩm ướt hoặc ánh nắng trực tiếp trong thời gian dài.</li>
                                                <li>Vệ sinh nhẹ nhàng bằng khăn mềm để giữ sản phẩm luôn bền đẹp.</li>
                                                <li>Bảo quản đúng cách để kéo dài tuổi thọ sản phẩm.</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="review" role="tabpanel">
                                        <div class="review-box review-tab-box">
                                            <div class="row g-4">
                                                <div class="col-xl-5">
                                                    <div class="product-rating-box sticky-top">
                                                        <div class="row g-3">
                                                            <div class="col-12">
                                                                <div class="product-main-rating">
                                                                    <h2>
                                                                        {{ number_format($averageRating, 1) }}
                                                                        <i data-feather="star"></i>
                                                                    </h2>
                                                                    <h5>{{ $reviewCount }} đánh giá cho sản phẩm này</h5>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <ul class="product-rating-list">
                                                                    @foreach(range(5, 1) as $star)
                                                                        @php
                                                                            $summary = $ratingSummary[$star] ?? ['count' => 0, 'percent' => 0];
                                                                        @endphp
                                                                        <li>
                                                                            <div class="rating-product">
                                                                                <h5>{{ $star }}<i data-feather="star"></i></h5>
                                                                                <div class="progress">
                                                                                    <div class="progress-bar" style="width: {{ $summary['percent'] }}%;"></div>
                                                                                </div>
                                                                                <h5 class="total">{{ $summary['count'] }}</h5>
                                                                            </div>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>

                                                                <div class="review-title-2 border-0 pb-0">
                                                                    <h4 class="fw-bold">Bình luận từ khách hàng</h4>
                                                                    <p>Chỉ hiển thị các đánh giá đã được gửi sau khi khách mua và nhận hàng.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-7">
                                                    <div class="review-people">
                                                        <ul class="review-list">
                                                            @forelse($reviews as $review)
                                                                @php
                                                                    $avatar = optional($review->user)->avatar;
                                                                    $avatarUrl = $avatar
                                                                        ? (\Illuminate\Support\Str::startsWith($avatar, ['http://', 'https://']) ? $avatar : asset('storage/' . $avatar))
                                                                        : asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/review/1.jpg');
                                                                @endphp
                                                                <li>
                                                                    <div class="people-box">
                                                                        <div>
                                                                            <div class="people-image people-text">
                                                                                <img alt="{{ optional($review->user)->name ?? 'Khách hàng' }}"
                                                                                     class="img-fluid"
                                                                                     src="{{ $avatarUrl }}">
                                                                            </div>
                                                                        </div>
                                                                        <div class="people-comment">
                                                                            <div class="people-name">
                                                                                <a href="javascript:void(0)" class="name">
                                                                                    {{ optional($review->user)->name ?? 'Khách hàng' }}
                                                                                </a>
                                                                                <div class="date-time">
                                                                                    <h6 class="text-content">
                                                                                        {{ optional($review->created_at)->format('d/m/Y H:i') }}
                                                                                    </h6>
                                                                                    <div class="product-rating">
                                                                                        <ul class="rating">
                                                                                            @for($i = 1; $i <= 5; $i++)
                                                                                                <li>
                                                                                                    <i data-feather="star" class="{{ $i <= (int) $review->rating ? 'fill' : '' }}"></i>
                                                                                                </li>
                                                                                            @endfor
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="reply">
                                                                                <p>{{ $review->comment }}</p>
                                                                            </div>

                                                                            @if(!empty($review->admin_reply))
                                                                                <div class="admin-reply-box mt-3">
                                                                                    <h6>Phản hồi từ shop</h6>
                                                                                    <p class="mb-0">{{ $review->admin_reply }}</p>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @empty
                                                                <li>
                                                                    <div class="people-box empty-review-box">
                                                                        <div class="people-comment">
                                                                            <div class="people-name">
                                                                                <a href="javascript:void(0)" class="name">Chưa có bình luận</a>
                                                                            </div>
                                                                            <div class="reply">
                                                                                <p>Sản phẩm này chưa có đánh giá nào. Khi khách mua hàng, nhận hàng và gửi bình luận, nội dung sẽ hiển thị tại đây.</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endforelse
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 d-lg-block d-none wow fadeInUp">
                    <div class="right-sidebar-box">
                        <div class="vendor-box">
                            <div class="vendor-contain">
                                <div class="vendor-image">
                                    <img src="{{ !empty($product->brand?->image) ? asset('storage/' . $product->brand->image) : asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/product/vendor.png') }}"
                                        class="blur-up lazyload vendor-img"
                                        alt="{{ $product->brand->name ?? 'Thương hiệu' }}">
                                </div>

                                <div class="vendor-name">
                                    <h5 class="fw-500">{{ $product->brand->name ?? 'Thương hiệu' }}</h5>
                                </div>
                            </div>
                        </div>

                        <div class="pt-25">
                            <div class="category-menu">
                                <h3>Sản phẩm liên quan</h3>

                                <ul class="product-list product-right-sidebar border-0 p-0">
                                    @forelse($relatedProducts->take(4) as $item)
                                        @php $itemVariant = $item->variants->first(); @endphp
                                        <li>
                                            <div class="offer-product">
                                                <a href="{{ route('client.product.detail', $item->id) }}" class="offer-image">
                                                    <img src="{{ asset('storage/' . $item->image_primary) }}"
                                                         class="img-fluid blur-up lazyload"
                                                         alt="{{ $item->name }}">
                                                </a>

                                                <div class="offer-detail">
                                                    <div>
                                                        <a href="{{ route('client.product.detail', $item->id) }}">
                                                            <h6 class="name">{{ $item->name }}</h6>
                                                        </a>
                                                        <span>{{ $item->category->name ?? 'Sản phẩm' }}</span>
                                                        <h6 class="price theme-color">
                                                            {{ number_format($itemVariant->price ?? 0, 0, ',', '.') }} đ
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <li>
                                            <div class="offer-product">
                                                <div class="offer-detail">
                                                    <div>
                                                        <h6 class="name">Chưa có sản phẩm liên quan</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div class="ratio_156 pt-25"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid-lg related-product-section">
            <div class="title">
                <h2>Sản phẩm liên quan</h2>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="product-border border-row">
                        <div class="slider-6_1 product-wrapper">
                            @forelse($relatedProducts as $item)
                                @php $itemVariant = $item->variants->first(); @endphp
                                <div>
                                    <div class="product-box-3 wow fadeInUp">
                                        <div class="product-header">
                                            <div class="product-image">
                                                <a href="{{ route('client.product.detail', $item->id) }}">
                                                    <img src="{{ asset('storage/' . $item->image_primary) }}"
                                                         class="img-fluid blur-up lazyload"
                                                         alt="{{ $item->name }}">
                                                </a>

                                                <ul class="product-option">
                                                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="Xem">
                                                        <a href="{{ route('client.product.detail', $item->id) }}">
                                                            <i data-feather="eye"></i>
                                                        </a>
                                                    </li>
                                                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="So sánh">
                                                        <a href="compare.html">
                                                            <i data-feather="refresh-cw"></i>
                                                        </a>
                                                    </li>
                                                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="Yêu thích">
                                                        <a href="wishlist.html" class="notifi-wishlist">
                                                            <i data-feather="heart"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="product-footer">
                                            <div class="product-detail">
                                                <span class="span-name">{{ $item->category->name ?? 'Sản phẩm' }}</span>
                                                <a href="{{ route('client.product.detail', $item->id) }}">
                                                    <h5 class="name">{{ $item->name }}</h5>
                                                </a>
                                                <h6 class="unit">Tồn kho: {{ $itemVariant->quantity ?? 0 }}</h6>
                                                <h5 class="price">
                                                    <span class="theme-color">
                                                        {{ number_format($itemVariant->price ?? 0, 0, ',', '.') }} đ
                                                    </span>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div>
                                    <div class="product-box-3">
                                        <div class="product-footer">
                                            <div class="product-detail">
                                                <h5 class="name">Chưa có sản phẩm liên quan</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection
@php
// Chuẩn bị dữ liệu biến thể sản phẩm để JavaScript có thể sử dụng
    $productVariantsJson = $product->variants->map(function ($v) use ($product, $galleryImages) {
        $variantImage = !empty($v->image)
            ? asset('storage/' . $v->image)
            : asset('storage/' . $product->image_primary);

        $slideIndex = collect($galleryImages)->search(fn($img) => $img['src'] === $variantImage);

        return [
            'id' => $v->id,
            'color_id' => $v->id_color,
            'color_name' => optional($v->color)->name,
            'size_id' => $v->id_size,
            'size_name' => optional($v->size)->name,
            'price' => $v->price,
            'quantity' => (int) $v->quantity,
            'image' => $variantImage,
            'slide_index' => $slideIndex !== false ? $slideIndex : 0,
        ];
    })->values()->toArray();
@endphp


@push('styles')
<style>
    .product-tab-wrapper {
        margin-bottom: 42px;
    }

    .related-product-section {
        padding-top: 20px;
    }

    .review-tab-box {
        padding-top: 6px;
    }

    .review-tab-box .product-main-rating h2 {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .review-tab-box .product-main-rating h2 svg {
        width: 22px;
        height: 22px;
    }

    .review-tab-box .product-rating-box {
        border: 1px solid rgba(34, 34, 34, 0.08);
        border-radius: 12px;
        padding: 24px;
        background-color: #fff;
        top: 20px;
    }

    .review-tab-box .review-list {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .review-tab-box .people-box {
        padding: 22px;
        border: 1px solid rgba(34, 34, 34, 0.08);
        border-radius: 12px;
        background-color: #fff;
    }

    .review-tab-box .people-image img {
        width: 58px;
        height: 58px;
        object-fit: cover;
        border-radius: 50%;
    }

    .review-tab-box .people-name .name {
        font-size: 16px;
        font-weight: 600;
    }

    .review-tab-box .date-time {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 14px;
        margin-top: 6px;
    }

    .review-tab-box .reply p {
        margin-bottom: 0;
        color: #4a5568;
        line-height: 1.7;
    }

    .admin-reply-box {
        padding: 14px 16px;
        border-radius: 10px;
        background: #f8f9fa;
        border-left: 3px solid var(--theme-color);
    }

    .admin-reply-box h6 {
        font-weight: 600;
        margin-bottom: 6px;
    }

    .empty-review-box {
        justify-content: center;
    }

    .empty-review-box .people-comment {
        width: 100%;
    }

    @media (max-width: 991px) {
        .product-tab-wrapper {
            margin-bottom: 32px;
        }

        .related-product-section {
            padding-top: 8px;
        }

        .review-tab-box .product-rating-box,
        .review-tab-box .people-box {
            padding: 18px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    window.productVariants = @json($productVariantsJson);
</script>

<script src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/js/slick/slick.js') }}"></script>
<script src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/js/slick/custom_slick.js') }}"></script>
<script src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/js/jquery.elevatezoom.js') }}"></script>
<script src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/js/zoom-filter.js') }}"></script>
<script src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/js/sticky-cart-bottom.js') }}"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {
    const variants = window.productVariants || [];
    // Biến để lưu trạng thái lựa chọn hiện tại
    let selectedColorId = null;
    let selectedSizeId = null;
    let currentVariant = null;

    const priceEl = document.getElementById('product-price');
    const priceTableEl = document.getElementById('product-price-table');
    const stockEl = document.getElementById('product-stock');
    const stockTextEl = document.getElementById('product-stock-text');
    const stockTableEl = document.getElementById('product-quantity-table');
    const skuEl = document.getElementById('product-sku');
    const stockBarEl = document.getElementById('product-stock-bar');
    const qtyInput = document.getElementById('product-quantity-input');
    const selectedVariantLabel = document.getElementById('selected-variant-label');
    const selectedVariantInput = document.getElementById('selected-variant-id');
    const wishlistVariantInput = document.getElementById('wishlist-variant-id');
    const wishlistForm = document.getElementById('wishlist-form');
    const addToCartForm = document.getElementById('add-to-cart-form');
    // Các nút điều chỉnh số lượng
    const btnMinus = document.querySelector('.my-qty-minus');
    const btnPlus = document.querySelector('.my-qty-plus');

    const $mainSlider = $('#product-main-slider');
    const $thumbSlider = $('#variant-thumbs');
    // Hàm định dạng số giá tiền
    function formatPrice(number) {
        return new Intl.NumberFormat('vi-VN').format(number || 0) + ' đ';
    }
    // Hàm lấy tên màu theo ID
    function getColorTextById(colorId) {
        const el = document.querySelector('.color-option[data-color-id="' + colorId + '"]');
        return el ? el.textContent.trim() : 'Chưa chọn màu';
    }
    // Hàm lấy tên kích thước theo ID
    function getSizeTextById(sizeId) {
        const el = document.querySelector('.size-option[data-size-id="' + sizeId + '"]');
        return el ? el.textContent.trim() : 'Chưa chọn kích thước';
    }
    // Hàm cập nhật lựa chọn hiện tại
    function updateSelectedLabel() {
        const colorText = selectedColorId ? getColorTextById(selectedColorId) : 'Chưa chọn màu';
        const sizeText = selectedSizeId ? getSizeTextById(selectedSizeId) : 'Chưa chọn kích thước';

        if (selectedVariantLabel) {
            selectedVariantLabel.textContent = 'Bạn đã chọn: ' + colorText + ' / ' + sizeText;
        }
    }
    // Hàm cập nhật thông tin tồn kho và số lượng
    function updateStockUI(quantity) {
        const qty = Number(quantity || 0);

        if (stockEl) stockEl.textContent = 'Tồn kho: ' + qty;
        if (stockTextEl) stockTextEl.textContent = qty + ' sản phẩm';
        if (stockTableEl) stockTableEl.textContent = qty;

        if (stockBarEl) {
            const width = Math.min(qty, 100);
            stockBarEl.style.width = width + '%';
            stockBarEl.setAttribute('aria-valuenow', width);
        }

        if (qtyInput) {
            qtyInput.setAttribute('max', qty > 0 ? qty : 1);

            let currentQtyValue = parseInt(qtyInput.value || 1, 10);
            if (isNaN(currentQtyValue) || currentQtyValue < 1) currentQtyValue = 1;
            if (qty > 0 && currentQtyValue > qty) currentQtyValue = qty;

            qtyInput.value = currentQtyValue;
        }
    }
    // Hàm tìm biến thể khớp chính xác với lựa chọn hiện tại
    function findExactVariant() {
        if (!selectedColorId || !selectedSizeId) return null;

        return variants.find(v =>
            String(v.color_id) === String(selectedColorId) &&
            String(v.size_id) === String(selectedSizeId) &&
            Number(v.quantity) > 0
        ) || null;
    }
    // Hàm chuyển slider đến biến thể tương ứng
    function slideToVariant(variant) {
        if (!variant) return;

        const slideIndex = Number(variant.slide_index || 0);

        if ($mainSlider.length && $mainSlider.hasClass('slick-initialized')) {
            $mainSlider.slick('slickGoTo', slideIndex);
        }

        if ($thumbSlider.length && $thumbSlider.hasClass('slick-initialized')) {
            $thumbSlider.slick('slickGoTo', slideIndex);
        }
    }
    // Hàm cập nhật thông tin khi có biến thể được chọn
    function updateVariantUI(variant) {
        if (!variant) return;

        currentVariant = variant;

        if (selectedVariantInput) {
            selectedVariantInput.value = variant.id;
        }

        if (priceEl) priceEl.textContent = formatPrice(variant.price);
        if (priceTableEl) priceTableEl.textContent = formatPrice(variant.price);
        if (skuEl) skuEl.textContent = variant.sku || '';
        updateStockUI(variant.quantity);
        slideToVariant(variant);
        if (wishlistVariantInput) {
            wishlistVariantInput.value = variant.id;
        }
    }
    // Hàm reset thông tin khi không có biến thể nào khớp
    function resetVariantUI() {
        currentVariant = null;

        if (selectedVariantInput) {
            selectedVariantInput.value = '';
        }

        if (qtyInput) {
            qtyInput.value = 1;
            qtyInput.setAttribute('max', 1);
        }
        if (wishlistVariantInput) {
            wishlistVariantInput.value = '';
        }
    }
    // Hàm hiển thị nút màu ,size đã chọn
    function renderSelectedState() {
        document.querySelectorAll('.color-option').forEach(el => {
            const isSelected = String(el.dataset.colorId) === String(selectedColorId);
            el.classList.toggle('active', isSelected);
            el.classList.toggle('is-selected', isSelected);
            el.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });

        document.querySelectorAll('.size-option').forEach(el => {
            const isSelected = String(el.dataset.sizeId) === String(selectedSizeId);
            el.classList.toggle('active', isSelected);
            el.classList.toggle('is-selected', isSelected);
            el.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });
    }
    // Hàm để khóa các option không hợp lệ.
    function refreshAvailableOptions() {
    document.querySelectorAll('.size-option').forEach(el => {
        const sizeId = el.dataset.sizeId;

        const exists = variants.some(v =>
            String(v.size_id) === String(sizeId) &&
            Number(v.quantity) > 0 &&
            (!selectedColorId || String(v.color_id) === String(selectedColorId))
        );

        el.classList.toggle('disabled', !exists);
        el.style.pointerEvents = exists ? 'auto' : 'none';
        el.style.opacity = exists ? '1' : '0.45';
    });
    // Nếu đã chọn kích thước, chỉ hiển thị màu có biến thể tương ứng với kích thước đó
    document.querySelectorAll('.color-option').forEach(el => {
        const colorId = el.dataset.colorId;

        const exists = variants.some(v =>
            String(v.color_id) === String(colorId) &&
            Number(v.quantity) > 0
        );

        el.classList.toggle('disabled', !exists);
        el.style.pointerEvents = exists ? 'auto' : 'none';
        el.style.opacity = exists ? '1' : '0.45';
    });
    }
     // Hàm đồng bộ giao diện sau khi có thay đổi lựa chọn
    function syncUI() {
        refreshAvailableOptions();
        renderSelectedState();
        updateSelectedLabel();

        const matched = findExactVariant();

        if (matched) {
            updateVariantUI(matched);
        } else {
            resetVariantUI();
        }
    }
    // Xử lý sự kiện khi người dùng chọn màu
    document.querySelectorAll('.color-option').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();

            if (this.classList.contains('disabled')) return;

            selectedColorId = this.dataset.colorId;

            const stillValid = variants.some(v =>
                String(v.color_id) === String(selectedColorId) &&
                String(v.size_id) === String(selectedSizeId) &&
                Number(v.quantity) > 0
            );

            if (!stillValid) {
                selectedSizeId = null;
            }

            syncUI();
        });
    });
    // Xử lý sự kiện khi người dùng chọn kích thước
    document.querySelectorAll('.size-option').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();

            if (this.classList.contains('disabled')) return;

            selectedSizeId = this.dataset.sizeId;

            const stillValid = variants.some(v =>
                String(v.color_id) === String(selectedColorId) &&
                String(v.size_id) === String(selectedSizeId) &&
                Number(v.quantity) > 0
            );

            if (!stillValid) {
                selectedColorId = null;
            }

            syncUI();
        });
    });
    // Xử lý sự kiện nút tăng giảm số lượng
    btnMinus?.addEventListener('click', function (e) {
        e.preventDefault();

        if (!qtyInput) return;

        let value = parseInt(qtyInput.value || 1, 10);
        if (isNaN(value) || value <= 1) value = 1;
        else value -= 1;

        qtyInput.value = value;
    });
    // Xử lý sự kiện nút tăng số lượng
    btnPlus?.addEventListener('click', function (e) {
        e.preventDefault();

        if (!qtyInput) return;

        let value = parseInt(qtyInput.value || 1, 10);
        let max = parseInt(qtyInput.getAttribute('max') || 1, 10);

        if (isNaN(value) || value < 1) value = 1;
        if (isNaN(max) || max < 1) max = 1;

        if (value < max) {
            value += 1;
        }

        qtyInput.value = value;
    });
    // Xử lý sự kiện khi người dùng nhập số lượng trực tiếp
    qtyInput?.addEventListener('input', function () {
        let value = parseInt(this.value || 1, 10);
        let max = parseInt(this.getAttribute('max') || 1, 10);

        if (isNaN(value) || value < 1) value = 1;
        if (isNaN(max) || max < 1) max = 1;
        if (value > max) value = max;

        this.value = value;
    });
    // Xử lý sự kiện khi người dùng submit form thêm vào giỏ hàng
    addToCartForm?.addEventListener('submit', function (e) {
        if (!selectedVariantInput || !selectedVariantInput.value) {
            e.preventDefault();
            alert('Vui lòng chọn màu và kích thước.');
            return;
        }

        const qty = parseInt(qtyInput?.value || 1, 10);
        const max = parseInt(qtyInput?.getAttribute('max') || 1, 10);

        if (isNaN(qty) || qty < 1) {
            e.preventDefault();
            alert('Số lượng không hợp lệ.');
            return;
        }

        if (qty > max) {
            e.preventDefault();
            alert('Số lượng vượt quá tồn kho.');
        }
    });
    // Xử lý sự kiện khi người dùng submit form thêm vào yêu thích
    wishlistForm?.addEventListener('submit', function (e) {
        if (!wishlistVariantInput || !wishlistVariantInput.value) {
            e.preventDefault();
            alert('Vui lòng chọn màu và kích thước trước khi thêm vào yêu thích.');
        }
    });

    // KHÔNG auto chọn sẵn cả màu + size
    updateSelectedLabel();
    refreshAvailableOptions();
    renderSelectedState();
});
</script>
@endpush

@push('styles')
    <style>
        .product-main-2:not(.slick-initialized) > div,
        .left-slider-image-2:not(.slick-initialized) > div,
        .slider-6_1:not(.slick-initialized) > div {
            display: block !important;
        }

        .product-main-2 .slider-image img,
        .left-slider-image-2 .sidebar-image img,
        .slider-6_1 .product-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .product-main-2 .slider-image {
            min-height: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }

        .left-slider-image-2 .sidebar-image {
            border: 1px solid #eee;
            padding: 6px;
            background: #fff;
        }

        .right-box-contain {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .product-section .wow {
            visibility: visible !important;
            animation: none !important;
        }
        .color-option.disabled,
        .size-option.disabled {
            cursor: not-allowed;
        }
    </style>
@endpush
