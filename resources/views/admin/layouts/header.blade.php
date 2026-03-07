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
                    <div class="dropdown topbar-item">
                         <a class="topbar-button" data-bs-toggle="dropdown">
                              <span class="d-flex align-items-center">
                                   <img class="rounded-circle" width="32" src="{{ asset('admin/assets/images/users/avatar-1.jpg') }}">
                              </span>
                         </a>

                         <div class="dropdown-menu dropdown-menu-end">

                              <h6 class="dropdown-header">Xin chào, Admin!</h6>

                              <a class="dropdown-item" href="#">
                                   <i class="bx bx-user-circle text-muted fs-18 align-middle me-1"></i>
                                   <span>Hồ sơ cá nhân</span>
                              </a>
                              <a class="dropdown-item" href="#">
                                   <i class="bx bx-message-dots text-muted fs-18 align-middle me-1"></i>
                                   <span>Tin nhắn</span>
                              </a>
                              <a class="dropdown-item" href="#">
                                   <i class="bx bx-wallet text-muted fs-18 align-middle me-1"></i>
                                   <span>Gói nâng cấp</span>
                              </a>
                              <a class="dropdown-item" href="#">
                                   <i class="bx bx-help-circle text-muted fs-18 align-middle me-1"></i>
                                   <span>Trợ giúp</span>
                              </a>
                              <a class="dropdown-item" href="#">
                                   <i class="bx bx-lock text-muted fs-18 align-middle me-1"></i>
                                   <span>Khóa màn hình</span>
                              </a>

                              <div class="dropdown-divider my-1"></div>

                              <form action="{{ route('admin.auth.logout') }}" method="POST">
                                   @csrf
                                   <button class="dropdown-item text-danger">
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
