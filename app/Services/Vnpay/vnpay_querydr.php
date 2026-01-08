<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Tra cứu giao dịch</title>
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
            <h3>Querydr</h3>
        </div>
        <div style="width: 100% ;border-bottom: 2px solid black;padding-bottom: 20px">
            <form action="{{ route('vnpay.querydr') }}" id="frmCreateOrder" method="post">
                @csrf
                <div class="form-group">
                    <label>Mã GD thanh toán cần quy vấn (vnp_TxnRef):</label>
                    <input class="form-control" data-val="true" name="txnRef" type="text" value="" />
                </div>
                <div class="form-group">
                    <label>Thời gian khởi tạo GD thanh toán (vnp_TransactionDate):</label>
                    <input class="form-control" data-val="true" name="transactionDate" type="text" placeholder="yyyyMMddHHmmss" value="" />
                </div>
                <input type="submit" class="btn btn-default" value="Querydr" />
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
                $vnp_Command = "querydr"; // Mã api
                $vnp_TxnRef = $_POST["txnRef"]; // Mã tham chiếu của giao dịch
                $vnp_OrderInfo = "Query transaction"; // Mô tả thông tin
                $vnp_TransactionDate = $_POST["transactionDate"]; // Thời gian ghi nhận giao dịch
                $vnp_CreateDate = date('YmdHis'); // Thời gian phát sinh request
                $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; // Địa chỉ IP của máy chủ thực hiện gọi API

                $datarq = [
                    "vnp_RequestId" => $vnp_RequestId,
                    "vnp_Version" => "2.1.0",
                    "vnp_Command" => $vnp_Command,
                    "vnp_TmnCode" => $vnp_TmnCode,
                    "vnp_TxnRef" => $vnp_TxnRef,
                    "vnp_OrderInfo" => $vnp_OrderInfo,
                    "vnp_TransactionDate" => $vnp_TransactionDate,
                    "vnp_CreateDate" => $vnp_CreateDate,
                    "vnp_IpAddr" => $vnp_IpAddr
                ];

                $dataHash = sprintf(
                    '%s|%s|%s|%s|%s|%s|%s|%s|%s',
                    $datarq['vnp_RequestId'],
                    $datarq['vnp_Version'],
                    $datarq['vnp_Command'],
                    $datarq['vnp_TmnCode'],
                    $datarq['vnp_TxnRef'],
                    $datarq['vnp_TransactionDate'],
                    $datarq['vnp_CreateDate'],
                    $datarq['vnp_IpAddr'],
                    $datarq['vnp_OrderInfo']
                );

                $checksum = hash_hmac('SHA512', $dataHash, $vnp_HashSecret);
                $datarq["vnp_SecureHash"] = $checksum;

                $response = Http::post($vnp_apiUrl, $datarq);
                $txnData = $response->getBody()->getContents();
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
