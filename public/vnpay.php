<?php
session_start();
require_once('../backend/core/config.php');
date_default_timezone_set('Asia/Ho_Chi_Minh');

// === CONFIG VNPAY (sandbox) ===
$vnp_TmnCode = "2QXUI4J4";
$vnp_HashSecret = "SECRETKEY123456789";
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_ReturnUrl = BASE_URL . "public/order_success.php";

// === Nếu user bấm submit thanh toán ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redirect'])) {

    $fullname = trim($_POST['fullname'] ?? '');
    $amount   = intval($_POST['amount'] ?? 0);
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    $vnp_TxnRef = time(); // mã đơn hàng
    $vnp_OrderInfo = "Thanh toán đơn hàng Fruit Farm - " . $fullname;
    $vnp_OrderType = "billpayment";
    $vnp_Amount = $amount * 100; // nhân 100 theo yêu cầu VNPAY
    $vnp_Locale = "vn";
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

    // === Dữ liệu gửi đến VNPAY ===
    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_ReturnUrl,
        "vnp_TxnRef" => $vnp_TxnRef
    );

    // === Sắp xếp key A-Z theo chuẩn VNPAY ===
    ksort($inputData);
    $hashdata = urldecode(http_build_query($inputData, '', '&'));
    $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

    // === Tạo URL redirect ===
    $vnp_Url = $vnp_Url . "?" . http_build_query($inputData) . "&vnp_SecureHash=" . $vnp_SecureHash;

    header('Location: ' . $vnp_Url);
    exit;
}

// === Nếu chỉ là GET: hiển thị form xác nhận ===
$fullname = $_GET['fullname'] ?? '';
$amount   = $_GET['amount'] ?? 0;
$phone    = $_GET['phone'] ?? '';
$address  = $_GET['address'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thanh toán qua VNPAY</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-blue-50 to-white flex items-center justify-center min-h-screen">
<div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-lg border border-blue-100">
    <h1 class="text-2xl font-semibold text-center text-blue-600 mb-6">💳 Thanh toán qua VNPAY</h1>

    <form method="POST" class="space-y-4">
        <input type="hidden" name="redirect" value="1">
        <input type="hidden" name="fullname" value="<?= htmlspecialchars($fullname) ?>">
        <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
        <input type="hidden" name="address" value="<?= htmlspecialchars($address) ?>">

        <div>
            <label class="block text-gray-700 font-medium mb-1">Họ và tên</label>
            <input type="text" value="<?= htmlspecialchars($fullname) ?>" disabled
                   class="w-full border rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed">
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Số điện thoại</label>
            <input type="text" value="<?= htmlspecialchars($phone) ?>" disabled
                   class="w-full border rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed">
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Địa chỉ giao hàng</label>
            <input type="text" value="<?= htmlspecialchars($address) ?>" disabled
                   class="w-full border rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed">
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Số tiền thanh toán</label>
            <input type="text" value="<?= number_format($amount,0,",",".") ?>₫" disabled
                   class="w-full border rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed text-blue-600 font-semibold">
        </div>

        <div class="flex justify-center mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition">
                Thanh toán ngay
            </button>
        </div>
    </form>

    <div class="text-center mt-6 text-gray-500 text-sm">
        <p>Bạn sẽ được chuyển đến cổng <strong>VNPAY Sandbox</strong> để hoàn tất giao dịch.</p>
        <a href="thanhtoan.php" class="text-blue-600 hover:underline">← Quay lại</a>
    </div>
</div>
</body>
</html>
