<header class="topbar">
     <div class="container-fluid">
          <div class="navbar-header">
               <div class="d-flex align-items-center">

                    <!-- Nút mở menu -->
                    <div class="topbar-item">
                         <button type="button" class="button-toggle-menu me-2">
                              <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- Tiêu đề -->
                    <div class="topbar-item">
                         <h4 class="fw-bold topbar-button pe-none text-uppercase mb-0">QUẢN TRỊ</h4>
                    </div>
               </div>

               <div class="d-flex align-items-center gap-1">

                    <!-- Chế độ sáng/tối -->
                    <div class="topbar-item">
                         <button type="button" class="topbar-button" id="light-dark-mode">
                              <iconify-icon icon="solar:moon-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- Thông báo -->


                    <!-- Cài đặt giao diện -->
                    <div class="topbar-item d-none d-md-flex">
                         <button type="button" class="topbar-button" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas">
                              <iconify-icon icon="solar:settings-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>



                    <!-- Người dùng -->
                    @php
                        $user = auth()->user();
                        $avatar = $user && $user->avatar
                            ? asset('storage/' . $user->avatar)
                            : asset('admin/assets/images/users/avatar-1.jpg');
                    @endphp

                    <div class="dropdown topbar-item">
                        <a class="topbar-button" data-bs-toggle="dropdown" href="javascript:void(0)">
                            <span class="d-flex align-items-center gap-2">
                                <img class="rounded-circle object-fit-cover" width="32" height="32" src="{{ $avatar }}" alt="{{ $user->name ?? 'User' }}">
                            </span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end p-0 overflow-hidden" style="min-width: 260px;">
                            <div class="px-3 py-3 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <img class="rounded-circle object-fit-cover" width="48" height="48" src="{{ $avatar }}" alt="{{ $user->name ?? 'User' }}">
                                    <div>
                                        <h6 class="mb-1">Xin chào, {{ $user->name ?? 'Bạn' }}!</h6>
                                        <div class="text-muted small">{{ $user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </div>

                            <a class="dropdown-item" href="#">
                                <i class="bx bx-user-circle text-muted fs-18 align-middle me-1"></i>
                                <span>Hồ sơ cá nhân</span>
                            </a>

                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="bx bx-store text-muted fs-18 align-middle me-1"></i>
                                <span>Ra trang client</span>
                            </a>

                            <a class="dropdown-item" href="{{ route('password.change') }}">
                                <i class="bx bx-lock text-muted fs-18 align-middle me-1"></i>
                                <span>Quên mật khẩu</span>
                            </a>

                            <div class="dropdown-divider my-1"></div>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bx bx-log-out fs-18 align-middle me-1"></i>
                                    <span>Đăng xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>

               </div>
          </div>
     </div>
</header>
