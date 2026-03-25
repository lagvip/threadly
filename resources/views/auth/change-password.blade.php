<!DOCTYPE html>
<html lang="en">
<head>
    <title>Đổi mật khẩu</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/fonts/Linearicons-Free-v1.0.0/icon-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/animate/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/css-hamburgers/hamburgers.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/animsition/css/animsition.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/vendor/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/css/util.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/auth/css/main.css') }}">

    <style>
        body, input, button {
            font-family: 'Poppins', sans-serif !important;
        }
    </style>
</head>
<body>

<div class="limiter">
    <div class="container-login100" style="background-image: url('{{ asset('admin/auth/images/bg-01.jpg') }}');">
        <div class="wrap-login100 p-l-110 p-r-110 p-t-62 p-b-33">

            @if(session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <form class="login100-form validate-form" method="POST" action="{{ route('password.change') }}">
                @csrf

                <span class="login100-form-title p-b-35">
                    Đổi mật khẩu
                </span>

                <div class="p-t-10 p-b-9">
                    <span class="txt1">Mật khẩu hiện tại</span>
                </div>
                <div class="wrap-input100 validate-input">
                    <input class="input100" type="password" name="current_password" placeholder="Nhập mật khẩu hiện tại">
                    <span class="focus-input100"></span>
                </div>
                @error('current_password')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror

                <div class="p-t-20 p-b-9">
                    <span class="txt1">Mật khẩu mới</span>
                </div>
                <div class="wrap-input100 validate-input">
                    <input class="input100" type="password" name="password" placeholder="Nhập mật khẩu mới">
                    <span class="focus-input100"></span>
                </div>
                @error('password')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror

                <div class="p-t-20 p-b-9">
                    <span class="txt1">Xác nhận mật khẩu mới</span>
                </div>
                <div class="wrap-input100 validate-input">
                    <input class="input100" type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu mới">
                    <span class="focus-input100"></span>
                </div>

                <div class="container-login100-form-btn m-t-30">
                    <button class="login100-form-btn">
                        Cập nhật mật khẩu
                    </button>
                </div>

                <div class="w-full text-center p-t-20">
                    <a href="{{ route('home') }}" class="txt2 bo1">Quay lại trang chủ</a>
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
