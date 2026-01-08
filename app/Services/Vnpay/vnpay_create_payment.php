<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy cấu hình từ Laravel thay vì require_once
$vnp_TmnCode = config('vnpay.tmn_code');
$vnp_HashSecret = config('vnpay.hash_secret');
$vnp_Url = config('vnpay.url');
$vnp_Returnurl = config('vnpay.return_url');
$vnp_apiUrl = config('vnpay.api_url');

// Sử dụng gmdate để đảm bảo thời gian là UTC
$expire = gmdate('YmdHis', strtotime('+15 minutes'));  // Tính toán thời gian hết hạn 15 phút sau


// Thông tin giao dịch
$vnp_TxnRef = rand(1, 10000); // Mã giao dịch thanh toán tham chiếu của merchant
$vnp_Amount = $_POST['total_amount'] ; // Số tiền thanh toán
$vnp_Locale = $_POST['language'] ?? 'vn'; // Ngôn ngữ chuyển hướng thanh toán
$vnp_BankCode = $_POST['bankCode'] ?? ''; // Rỗng nếu không chọn
$vnp_IpAddr = $_SERVER['REMOTE_ADDR']; // IP Khách hàng thanh toán

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount * 100,  // VNPay yêu cầu số tiền tính bằng đơn vị "cent"
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef,
    "vnp_OrderType" => "other",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $expire
);

// Nếu có mã ngân hàng thì thêm vào
if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

// Sắp xếp các tham số theo thứ tự
ksort($inputData);

// Tạo chuỗi query và hash
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

// Tạo URL thanh toán VNPay
$vnp_Url = $vnp_Url . "?" . $query;

// Thêm Secure Hash vào URL
if (isset($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);  // Tạo Secure Hash
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}

// Chuyển hướng người dùng đến URL thanh toán VNPay
header('Location: ' . $vnp_Url);
die();
