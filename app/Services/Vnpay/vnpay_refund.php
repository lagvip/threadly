<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hoàn tiền giao dịch</title>
    <!-- Bootstrap core CSS -->
    <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">
    <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="header clearfix">
            <h3 class="text-muted">VNPAY DEMO</h3>
        </div>
        <div style="width: 100%;padding-top:0px;font-weight: bold;color: #333333">
            <h3>Refund</h3>
        </div>
        <div style="width: 100% ;border-bottom: 2px solid black;padding-bottom: 20px">
            <form action="{{ route('vnpay.refund') }}" id="frmCreateOrder" method="post">
                @csrf
                <div class="form-group">
                    <label>Mã GD thanh toán cần hoàn (vnp_TxnRef):</label>
                    <input class="form-control" data-val="true" name="TxnRef" type="text" value="" />
                </div>
                <div class="form-group">
                    <label>Kiểu hoàn tiền (vnp_TransactionType):</label>
                    <select name="TransactionType" id="trantype" class="form-control">
                        <option value="02">Hoàn tiền toàn phần</option>
                        <option value="03">Hoàn tiền một phần</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Số tiền hoàn:</label>
                    <input class="form-control" max="100000000" min="1" name="Amount" type="number" value="" />
                </div>
                <div class="form-group">
                    <label>Thời gian khởi tạo GD thanh toán (vnp_TransactionDate):</label>
                    <input class="form-control" data-val="true" name="TransactionDate" type="text" placeholder="yyyyMMddHHmmss" value="" />
                </div>
                <div class="form-group">
                    <label>User khởi tạo hoàn (vnp_CreateBy):</label>
                    <input class="form-control" data-val="true" name="CreateBy" type="text" value="" />
                </div>
                <input type="submit" class="btn btn-default" value="Refund" />
            </form>
        </div>

        @if ($_SERVER['REQUEST_METHOD'] === 'POST')
            @php
                use Illuminate\Support\Facades\Http;

                date_default_timezone_set('Asia/Ho_Chi_Minh');

                $vnp_TmnCode = config('vnpay.tmn_code'); // Lấy TMN Code từ cấu hình
                $vnp_HashSecret = config('vnpay.hash_secret'); // Lấy Hash Secret từ cấu hình
                $vnp_Url = config('vnpay.url'); // Lấy URL thanh toán VNPay từ cấu hình
                $vnp_apiUrl = config('vnpay.api_url'); // Lấy API URL từ cấu hình

                $vnp_RequestId = rand(1,10000); // Mã truy vấn
                $vnp_Command = "refund"; // Mã api
                $vnp_TransactionType = $_POST["TransactionType"]; // 02 hoàn trả toàn phần - 03 hoàn trả một phần
                $vnp_TxnRef = $_POST["TxnRef"]; // Mã tham chiếu của giao dịch
                $vnp_Amount = $_POST["Amount"] * 100; // Số tiền hoàn trả
                $vnp_OrderInfo = "Hoan Tien Giao Dich"; // Mô tả thông tin
                $vnp_TransactionNo = "0"; // Tuỳ chọn, "0": giả sử merchant không ghi nhận được mã GD do VNPAY phản hồi.
                $vnp_TransactionDate = $_POST["TransactionDate"]; // Thời gian ghi nhận giao dịch
                $vnp_CreateDate = date('YmdHis'); // Thời gian phát sinh request
                $vnp_CreateBy = $_POST["CreateBy"]; // Người khởi tạo hoàn tiền
                $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; // Địa chỉ IP của máy chủ thực hiện gọi API

                $ispTxnRequest = [
                    "vnp_RequestId" => $vnp_RequestId,
                    "vnp_Version" => "2.1.0",
                    "vnp_Command" => $vnp_Command,
                    "vnp_TmnCode" => $vnp_TmnCode,
                    "vnp_TransactionType" => $vnp_TransactionType,
                    "vnp_TxnRef" => $vnp_TxnRef,
                    "vnp_Amount" => $vnp_Amount,
                    "vnp_OrderInfo" => $vnp_OrderInfo,
                    "vnp_TransactionNo" => $vnp_TransactionNo,
                    "vnp_TransactionDate" => $vnp_TransactionDate,
                    "vnp_CreateDate" => $vnp_CreateDate,
                    "vnp_CreateBy" => $vnp_CreateBy,
                    "vnp_IpAddr" => $vnp_IpAddr
                ];

                $dataHash = sprintf(
                    '%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s',
                    $ispTxnRequest['vnp_RequestId'],
                    $ispTxnRequest['vnp_Version'],
                    $ispTxnRequest['vnp_Command'],
                    $ispTxnRequest['vnp_TmnCode'],
                    $ispTxnRequest['vnp_TransactionType'],
                    $ispTxnRequest['vnp_TxnRef'],
                    $ispTxnRequest['vnp_Amount'],
                    $ispTxnRequest['vnp_TransactionNo'],
                    $ispTxnRequest['vnp_TransactionDate'],
                    $ispTxnRequest['vnp_CreateBy'],
                    $ispTxnRequest['vnp_CreateDate'],
                    $ispTxnRequest['vnp_IpAddr'],
                    $ispTxnRequest['vnp_OrderInfo']
                );

                $checksum = hash_hmac('SHA512', $dataHash, $vnp_HashSecret);
                $ispTxnRequest["vnp_SecureHash"] = $checksum;

                // Gọi API
                $response = Http::post($vnp_apiUrl, $ispTxnRequest);
                $txnData = $response->body();
            @endphp

            <div>
                <label>API Response:</label>
                <pre>
                    {{ $txnData }}
                </pre>
            </div>
        @endif
    </div>
</body>

</html>
