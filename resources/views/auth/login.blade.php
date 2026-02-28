<!DOCTYPE html>
<html lang="en">
<head>
    <title>Đăng nhập</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
</head>
<body>

<div class="limiter">
    <div class="container-login100" style="background-image: url('{{ asset('admin/auth/images/bg-01.jpg') }}');">
        <div class="wrap-login100 p-l-55 p-r-55 p-t-65 p-b-54">

            @if(session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <form class="login100-form validate-form" method="POST" action="{{ route('admin.auth.postLoginAdmin') }}">
                @csrf

                <span class="login100-form-title p-b-49">
                    Đăng nhập
                </span>

                <div class="wrap-input100 validate-input m-b-23">
                    <span class="label-input100">Email</span>
                    <input class="input100" type="email" name="email" placeholder="Nhập email" value="{{ old('email') }}">
                    <span class="focus-input100" data-symbol="&#xf206;"></span>
                    @error('email') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="wrap-input100 validate-input">
                    <span class="label-input100">Mật khẩu</span>
                    <input class="input100" type="password" name="password" placeholder="Nhập mật khẩu">
                    <span class="focus-input100" data-symbol="&#xf190;"></span>
                    @error('password') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="text-right p-t-8 p-b-31">
                    <label class="d-inline-flex align-items-center" style="gap:8px; cursor:pointer;">
                        <input type="checkbox" name="remember" value="1">
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                </div>

                <div class="container-login100-form-btn">
                    <div class="wrap-login100-form-btn">
                        <div class="login100-form-bgbtn"></div>
                        <button class="login100-form-btn" type="submit">
                            Đăng nhập
                        </button>
                    </div>
                </div>

                <div class="flex-col-c p-t-25">
                    <span class="txt1 p-b-17">Chưa có tài khoản?</span>
                    <a class="txt2" href="{{ route('admin.auth.register') }}">Đăng ký</a>
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
