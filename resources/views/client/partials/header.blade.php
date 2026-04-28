<!-- Header Start -->
<header class="client-header-fixed">
    <style>
        .client-header-fixed {
            background: #fff;
            position: relative;
            z-index: 1000;
        }

        .client-header-fixed,
        .client-header-fixed * {
            box-sizing: border-box;
        }

        .client-header-fixed .header-top {
            display: none;
        }

        .client-header-fixed .top-header,
        .client-header-fixed .top-nav {
            background: #fff;
            border: 0;
        }

        .client-header-fixed .container-fluid-lg {
            max-width: 1600px;
            padding-left: 28px;
            padding-right: 28px;
        }

        .client-header-fixed .navbar-top {
            min-height: 92px;
            display: flex;
            align-items: center;
            gap: 28px;
            width: 100%;
        }

        .client-header-fixed .navbar-menu-button {
            flex: 0 0 auto;
        }

        .client-header-fixed .web-logo {
            flex: 0 0 auto;
            width: 170px;
            max-width: 170px;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .client-header-fixed .web-logo img {
            max-width: 145px;
            height: auto;
            display: block;
        }

        .client-header-fixed .header-nav-middle {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .client-header-fixed .main-nav,
        .client-header-fixed .navbar,
        .client-header-fixed .navbar-sticky {
            width: 100%;
            padding: 0;
            margin: 0;
        }

        .client-header-fixed .offcanvas-body {
            padding: 0;
        }

        .client-header-fixed .navbar-nav {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: nowrap;
            gap: clamp(18px, 2.2vw, 42px);
            margin: 0;
            padding: 0;
        }

        .client-header-fixed .navbar-nav .nav-item {
            flex: 0 0 auto;
            margin: 0;
            padding: 0;
        }

        .client-header-fixed .navbar-nav .nav-link {
            color: #222;
            font-size: clamp(15px, 1.2vw, 20px);
            font-weight: 500;
            line-height: 1.25;
            white-space: nowrap;
            padding: 8px 0 !important;
            text-decoration: none;
        }

        .client-header-fixed .navbar-nav .nav-link:hover {
            color: var(--theme-color, #ff6b35);
        }

        .client-header-fixed .dropdown-menu {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.14);
            padding: 10px;
        }

        .client-header-fixed .dropdown-item {
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 14px;
        }

        .client-header-fixed .dropdown-item:hover {
            background: #fff3ed;
            color: var(--theme-color, #ff6b35);
        }

        .client-header-fixed .rightside-box {
            flex: 0 0 auto;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .client-header-fixed .right-side-menu {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
            flex-wrap: nowrap !important;
        }

        .client-header-fixed .right-side-menu > li,
        .client-header-fixed .right-side {
            display: flex !important;
            align-items: center !important;
            position: relative;
            margin: 0 !important;
            padding: 0 !important;
            flex: 0 0 auto;
        }

        .client-header-fixed .right-side::before,
        .client-header-fixed .right-side::after,
        .client-header-fixed .right-side-menu > li::before,
        .client-header-fixed .right-side-menu > li::after {
            display: none !important;
            content: none !important;
        }

        .client-header-fixed .header-divider {
            width: 1px;
            height: 26px;
            display: inline-block;
            background: #cbd5e1;
        }

        .client-header-fixed .header-icon-btn {
            width: 44px;
            height: 44px;
            border: 0;
            outline: 0;
            background: #f8f8f8;
            color: #222;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            cursor: pointer;
            text-decoration: none;
            position: relative;
            transition: all 0.2s ease;
        }

        .client-header-fixed .header-icon-btn:hover,
        .client-header-fixed .header-icon-btn.active {
            background: var(--theme-color, #ff6b35);
            color: #fff;
        }

        .client-header-fixed .header-icon-btn svg {
            width: 23px;
            height: 23px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: block;
        }

        .client-header-fixed .cart-count-badge {
            position: absolute;
            top: -8px;
            right: -6px;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            background: #2c6e9f;
            color: #fff;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .client-header-fixed .header-search-popover {
            position: absolute;
            top: calc(100% + 16px);
            right: 0;
            width: min(500px, calc(100vw - 32px));
            background: #fff;
            border: 1px solid #eef2f7;
            border-radius: 20px;
            box-shadow: 0 18px 46px rgba(15, 23, 42, 0.18);
            padding: 14px;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.2s ease;
        }

        .client-header-fixed .header-search-popover.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .client-header-fixed .header-search-popover::before {
            content: "";
            position: absolute;
            top: -8px;
            right: 170px;
            width: 16px;
            height: 16px;
            background: #fff;
            border-left: 1px solid #eef2f7;
            border-top: 1px solid #eef2f7;
            transform: rotate(45deg);
        }

        .client-header-fixed .header-search-form {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .client-header-fixed .header-search-input-wrap {
            position: relative;
            flex: 1;
            min-width: 0;
        }

        .client-header-fixed .header-search-input-wrap svg {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #94a3b8;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .client-header-fixed .header-search-input {
            width: 100%;
            height: 46px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            padding: 0 16px 0 44px;
            color: #334155;
            font-size: 14px;
            outline: none;
            transition: all 0.2s ease;
        }

        .client-header-fixed .header-search-input:focus {
            border-color: var(--theme-color, #ff6b35);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        .client-header-fixed .header-search-submit {
            height: 46px;
            min-width: 96px;
            border: 0;
            border-radius: 999px;
            background: var(--theme-color, #ff6b35);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            padding: 0 18px;
            cursor: pointer;
        }

        .client-header-fixed .header-search-submit:hover {
            background: #f15c25;
        }

        .client-header-fixed .header-search-close {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            flex: 0 0 36px;
            cursor: pointer;
        }

        .client-header-fixed .header-search-close:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .client-header-fixed .header-search-close svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .client-header-fixed .account-dropdown .delivery-login-box {
            display: inline-flex;
            align-items: center;
            gap: 0;
            margin: 0;
            padding: 0;
        }

        .client-header-fixed .account-dropdown .delivery-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #222;
        }

        .client-header-fixed .account-dropdown .delivery-icon svg {
            width: 25px;
            height: 25px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .client-header-fixed .account-dropdown .delivery-detail {
            display: none !important;
        }

        .client-header-fixed .onhover-div {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 18px 46px rgba(15, 23, 42, 0.16);
        }

        @media (max-width: 1399.98px) {
            .client-header-fixed .navbar-top {
                gap: 22px;
            }

            .client-header-fixed .web-logo {
                width: 150px;
                max-width: 150px;
            }

            .client-header-fixed .web-logo img {
                max-width: 130px;
            }

            .client-header-fixed .navbar-nav {
                gap: clamp(14px, 1.8vw, 30px);
            }

            .client-header-fixed .navbar-nav .nav-link {
                font-size: clamp(14px, 1.1vw, 18px);
            }

            .client-header-fixed .right-side-menu {
                gap: 13px !important;
            }

            .client-header-fixed .header-icon-btn,
            .client-header-fixed .account-dropdown .delivery-icon {
                width: 40px;
                height: 40px;
            }
        }

        @media (max-width: 1199.98px) {
            .client-header-fixed .navbar-top {
                min-height: 78px;
            }

            .client-header-fixed .header-nav-middle {
                justify-content: flex-start;
            }

            .client-header-fixed .offcanvas-body {
                padding: 18px;
            }

            .client-header-fixed .navbar-nav {
                align-items: flex-start;
                justify-content: flex-start;
                flex-direction: column;
                flex-wrap: nowrap;
                gap: 0;
            }

            .client-header-fixed .navbar-nav .nav-link {
                font-size: 16px;
                padding: 10px 0 !important;
            }
        }

        @media (max-width: 575.98px) {
            .client-header-fixed .container-fluid-lg {
                padding-left: 14px;
                padding-right: 14px;
            }

            .client-header-fixed .navbar-top {
                gap: 10px;
            }

            .client-header-fixed .web-logo {
                width: 118px;
                max-width: 118px;
            }

            .client-header-fixed .web-logo img {
                max-width: 108px;
            }

            .client-header-fixed .right-side-menu {
                gap: 8px !important;
            }

            .client-header-fixed .header-divider {
                display: none;
            }

            .client-header-fixed .header-icon-btn,
            .client-header-fixed .account-dropdown .delivery-icon {
                width: 34px;
                height: 34px;
            }

            .client-header-fixed .header-icon-btn svg,
            .client-header-fixed .account-dropdown .delivery-icon svg {
                width: 19px;
                height: 19px;
            }

            .client-header-fixed .header-search-popover {
                position: fixed;
                top: 76px;
                left: 14px;
                right: 14px;
                width: auto;
            }

            .client-header-fixed .header-search-popover::before {
                display: none;
            }

            .client-header-fixed .header-search-form {
                gap: 8px;
            }

            .client-header-fixed .header-search-submit {
                min-width: 74px;
                padding: 0 12px;
                font-size: 13px;
            }
        }
    </style>

    <div class="top-nav top-header sticky-header">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="navbar-top">
                        <button class="navbar-toggler d-xl-none d-inline navbar-menu-button"
                                type="button"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#primaryMenu">
                        </button>

                        <a href="{{ route('home') }}" class="web-logo nav-logo">
                            <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/logo/6.png') }}"
                                 class="img-fluid blur-up lazyload"
                                 alt="Threadly">
                        </a>

                        <div class="header-nav-middle">
                            <div class="main-nav navbar navbar-expand-xl navbar-light navbar-sticky">
                                <div class="offcanvas offcanvas-collapse order-xl-2" id="primaryMenu">
                                    <div class="offcanvas-header navbar-shadow">
                                        <h5>Menu</h5>
                                        <button class="btn-close lead"
                                                type="button"
                                                data-bs-dismiss="offcanvas"></button>
                                    </div>

                                    <div class="offcanvas-body">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link ps-xl-2 ps-0" href="{{ route('home') }}">
                                                    Trang chủ
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link ps-xl-2 ps-0" href="{{ route('contact.index') }}">
                                                    Liên hệ
                                                </a>
                                            </li>

                                            @php
                                                $renderHeaderCategoryHtml = function ($category, $level = 0) use (&$renderHeaderCategoryHtml) {
                                                    $children = $category->childrenRecursive ?? collect();
                                                    $hasChildren = $children->count() > 0;

                                                    $url = route('client.category', $category->id);
                                                    $name = e($category->name);

                                                    if ($level === 0) {
                                                        $liClass = $hasChildren ? 'nav-item dropdown' : 'nav-item';
                                                        $aClass = 'nav-link';
                                                        $toggle = $hasChildren ? ' data-bs-toggle="dropdown"' : '';

                                                        $html = '<li class="' . $liClass . '">';
                                                        $html .= '<a class="' . $aClass . '" href="' . $url . '"' . $toggle . '>' . $name . '</a>';

                                                        if ($hasChildren) {
                                                            $html .= '<ul class="dropdown-menu">';

                                                            foreach ($children as $child) {
                                                                $html .= $renderHeaderCategoryHtml($child, $level + 1);
                                                            }

                                                            $html .= '</ul>';
                                                        }

                                                        $html .= '</li>';

                                                        return $html;
                                                    }

                                                    if ($hasChildren) {
                                                        $html = '<li class="sub-dropdown-hover">';
                                                        $html .= '<a class="dropdown-item" href="' . $url . '">' . $name . '</a>';
                                                        $html .= '<ul class="sub-menu">';

                                                        foreach ($children as $child) {
                                                            $html .= $renderHeaderCategoryHtml($child, $level + 1);
                                                        }

                                                        $html .= '</ul>';
                                                        $html .= '</li>';

                                                        return $html;
                                                    }

                                                    return '<li><a class="dropdown-item" href="' . $url . '">' . $name . '</a></li>';
                                                };

                                                $headerMenuHtml = '';

                                                if (!empty($headerCategories) && $headerCategories->count() > 0) {
                                                    foreach ($headerCategories as $category) {
                                                        $headerMenuHtml .= $renderHeaderCategoryHtml($category, 0);
                                                    }
                                                }

                                                $searchAction = \Illuminate\Support\Facades\Route::has('client.products.search')
                                                    ? route('client.products.search')
                                                    : url('/tim-kiem');

                                                $cartCount = 0;
                                                $cartTotal = 0;
                                                $cartItems = collect();

                                                if (auth()->check()) {
                                                    $cartModel = \App\Models\Cart::with(['details.variant.product'])
                                                        ->where('id_user', auth()->id())
                                                        ->first();

                                                    if ($cartModel) {
                                                        $cartItems = $cartModel->details;

                                                        foreach ($cartItems as $item) {
                                                            $qty = $item->quantity ?? 1;
                                                            $variant = $item->variant;
                                                            $price = $variant->price_sale ?? ($variant->price ?? 0);

                                                            $cartCount += $qty;
                                                            $cartTotal += $price * $qty;
                                                        }
                                                    }
                                                }
                                            @endphp

                                            {!! $headerMenuHtml !!}

                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ route('client.about') }}">
                                                    Về chúng tôi
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rightside-box">
                            <ul class="right-side-menu">
                                <li class="right-side">
                                    <button type="button"
                                            class="header-icon-btn js-header-search-toggle"
                                            aria-label="Tìm kiếm sản phẩm">
                                        <svg viewBox="0 0 24 24">
                                            <circle cx="11" cy="11" r="7"></circle>
                                            <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
                                        </svg>
                                    </button>
                                </li>

                                <li class="right-side">
                                    <a href="{{ route('client.wishlist.index') }}"
                                       class="header-icon-btn"
                                       aria-label="Yêu thích">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                    </a>
                                </li>

                                <li class="right-side">
                                    <span class="header-divider"></span>
                                </li>

                                <li class="right-side">
                                    <div class="onhover-dropdown header-badge">
                                        <button type="button"
                                                class="header-icon-btn"
                                                aria-label="Giỏ hàng">
                                            <svg viewBox="0 0 24 24">
                                                <circle cx="9" cy="21" r="1"></circle>
                                                <circle cx="20" cy="21" r="1"></circle>
                                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                            </svg>

                                            <span class="cart-count-badge">
                                                {{ $cartCount }}
                                            </span>
                                        </button>

                                        <div class="onhover-div">
                                            @if($cartItems->count() > 0)
                                                <ul class="cart-list">
                                                    @foreach($cartItems as $item)
                                                        @php
                                                            $variant = $item->variant;
                                                            $product = $variant->product ?? null;

                                                            $qty = $item->quantity ?? 1;
                                                            $price = $variant->price_sale ?? ($variant->price ?? 0);

                                                            $rawImage = $variant->image ?? $product->image ?? null;

                                                            if ($rawImage) {
                                                                $image = filter_var($rawImage, FILTER_VALIDATE_URL)
                                                                    ? $rawImage
                                                                    : asset('storage/' . ltrim($rawImage, '/'));
                                                            } else {
                                                                $image = asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/placeholder.png');
                                                            }

                                                            $name = $product->name ?? 'Sản phẩm';
                                                            $productId = $product->id ?? 0;
                                                            $variantName = $variant->name ?? null;
                                                        @endphp

                                                        <li class="product-box-contain">
                                                            <div class="drop-cart">
                                                                <a href="{{ route('client.product.detail', $productId) }}"
                                                                   class="drop-image">
                                                                    <img src="{{ $image }}"
                                                                         class="blur-up lazyload"
                                                                         alt="{{ $name }}">
                                                                </a>

                                                                <div class="drop-contain">
                                                                    <a href="{{ route('client.product.detail', $productId) }}">
                                                                        <h5>
                                                                            {{ $name }}
                                                                            @if($variantName)
                                                                                <br><small>{{ $variantName }}</small>
                                                                            @endif
                                                                        </h5>
                                                                    </a>

                                                                    <h6>
                                                                        <span>{{ $qty }} x</span>
                                                                        {{ number_format($price, 0, ',', '.') }}đ
                                                                    </h6>

                                                                    <form action="{{ route('client.cart.remove', $item->id) }}" method="POST">
                                                                        @csrf
                                                                        @method('DELETE')

                                                                        <button type="submit"
                                                                                class="close-button close_button border-0 bg-transparent">
                                                                            <i class="fa-solid fa-xmark"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                <div class="price-box">
                                                    <h5>Tổng cộng :</h5>
                                                    <h4 class="theme-color fw-bold">
                                                        {{ number_format($cartTotal, 0, ',', '.') }}đ
                                                    </h4>
                                                </div>

                                                <div class="button-group">
                                                    <a href="{{ route('client.cart.index') }}"
                                                       class="btn btn-sm cart-button">
                                                        Xem giỏ hàng
                                                    </a>
                                                </div>
                                            @else
                                                <div class="p-3 text-center">
                                                    <p class="mb-2">Giỏ hàng đang trống</p>

                                                    <a href="{{ route('client.cart.index') }}"
                                                       class="btn btn-sm cart-button">
                                                        Vào giỏ hàng
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>

                                <li class="right-side">
                                    <span class="header-divider"></span>
                                </li>

                                <li class="right-side onhover-dropdown account-dropdown">
                                    <div class="delivery-login-box">
                                        <div class="delivery-icon">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </div>

                                        <div class="delivery-detail">
                                            @auth
                                                <h6>Xin chào,</h6>
                                                <h5>{{ auth()->user()->name }}</h5>
                                            @else
                                                <h6>Xin chào,</h6>
                                                <h5>Tài khoản của tôi</h5>
                                            @endauth
                                        </div>
                                    </div>

                                    <div class="onhover-div onhover-div-login">
                                        <ul class="user-box-name">
                                            @guest
                                                <li class="product-box-contain">
                                                    <a href="{{ route('login', ['redirect' => url()->current()]) }}">
                                                        Đăng nhập
                                                    </a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <a href="{{ route('register') }}">
                                                        Đăng ký
                                                    </a>
                                                </li>
                                            @endguest

                                            @auth
                                                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                                    <li class="product-box-contain">
                                                        <a href="{{ route('admin.homeAdmin') }}">
                                                            Trang quản trị
                                                        </a>
                                                    </li>
                                                @endif

                                                <li class="product-box-contain">
                                                    <a href="{{ route('client.account.index') }}">
                                                        Tài khoản của tôi
                                                    </a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <a href="{{ route('client.cart.index') }}">
                                                        Giỏ hàng
                                                    </a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <a href="{{ route('password.change') }}">
                                                        Đặt lại mật khẩu
                                                    </a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                                                        @csrf

                                                        <button type="submit"
                                                                style="background:none;border:none;padding:0;color:inherit;cursor:pointer;">
                                                            Đăng xuất
                                                        </button>
                                                    </form>
                                                </li>
                                            @endauth
                                        </ul>
                                    </div>
                                </li>
                            </ul>

                            <div class="header-search-popover js-header-search-popover">
                                <form action="{{ $searchAction }}"
                                      method="GET"
                                      class="header-search-form">
                                    <div class="header-search-input-wrap">
                                        <svg viewBox="0 0 24 24">
                                            <circle cx="11" cy="11" r="7"></circle>
                                            <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
                                        </svg>

                                        <input type="search"
                                               name="q"
                                               value="{{ request('q') }}"
                                               class="header-search-input js-header-search-input"
                                               placeholder="Tìm kiếm sản phẩm..."
                                               autocomplete="off">
                                    </div>

                                    <button type="submit" class="header-search-submit">
                                        Tìm kiếm
                                    </button>

                                    <button type="button"
                                            class="header-search-close js-header-search-close"
                                            aria-label="Đóng tìm kiếm">
                                        <svg viewBox="0 0 24 24">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @once
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toggle = document.querySelector('.js-header-search-toggle');
                const popover = document.querySelector('.js-header-search-popover');
                const close = document.querySelector('.js-header-search-close');
                const input = document.querySelector('.js-header-search-input');

                if (!toggle || !popover) {
                    return;
                }

                function openSearch() {
                    popover.classList.add('show');
                    toggle.classList.add('active');

                    setTimeout(function () {
                        if (input) {
                            input.focus();
                        }
                    }, 80);
                }

                function closeSearch() {
                    popover.classList.remove('show');
                    toggle.classList.remove('active');
                }

                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (popover.classList.contains('show')) {
                        closeSearch();
                    } else {
                        openSearch();
                    }
                });

                if (close) {
                    close.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        closeSearch();
                    });
                }

                popover.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                document.addEventListener('click', function () {
                    closeSearch();
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeSearch();
                    }
                });
            });
        </script>
    @endonce
</header>
<!-- Header End -->
