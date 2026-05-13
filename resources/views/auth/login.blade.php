<!DOCTYPE html>
<html lang="en">
<head>
    <title>Đăng nhập</title>
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
        body, input, button {
            font-family: 'Poppins', sans-serif !important;
        }

        .social-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #ddd;
            transition: 0.2s;
            text-decoration: none;
        }

        .social-btn:hover {
            background: #f3f3f3;
        }
    </style>
</head>

<body>

<div class="limiter">
    <div class="container-login100" style="background-image: url('{{ asset('admin/auth/images/bg-01.jpg') }}');">
        <div class="wrap-login100 p-l-110 p-r-110 p-t-62 p-b-33">

            {{-- Hiển thị lỗi --}}
            @if(session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            {{-- FORM --}}
            <form class="login100-form validate-form "
                  method="POST" action="{{ route('login.submit') }}">
                @csrf

                <span class="login100-form-title p-b-40">
                    Đăng nhập
                </span>

                {{-- EMAIL --}}
                <div class="p-t-15 p-b-9">
                    <span class="txt1">Email</span>
                </div>
                <div class="wrap-input100 validate-input" data-validate="Email không được bỏ trống">
                    <input class="input100" type="email" name="email" value="{{ old('email') }}" placeholder="Nhập email">
                    <span class="focus-input100"></span>
                </div>
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror

                {{-- PASSWORD --}}
                <div class="p-t-25 p-b-9">
                    <span class="txt1">Mật khẩu</span>
                </div>
                <div class="wrap-input100 validate-input" data-validate="Mật khẩu không được bỏ trống">
                    <input class="input100" type="password" name="password" placeholder="Nhập mật khẩu">
                    <span class="focus-input100"></span>
                </div>
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror

                {{-- REMEMBER --}}
                <div class="p-t-15 p-b-25">
                    <label class="d-inline-flex align-items-center" style="gap:8px; cursor:pointer;">
                        <a href="{{ route('password.request') }}">Quên mật khẩu</a>
                    </label>
                </div>

                {{-- BUTTON LOGIN --}}
                <div class="container-login100-form-btn m-t-17">
                    <button class="login100-form-btn">Đăng nhập</button>
                </div>

                {{-- DIVIDER --}}
                <div class="w-full text-center p-t-30 p-b-10">
                    <span class="txt2">hoặc tiếp tục với</span>
                </div>

                {{-- SOCIAL LOGIN (ONLY UI) --}}
                <div class="d-flex justify-content-center" style="gap: 15px;">

                    {{-- Google --}}
                    <a href="{{ route('google.login') }}" class="social-btn">
                        <svg width="20" height="20" viewBox="0 0 48 48">
                            <path fill="#FFC107" d="M43.6,20.4H42V20H24v8h11.3c-1.3,3.7-4.7,6.4-8.8,6.4c-5.1,0-9.3-4.2-9.3-9.3S21.4,15.7,26.5,15.7 c2.6,0,5,1.1,6.7,2.8l5.7-5.7C35.2,9.2,30.1,7,24,7C12.9,7,4,15.9,4,27s8.9,20,20,20c11,0,20-9,20-20 C44,25.1,43.8,22.7,43.6,20.4z"/>
                            <path fill="#FF3D00" d="M6.3,14.7l6.6,4.8c1.8-3.6,5.2-6.2,9.1-7.2V7H6.3V14.7z"/>
                            <path fill="#4CAF50" d="M24,47v-7c-3.9-1-7.3-3.6-9.1-7.2l-6.6,4.8L24,47z"/>
                            <path fill="#1976D2" d="M43.6,20.4H42V20H24v8h11.3c-0.9,2.5-2.6,4.8-4.7,6.3l0.1,0.1l6.7,5C40.5,35.6,44,31.7,44,27 C44,25.1,43.8,22.7,43.6,20.4z"/>
                        </svg>
                        <span style="color:#333; font-weight:500;">Google</span>
                    </a>

                    {{-- GitHub --}}
                    <a href="javascript:void(0)" class="social-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#000000"
                                  d="M12 .5C5.65.5.5 5.65.5 12c0 5.1 3.3 9.4 7.9 10.9.6.1.8-.3.8-.6v-2c-3.2.7-3.9-1.4-3.9-1.4-.5-1.1-1.1-1.4-1.1-1.4-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.5 2.4 1.1 3 .8.1-.7.4-1.1.7-1.4-2.6-.3-5.4-1.3-5.4-5.9 0-1.3.5-2.4 1.2-3.3-.1-.3-.5-1.6.1-3.3 0 0 1-.3 3.4 1.3 1-.3 2-.4 3-.4s2 .1 3 .4c2.4-1.6 3.4-1.3 3.4-1.3.6 1.7.2 3 .1 3.3.8.9 1.2 2 1.2 3.3 0 4.7-2.8 5.6-5.4 5.9.4.4.7 1 .7 2v3c0 .3.2.7.8.6 4.6-1.5 7.9-5.8 7.9-10.9C23.5 5.65 18.35.5 12 .5z"/>
                        </svg>
                        <span style="color:#333; font-weight:500;">GitHub</span>
                    </a>

                </div>

                {{-- REGISTER --}}
                <div class="w-full text-center p-t-55">
                    <span class="txt2">Chưa có tài khoản?</span>
                    <a href="{{ route('register') }}" class="txt2 bo1">Đăng ký</a>
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
