<!-- Header Start -->
<header class="">
    <div class="header-top">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-xxl-3 d-xxl-block d-none">
                    <div class="top-left-header">


                    </div>
                </div>
                <div class="col-lg-3">

                </div>
            </div>
        </div>
    </div>

    <div class="top-nav top-header sticky-header">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="navbar-top">
                        <button class="navbar-toggler d-xl-none d-inline navbar-menu-button me-2" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#primaryMenu">
                            <span class="navbar-toggler-icon">
                                <i class="fa-solid fa-bars"></i>
                            </span>
                        </button>
                        <a href="index.html" class="web-logo nav-logo">
                            <img src="{{ asset('client/theme/themes.pixelstrap.com/fastkart/assets/images/logo/6.png') }}" class="img-fluid blur-up lazyload" alt="">
                        </a>

                        <div class="header-nav-middle">
                            <div class="main-nav navbar navbar-expand-xl navbar-light navbar-sticky">
                                <div class="offcanvas offcanvas-collapse order-xl-2" id="primaryMenu">
                                    <div class="offcanvas-header navbar-shadow">
                                        <h5>Menu</h5>
                                        <button class="btn-close lead" type="button"
                                            data-bs-dismiss="offcanvas"></button>
                                    </div>
                                    <div class="offcanvas-body">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link ps-xl-2 ps-0" href="{{ route('home') }}">Trang chủ</a>
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
                                            @endphp

                                            {!! $headerMenuHtml !!}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rightside-box">
                            <div class="search-full">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i data-feather="search" class="font-light"></i>
                                    </span>
                                    <input type="text" class="form-control search-type" placeholder="Tìm kiếm tại đây...">
                                    <span class="input-group-text close-search">
                                        <i data-feather="x" class="font-light"></i>
                                    </span>
                                </div>
                            </div>
                            <ul class="right-side-menu">
                                <li class="right-side">
                                    <div class="delivery-login-box">
                                        <div class="delivery-icon">
                                            <div class="search-box">
                                                <i data-feather="search"></i>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="right-side">
                                    <a href="wishlist.html" class="btn p-0 position-relative header-wishlist">
                                        <i data-feather="bookmark"></i>
                                    </a>
                                </li>
                                <li class="right-side">
                                    @php
                                        use Illuminate\Support\Facades\Auth;
                                        use App\Models\Cart;

                                        $cartCount = 0;
                                        $cartTotal = 0;
                                        $cartItems = collect();

                                        if (Auth::check()) {
                                            $cartModel = Cart::with(['details.variant.product'])
                                                ->where('id_user', Auth::id())
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

                                        <li class="right-side">
                                            <div class="onhover-dropdown header-badge">
                                                <button type="button" class="btn p-0 position-relative header-wishlist">
                                                    <i data-feather="shopping-cart"></i>

                                                    <span class="position-absolute top-0 start-100 translate-middle badge">
                                                        {{ $cartCount }}
                                                        <span class="visually-hidden">sản phẩm trong giỏ hàng</span>
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
                                                                        <a href="{{ route('client.product.detail', $productId) }}" class="drop-image">
                                                                            <img src="{{ $image }}" class="blur-up lazyload" alt="{{ $name }}">
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
                                                                                <button type="submit" class="close-button close_button border-0 bg-transparent">
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
                                                            <h4 class="theme-color fw-bold">{{ number_format($cartTotal, 0, ',', '.') }}đ</h4>
                                                        </div>

                                                        <div class="button-group">
                                                            <a href="{{ route('client.cart.index') }}" class="btn btn-sm cart-button">Xem giỏ hàng</a>
                                                            <a href="#" class="btn btn-sm cart-button theme-bg-color text-white">
                                                                Thanh toán
                                                            </a>
                                                        </div>
                                                    @else
                                                        <div class="p-3 text-center">
                                                            <p class="mb-2">Giỏ hàng đang trống</p>
                                                            <a href="{{ route('client.cart.index') }}" class="btn btn-sm cart-button">
                                                                Vào giỏ hàng
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                </li>
                                <li class="right-side onhover-dropdown">
                                    <div class="delivery-login-box">
                                        <div class="delivery-icon">
                                            <i data-feather="user"></i>
                                        </div>
                                        <div class="delivery-detail">
                                            @auth
                                                <h6>Xin chào,</h6>
                                                <h5>{{ Auth::user()->name }}</h5>
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
                                                    <a href="{{ route('login', ['redirect' => url()->current()]) }}">Đăng nhập</a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <a href="{{ route('register') }}">Đăng ký</a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <a href="#">Quên mật khẩu</a>
                                                </li>
                                            @endguest

                                            @auth
                                                @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                                                    <li class="product-box-contain">
                                                        <a href="{{ route('admin.homeAdmin') }}">Trang quản trị</a>
                                                    </li>
                                                @endif

                                                <li class="product-box-contain">
                                                    <a href="{{ route('client.account.index') }}">Tài khoản của tôi</a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <a href="{{ route('client.cart.index') }}">Giỏ hàng</a>
                                                </li>
                                                <li class="product-box-contain">
                                                    <a href="{{ route('password.change') }}">Đặt lại mật khẩu</a>
                                                </li>

                                                <li class="product-box-contain">
                                                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" style="background:none;border:none;padding:0;color:inherit;cursor:pointer;">
                                                            Đăng xuất
                                                        </button>
                                                    </form>
                                                </li>
                                            @endauth
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Header End -->
