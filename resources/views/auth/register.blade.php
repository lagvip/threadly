<!DOCTYPE html>
<html lang="en">
<head>
    <title>Đăng ký</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/fonts/Linearicons-Free-v1.0.0/icon-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/animate/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/css-hamburgers/hamburgers.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/animsition/css/animsition.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/daterangepicker/daterangepicker.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('admin/auth/css/util.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/css/main.css') }}">

    <style>
        
            .input100 {
                width: 100%;
                background: #f5f5f5 !important;      /* nền xám nhạt */
                border: 1px solid #e5e5e5 !important; /* viền mỏng */
                border-radius: 12px !important;       /* bo cong */
                padding: 15px 20px !important;        /* khoảng cách trong rộng */
                font-size: 16px !important;
                color: #333 !important;
                transition: 0.3s ease-in-out;
            }

            /* Khi focus */
            .input100:focus {
                border-color: #b39ddb !important; /* tím pastel nhẹ */
                box-shadow: 0 0 0 3px rgba(179,157,219,0.3);
                background: #fff !important;
            }

            /* Placeholder */
            .input100::placeholder {
                color: #777 !important;
                opacity: 1;
            }
        body, input, button {
            font-family: 'Poppins', sans-serif !important;
        }

        .login100-form {
            width: 100%;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .wrap-login100 {
            border-radius: 20px;
        }

        .label-input100 {
            font-size: 15px;
            font-weight: 500;
            color: #555;
        }

        .input100 {
            border-radius: 10px !important;
            padding-left: 15px !important;
        }

        .login100-form-title {
            font-size: 30px;
            font-weight: 600;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="limiter">
    <div class="container-login100" style="background-image: url('{{ asset('admin/auth/images/bg-01.jpg') }}');">
        <div class="wrap-login100 p-l-110 p-r-110 p-t-55 p-b-55">

            {{-- Hiển thị lỗi --}}
            @if(session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <form class="login100-form validate-form" method="POST" action="{{ route('admin.auth.postRegister') }}">
                @csrf

                <span class="login100-form-title p-b-30">
                    Đăng ký
                </span>

                {{-- Họ tên --}}
                <div>
                    <span class="label-input100">Họ tên</span>
                    <input class="input100" type="text" name="name" placeholder="Nhập họ tên" value="{{ old('name') }}">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <span class="label-input100">Email</span>
                    <input class="input100" type="email" name="email" placeholder="Nhập email" value="{{ old('email') }}">
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Mật khẩu --}}
                <div>
                    <span class="label-input100">Mật khẩu</span>
                    <input class="input100" type="password" name="password" placeholder="Tối thiểu 6 ký tự">
                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Nhập lại mật khẩu --}}
                <div>
                    <span class="label-input100">Nhập lại mật khẩu</span>
                    <input class="input100" type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu">
                    @error('password_confirmation') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Nút đăng ký --}}
                <div class="container-login100-form-btn m-t-20">
                    <button class="login100-form-btn" type="submit">
                        Tạo tài khoản
                    </button>
                </div>

                {{-- Đã có tài khoản --}}
                <div class="w-full text-center p-t-40">
                    <span class="txt2">Đã có tài khoản?</span>
                    <a href="{{ route('admin.auth.login') }}" class="txt2 bo1">Đăng nhập</a>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="{{ asset('admin/auth/vendor/jquery/jquery-3.2.1.min.js') }}"></script>
<script src="{{ asset('admin/auth/vendor/animsition/js/animsition.min.js') }}"></script>
<script src="{{ asset('admin/auth/vendor/bootstrap/js/popper.js') }}"></script>
<script src="{{ asset('admin/auth/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin/auth/vendor/select2/select2.min.js') }}"></script>
<script src="{{ asset('admin/auth/vendor/daterangepicker/moment.min.js') }}"></script>
<script src="{{ asset('admin/auth/vendor/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('admin/auth/vendor/countdowntime/countdowntime.js') }}"></script>
<script src="{{ asset('admin/auth/js/main.js') }}"></script>

</body>
</html>
