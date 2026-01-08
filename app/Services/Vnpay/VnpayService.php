<?php
namespace App\Services\Vnpay;

class VnpayService
{
    public function createPaymentUrl($request)
    {
        // Bao gồm file vnpay_create_payment.php
        include app_path('Services/Vnpay/vnpay_create_payment.php');

        // Lấy giá trị từ request
        $vnp_Amount = $request->input('amount');
        $vnp_Locale = $request->input('language');
        $vnp_BankCode = $request->input('bankCode');

        // Khởi tạo các tham số cần thiết và gọi hàm trong file vnpay_create_payment.php
        $paymentUrl = $vnp_Url;  // Lấy URL thanh toán đã được tạo trong file vnpay_create_payment.php

        return $paymentUrl;
    }
}
