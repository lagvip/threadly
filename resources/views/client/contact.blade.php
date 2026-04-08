@extends('client.layouts.master')

@section('content')
    <!-- Breadcrumb -->
    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2>Liên hệ</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section class="contact-section py-5">
        <div class="container-fluid-lg">
            <div class="row g-4">

                <!-- FORM -->
                <div class="col-lg-7">
                    <div class="contact-form p-4 shadow rounded bg-white">
                        <h3 class="mb-4">Gửi thắc mắc cho chúng tôi</h3>
                        <p>Nếu bạn có thắc mắc gì, có thể gửi yêu cầu cho chúng tôi, và chúng tôi sẽ liên lạc lại với bạn sớm nhất có thể .</p>
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('contact.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Họ tên</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                        required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                        required>
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Số điện thoại</label>
                                <input type="tel" class="form-control" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label>Tin nhắn</label>
                                <textarea class="form-control" name="message" rows="5" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <button type="submit" class="btn-send">
                                Gửi cho chúng tôi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- COMPANY INFO -->
                <div class="col-lg-5">
                    <div class="contact-info p-4 shadow rounded bg-white h-100">
                        <h3 class="mb-4">Thông tin shop</h3>

                        <p><strong>Tên shop:</strong> Threadly</p>
                        <p><strong>Địa chỉ:</strong> Cổng số 2, 13 Trịnh Văn Bô, Xuân Phương, Hà Nội 100000, Vietnam</p>
                        <p><strong>Email:</strong> info@threadly.com</p>
                        <p><strong>Hotline:</strong> 1900 1234</p>

                        <hr>


                        <hr>

                        <h5>Bản đồ</h5>
                        <div style="width:100%; height:250px; border-radius:10px; overflow:hidden;">
                            <iframe
                                src="https://www.google.com/maps?q=Cổng+số+2,+13+Trịnh+Văn+Bô,+Xuân+Phương,+Hà+Nội+100000,+Vietnam&output=embed"
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy">
                            </iframe>
                        </div>

                    </div>
                </div>
    </section>

    <!-- CSS -->
    <style>
        .btn-send {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: 0.3s;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .contact-form input,
        .contact-form textarea {
            border-radius: 8px;
        }

        .contact-info i {
            color: #0d6efd;
        }
    </style>
@endsection
