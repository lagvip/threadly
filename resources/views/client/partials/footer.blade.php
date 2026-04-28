<footer class="section-t-space footer-section-2 footer-color-3 threadly-footer">
    @php
        $footerCategories = collect();

        if (isset($headerCategories) && $headerCategories->count() > 0) {
            $footerCategories = $headerCategories->take(6);
        } elseif (isset($categories) && $categories->count() > 0) {
            $footerCategories = $categories->take(6);
        } else {
            $footerCategories = \App\Models\Category::query()
                ->whereNull('deleted_at')
                ->orderBy('id', 'asc')
                ->take(6)
                ->get();
        }
    @endphp

    <style>
        .threadly-footer {
            background: linear-gradient(90deg, #17212b 0%, #111926 100%);
            color: #fff;
            padding-top: 70px;
        }

        .threadly-footer .main-footer {
            padding-bottom: 36px;
        }

        .threadly-footer .footer-col {
            min-height: 100%;
        }

        .threadly-footer .footer-title h4 {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 28px;
        }

        .threadly-footer .footer-list,
        .threadly-footer .footer-address {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .threadly-footer .footer-list li,
        .threadly-footer .footer-address li {
            margin-bottom: 18px;
        }

        .threadly-footer .footer-list li:last-child,
        .threadly-footer .footer-address li:last-child {
            margin-bottom: 0;
        }

        .threadly-footer .footer-list a,
        .threadly-footer .footer-address a,
        .threadly-footer .footer-address p,
        .threadly-footer .footer-address span {
            color: rgba(255, 255, 255, 0.78) !important;
            text-decoration: none;
            font-size: 17px;
            line-height: 1.6;
            transition: all 0.2s ease;
            margin: 0;
        }

        .threadly-footer .footer-list a:hover,
        .threadly-footer .footer-address a:hover {
            color: #ffffff !important;
            padding-left: 4px;
        }

        .threadly-footer .inform-box {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .threadly-footer .inform-box i,
        .threadly-footer .inform-box svg {
            width: 18px;
            height: 18px;
            color: rgba(255, 255, 255, 0.78);
            flex-shrink: 0;
        }

        .threadly-footer .sub-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            padding: 28px 0;
            margin-top: 10px;
        }

        .threadly-footer .sub-footer p,
        .threadly-footer .sub-footer span {
            color: rgba(255, 255, 255, 0.78) !important;
            font-size: 16px;
            margin-bottom: 0;
        }

        .threadly-footer .payment-box {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 18px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .threadly-footer .payment-box li {
            margin: 0;
        }

        @media (min-width: 1200px) {
            .threadly-footer .footer-grid > div {
                width: 25%;
            }
        }

        @media (max-width: 1199px) {
            .threadly-footer .footer-title h4 {
                margin-bottom: 18px;
            }
        }

        @media (max-width: 767px) {
            .threadly-footer {
                padding-top: 45px;
            }

            .threadly-footer .sub-footer {
                text-align: center;
            }

            .threadly-footer .payment-box {
                justify-content: center;
                margin-top: 12px;
            }
        }
    </style>

    <div class="container-fluid-lg">
        <div class="main-footer">
            <div class="row g-4 footer-grid justify-content-between">

                {{-- Cột 1 --}}
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="footer-col">
                        <div class="footer-title">
                            <h4>Về Threadly</h4>
                        </div>

                        <ul class="footer-list">
                            <li>
                                <a href="{{ route('home') }}">Trang chủ</a>
                            </li>
                            <li>
                                <a href="{{ route('client.about') }}">Về chúng tôi</a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Cột 2 --}}
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="footer-col">
                        <div class="footer-title">
                            <h4>Danh mục</h4>
                        </div>

                        <ul class="footer-list">

                            @foreach($footerCategories as $category)
                                <li>
                                    <a href="{{ route('client.category', $category->id) }}">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Cột 3 --}}
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="footer-col">
                        <div class="footer-title">
                            <h4>Tài khoản</h4>
                        </div>

                        <ul class="footer-list">
                            @auth
                                <li>
                                    <a href="{{ route('client.account.index') }}">Tài khoản của tôi</a>
                                </li>
                                <li>
                                    <a href="{{ route('client.orders.index') }}">Đơn hàng của tôi</a>
                                </li>
                                <li>
                                    <a href="{{ route('client.wallet.index') }}">Ví hoàn tiền demo</a>
                                </li>
                                <li>
                                    <a href="{{ route('client.cart.index') }}">Giỏ hàng</a>
                                </li>
                                <li>
                                    <a href="{{ route('client.wishlist.index') }}">Sản phẩm yêu thích</a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('login') }}">Đăng nhập</a>
                                </li>
                                <li>
                                    <a href="{{ route('register') }}">Đăng ký</a>
                                </li>
                                <li>
                                    <a href="{{ route('client.cart.index') }}">Giỏ hàng</a>
                                </li>
                            @endauth
                        </ul>
                    </div>
                </div>

                {{-- Cột 4 --}}
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="footer-col">
                        <div class="footer-title">
                            <h4>Thông tin cửa hàng</h4>
                        </div>

                        <ul class="footer-address">
                            <li>
                                <a href="tel:0123456789">
                                    <div class="inform-box">
                                        <i data-feather="phone"></i>
                                        <span>Hotline: 0123 456 789</span>
                                    </div>
                                </a>
                            </li>

                            <li>
                                <a href="mailto:support@threadly.vn">
                                    <div class="inform-box">
                                        <i data-feather="mail"></i>
                                        <span>Email: support@threadly.vn</span>
                                    </div>
                                </a>
                            </li>

                            <li>
                                <div class="inform-box">
                                    <i data-feather="map-pin"></i>
                                    <span>Hà Nội, Việt Nam</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>


    </div>
</footer>
