<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>VNPAY RESPONSE</title>
    <!-- Bootstrap core CSS -->
    <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">
    <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="header clearfix">
            <h3 class="text-muted">VNPAY RESPONSE</h3>
        </div>

        <?php
            use Illuminate\Support\Facades\Config;

            $vnp_SecureHash = $_GET['vnp_SecureHash'];
            $inputData = array();

            foreach ($_GET as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }

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

            $vnp_HashSecret = config('vnpay.hash_secret'); // Lấy Hash Secret từ cấu hình
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        ?>

        <!-- Begin display -->
        <div style="width: 100%;padding-top:0px;font-weight: bold;color: #333333">
            <h3>Transaction Details</h3>
        </div>
        <div style="width: 100% ;border-bottom: 2px solid black;padding-bottom: 20px">
            <div class="form-group">
                <label>Mã đơn hàng:</label>
                <label>{{ $_GET['vnp_TxnRef'] }}</label>
            </div>
            <div class="form-group">
                <label>Số tiền:</label>
                <label>{{ $_GET['vnp_Amount'] }}</label>
            </div>
            <div class="form-group">
                <label>Nội dung thanh toán:</label>
                <label>{{ $_GET['vnp_OrderInfo'] }}</label>
            </div>
            <div class="form-group">
                <label>Mã phản hồi (vnp_ResponseCode):</label>
                <label>{{ $_GET['vnp_ResponseCode'] }}</label>
            </div>
            <div class="form-group">
                <label>Mã GD Tại VNPAY:</label>
                <label>{{ $_GET['vnp_TransactionNo'] }}</label>
            </div>
            <div class="form-group">
                <label>Mã Ngân hàng:</label>
                <label>{{ $_GET['vnp_BankCode'] }}</label>
            </div>
            <div class="form-group">
                <label>Thời gian thanh toán:</label>
                <label>{{ $_GET['vnp_PayDate'] }}</label>
            </div>
            <div class="form-group">
                <label>Kết quả:</label>
                <label>
                    @if ($secureHash == $vnp_SecureHash)
                        @if ($_GET['vnp_ResponseCode'] == '00')
                            <span style="color:blue">GD Thanh cong</span>
                        @else
                            <span style="color:red">GD Khong thanh cong</span>
                        @endif
                    @else
                        <span style="color:red">Chu ky khong hop le</span>
                    @endif
                </label>
            </div>
        </div>
        <p>&nbsp;</p>
        <footer class="footer">
            <p>&copy; VNPAY {{ date('Y') }}</p>
        </footer>
    </div>
</body>

</html>
