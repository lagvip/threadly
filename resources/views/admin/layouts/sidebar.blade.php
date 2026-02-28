<div class="main-nav">

    <div class="logo-box d-flex justify-content-center align-items-center py-3" style="height: 100px;">
        <a href="{{ route('admin.homeAdmin') }}" class="logo-dark">
            <img src="{{ asset('admin/assets/images/cdp2.png') }}" class="logo-sm" alt="logo sm" style="height: 150px;">
        </a>
        <a href="{{ route('admin.homeAdmin') }}" class="logo-dark">
            <img src="{{ asset('admin/assets/images/cdp2.png') }}" class="logo-lg" alt="logo dark"
                style="height: 150px;">
        </a>

        <a href="{{ route('admin.homeAdmin') }}" class="logo-light">
            <img src="{{ asset('admin/assets/images/cdp2.png') }}" class="logo-sm" alt="logo sm"
                style="height: 150px;">
        </a>
        <a href="{{ route('admin.homeAdmin') }}" class="logo-light">
            <img src="{{ asset('admin/assets/images/cdp2.png') }}" class="logo-lg" alt="logo light"
                style="height: 150px;">
        </a>
    </div>

    <!-- Menu Toggle Button (sm-hover) -->
    <button type="button" class="button-sm-hover" aria-label="Hiển thị Thanh điều hướng">
        <iconify-icon icon="solar:double-alt-arrow-right-bold-duotone" class="button-sm-hover-icon"></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title">Tổng quan</li>

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

            <li class="sub-nav-item">
                <a class="sub-nav-link" href="{{ route('vouchers.create') }}">
                    Thêm voucher
                </a>
            </li>
        </ul>
    </div>
</li>





        </ul>
    </div>
</div>