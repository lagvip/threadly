@extends('client.layouts.master')

@section('title', 'Về chúng tôi')

@section('content')
    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2>Về chúng tôi</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Về chúng tôi</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-b-space">
        <div class="container-fluid-lg">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="title d-block">
                        <h2>Mua sắm dễ hơn, nhanh hơn, đáng tin hơn</h2>
                        <span class="title-leaf">
                            <svg class="icon-width">
                                <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/leaf.svg#leaf"></use>
                            </svg>
                        </span>
                        <p>
                            Chúng tôi xây dựng cửa hàng với mục tiêu mang đến trải nghiệm mua sắm hiện đại: sản phẩm rõ nguồn gốc,
                            giá minh bạch, giao hàng nhanh và chăm sóc khách hàng tận tâm.
                        </p>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="service-contain-2 h-100">
                                <svg class="icon-width">
                                    <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#shipping"></use>
                                </svg>
                                <div class="service-detail">
                                    <h3>Giao nhanh</h3>
                                    <h6 class="text-content">Đóng gói chuẩn, theo dõi đơn hàng rõ ràng</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="service-contain-2 h-100">
                                <svg class="icon-width">
                                    <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#return"></use>
                                </svg>
                                <div class="service-detail">
                                    <h3>Đổi trả linh hoạt</h3>
                                    <h6 class="text-content">Hỗ trợ đổi trả theo chính sách cửa hàng</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="service-contain-2 h-100">
                                <svg class="icon-width">
                                    <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#service"></use>
                                </svg>
                                <div class="service-detail">
                                    <h3>Hỗ trợ 24/7</h3>
                                    <h6 class="text-content">Tư vấn nhanh qua chat và hotline</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="service-contain-2 h-100">
                                <svg class="icon-width">
                                    <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#pay"></use>
                                </svg>
                                <div class="service-detail">
                                    <h3>Thanh toán tiện lợi</h3>
                                    <h6 class="text-content">Nhiều phương thức thanh toán an toàn</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="newsletter-box newsletter-box-2" style="min-height: 360px; display:flex; align-items:center;">
                        <div class="w-100 p-4 p-sm-5">
                            <h3 class="mb-2">Cam kết của chúng tôi</h3>
                            <p class="text-content mb-4">
                                Mỗi sản phẩm đều được chọn lọc kỹ, thông tin rõ ràng và hỗ trợ sau mua đầy đủ.
                                Nếu bạn cần tư vấn chọn size/màu, đội ngũ luôn sẵn sàng.
                            </p>

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <span class="badge theme-bg-color text-white" style="padding: 10px 12px;">01</span>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Sản phẩm chất lượng</h5>
                                            <div class="text-content">Ưu tiên nguồn hàng uy tín và kiểm tra trước khi giao.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <span class="badge theme-bg-color text-white" style="padding: 10px 12px;">02</span>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Trải nghiệm mua sắm mượt mà</h5>
                                            <div class="text-content">Tìm kiếm nhanh, đặt hàng gọn, cập nhật trạng thái đơn rõ ràng.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <span class="badge theme-bg-color text-white" style="padding: 10px 12px;">03</span>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Chăm sóc khách hàng tận tâm</h5>
                                            <div class="text-content">Hỗ trợ trước và sau mua để bạn luôn yên tâm.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2 flex-wrap">
                                <a href="{{ route('home') }}" class="btn btn-animation">
                                    Tiếp tục mua sắm
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="service-section section-b-space">
        <div class="container-fluid-lg">
            <div class="title d-block">
                <h2>Vì sao khách hàng chọn chúng tôi?</h2>
                <span class="title-leaf">
                    <svg class="icon-width">
                        <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/leaf.svg#leaf"></use>
                    </svg>
                </span>
                <p>Những giá trị cốt lõi tạo nên sự khác biệt của cửa hàng.</p>
            </div>

            <div class="row g-3 row-cols-xxl-4 row-cols-lg-4 row-cols-md-2 row-cols-1">
                <div>
                    <div class="service-contain-2 h-100">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#offer"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Giá tốt mỗi ngày</h3>
                            <h6 class="text-content">Nhiều chương trình ưu đãi theo mùa</h6>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="service-contain-2 h-100">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#pay"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Bảo mật thanh toán</h3>
                            <h6 class="text-content">Tối ưu trải nghiệm và an toàn thông tin</h6>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="service-contain-2 h-100">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#service"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Tư vấn đúng nhu cầu</h3>
                            <h6 class="text-content">Gợi ý sản phẩm phù hợp, tiết kiệm thời gian</h6>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="service-contain-2 h-100">
                        <svg class="icon-width">
                            <use xlink:href="https://themes.pixelstrap.com/fastkart/assets/svg/svg/service-icon-4.svg#return"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Hậu mãi rõ ràng</h3>
                            <h6 class="text-content">Luôn đồng hành cùng bạn sau khi nhận hàng</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
