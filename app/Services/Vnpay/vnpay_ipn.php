<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy cấu hình từ Laravel thay vì require_once
$vnp_HashSecret = config('vnpay.hash_secret');  // Lấy Hash Secret từ cấu hình
$vnp_Url = config('vnpay.url');  // Lấy URL thanh toán VNPay từ cấu hình
$vnp_Returnurl = config('vnpay.return_url');  // Lấy Return URL từ cấu hình
$vnp_apiUrl = config('vnpay.api_url');  // Lấy API URL từ cấu hình

$inputData = array();
$returnData = array();

// Lấy các tham số vnp từ URL
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

// Lấy hash và kiểm tra dữ liệu
$vnp_SecureHash = $inputData['vnp_SecureHash'];
unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

// Kiểm tra checksum
$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$vnpTranId = $inputData['vnp_TransactionNo']; // Mã giao dịch tại VNPAY
$vnp_BankCode = $inputData['vnp_BankCode']; // Ngân hàng thanh toán
$vnp_Amount = $inputData['vnp_Amount'] / 100; // Số tiền thanh toán VNPAY phản hồi

$Status = 0; // Trạng thái giao dịch chưa được xử lý
$orderId = $inputData['vnp_TxnRef'];

try {
    // Kiểm tra checksum của dữ liệu
    if ($secureHash == $vnp_SecureHash) {
        // Lấy thông tin đơn hàng từ database và kiểm tra trạng thái của đơn hàng
        $order = NULL;  // Giả sử đây là câu truy vấn lấy thông tin đơn hàng từ DB

        if ($order != NULL) {
            if ($order["Amount"] == $vnp_Amount) { // Kiểm tra số tiền thanh toán của giao dịch
                if ($order["Status"] != NULL && $order["Status"] == 0) {
                    if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                        $Status = 1; // Trạng thái thanh toán thành công
                    } else {
                        $Status = 2; // Trạng thái thanh toán thất bại
                    }

                    // Cập nhật kết quả thanh toán vào Database
                    // Ví dụ: cập nhật trạng thái đơn hàng trong DB

                    // Trả kết quả cho VNPAY
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success';
                } else {
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order already confirmed';
                }
            } else {
                $returnData['RspCode'] = '04';
                $returnData['Message'] = 'Invalid amount';
            }
        } else {
            $returnData['RspCode'] = '01';
            $returnData['Message'] = 'Order not found';
        }
    } else {
        $returnData['RspCode'] = '97';
        $returnData['Message'] = 'Invalid signature';
    }
} catch (Exception $e) {
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknown error';
}

// Trả lại VNPAY theo định dạng JSON
echo json_encode($returnData);
