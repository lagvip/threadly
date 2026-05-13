<div class="main-nav">

    <div class="logo-box d-flex justify-content-center align-items-center py-3" style="height: 100px;">
        <a href="{{ route('admin.homeAdmin') }}" class="logo-dark">
            <img src="{{ asset('admin/assets/images/cdp2.png') }}" class="logo-sm" alt="logo nhỏ" style="height: 150px;">
        </a>
        <a href="{{ route('admin.homeAdmin') }}" class="logo-dark">
            <img src="{{ asset('admin/assets/images/cdp2.png') }}" class="logo-lg" alt="logo nền tối" style="height: 150px;">
        </a>

        <a href="{{ route('admin.homeAdmin') }}" class="logo-light">
            <img src="{{ asset('admin/assets/images/cdp2.png') }}" class="logo-sm" alt="logo nhỏ" style="height: 150px;">
        </a>
        <a href="{{ route('admin.homeAdmin') }}" class="logo-light">
            <img src="{{ asset('admin/assets/images/cdp2.png') }}" class="logo-lg" alt="logo nền sáng" style="height: 150px;">
        </a>
    </div>

    <button type="button" class="button-sm-hover" aria-label="Hiển thị Thanh điều hướng">
        <iconify-icon icon="solar:double-alt-arrow-right-bold-duotone" class="button-sm-hover-icon"></iconify-icon>
    </button>

    @php
        $user = auth()->user();
    @endphp

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title">Tổng quan</li>

            {{-- Admin + Manager --}}
            @if(auth()->check() && $user->isStaff())

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.homeAdmin') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:widget-5-bold-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text"> Bảng điều khiển </span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCategory" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarCategory">
                        <span class="nav-text"> Danh mục </span>
                    </a>
                    <div class="collapse" id="sidebarCategory">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('listCategory.list') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarBrand" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarBrand">
                        <span class="nav-text"> Thương hiệu </span>
                    </a>
                    <div class="collapse" id="sidebarBrand">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('brands.index') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarProducts" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarProducts">
                        <span class="nav-text"> Sản phẩm </span>
                    </a>
                    <div class="collapse" id="sidebarProducts">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('product.listProduct') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarColor" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarColor">
                        <span class="nav-text"> Màu sắc </span>
                    </a>
                    <div class="collapse" id="sidebarColor">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('listColor.list') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarBanner" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarBanner">
                        <span class="nav-text"> Banner </span>
                    </a>
                    <div class="collapse" id="sidebarBanner">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('listBanner.list') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarSize" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarSize">
                        <span class="nav-text"> Kích cỡ </span>
                    </a>
                    <div class="collapse" id="sidebarSize">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('listSize.list') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarOrders" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarOrders">
                        <span class="nav-text"> Đơn hàng </span>
                    </a>
                    <div class="collapse" id="sidebarOrders">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('orders.index') }}">Danh sách</a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('deleted.index') }}">Đơn hàng đã xoá</a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('admin.refunds.index') }}">Yêu cầu hoàn tiền</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarReviews" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarReviews">
                        <span class="nav-text"> Đánh giá </span>
                    </a>
                    <div class="collapse" id="sidebarReviews">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('reviews.index') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarChat" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarChat">
                        <span class="nav-text"> Chat trực tuyến </span>
                    </a>
                    <div class="collapse" id="sidebarChat">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('admin.chats.index') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarVoucher" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarVoucher">
                        <span class="nav-text"> Voucher </span>
                    </a>
                    <div class="collapse" id="sidebarVoucher">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('vouchers.index') }}">
                                    Danh sách voucher
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>



            {{-- Chỉ Admin --}}
            @if(auth()->check() && $user->isAdmin())

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarUsers" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarUsers">
                        <span class="nav-text"> Người dùng </span>
                    </a>
                    <div class="collapse" id="sidebarUsers">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('users.list') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarRoles" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarRoles">
                        <span class="nav-text"> Vai trò </span>
                    </a>
                    <div class="collapse" id="sidebarRoles">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('roles.list') }}">Danh sách role</a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('roles.trash') }}">Vai trò đã xoá</a>
                            </li>
                        </ul>
                    </div>
                </li>

            @endif
               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarContacts" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarContacts">
                        <span class="nav-text"> Liên hệ </span>
                    </a>
                    <div class="collapse" id="sidebarContacts">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('listContact.list') }}">Danh sách</a>
                            </li>
                        </ul>
                    </div>
                </li>

            @endif

        </ul>
    </div>
</div>
