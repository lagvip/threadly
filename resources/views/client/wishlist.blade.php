@extends('client.layouts.master')

@section('content')
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-12">
                <div class="breadcrumb-contain">
                    <h2>Yêu thích</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">
                                    <i class="fa-solid fa-house"></i>
                                </a>
                            </li>
           
           
           
           
           
           
           
           
           
           
           
           
           
           
           
           
           
           
           
           
           
                            <li class="breadcrumb-item active">Yêu thích</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="wishlist-section section-b-space">
    <div class="container-fluid-lg">
        <div class="row g-sm-3 g-2">
            @forelse($wishlists as $item)
                @php
                    $variant = $item->variant;
                    $product = $variant->product;
                    $image = $variant->image
                        ? asset('storage/' . $variant->image)
                        : asset('storage/' . $product->image_primary);
                @endphp

                <div class="col-xxl-2 col-lg-3 col-md-4 col-6 product-box-contain">
                    <div class="product-box-3 h-100">
                        <div class="product-header">
                            <div class="product-image">
                                <a href="{{ route('client.product.detail', $product->id) }}">
                                    <img src="{{ $image }}" class="img-fluid blur-up lazyload" alt="{{ $product->name }}">
                                </a>

                                <div class="product-header-top">
                                    <form action="{{ route('client.wishlist.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn wishlist-button" type="submit">
                                            <i data-feather="x"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="product-footer">
                            <div class="product-detail">
                                <span class="span-name">{{ $product->category->name ?? 'Sản phẩm' }}</span>

                                <a href="{{ route('client.product.detail', $product->id) }}">
                                    <h5 class="name">{{ $product->name }}</h5>
                                </a>

                                <h6 class="unit mt-1">
                                    {{ $variant->color->name ?? 'Không có' }} / {{ $variant->size->name ?? 'Không có' }}
                                </h6>

                                <h5 class="price">
                                    <span class="theme-color">
                                        {{ number_format($variant->price ?? 0, 0, ',', '.') }} đ
                                    </span>
                                </h5>

                                <div class="add-to-cart-box bg-white mt-2">
                                    <form action="{{ route('client.cart.add') }}" method="POST" class="w-100">
                                        @csrf
                                        <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                                        <input type="hidden" name="quantity" value="1">

                                        <button class="btn btn-add-cart addcart-button w-100" type="submit">
                                            Thêm vào giỏ hàng
                                            <span class="add-icon bg-light-gray">
                                                <i class="fa-solid fa-plus"></i>
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light text-center">
                        Bạn chưa có sản phẩm yêu thích nào.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
