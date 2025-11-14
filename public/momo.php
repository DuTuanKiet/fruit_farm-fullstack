<?php
session_start();
require_once('../backend/core/config.php');

// 🧾 Lấy thông tin người dùng từ GET (từ trang thanhtoan.php gửi sang)
$fullname = $_GET['fullname'] ?? '';
$amount   = (int)($_GET['amount'] ?? 0);
$phone    = $_GET['phone'] ?? '';
$address  = $_GET['address'] ?? '';

// ⚠️ Kiểm tra dữ liệu hợp lệ
if (empty($fullname) || empty($phone) || $amount <= 0) {
    die("<h3 style='color:red;text-align:center;'>Yêu cầu không hợp lệ!</h3>");
}

// ✅ Cấu hình MoMo sandbox
$endpoint   = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = "MOMO";
$accessKey   = "F8BBA842ECF85";
$secretKey   = "K951B6PE1waDMi640xX08PD3vg6EkVlz";

$orderInfo   = "Thanh toán đơn hàng Fruit Farm";
$orderId     = time(); // mã đơn hàng tạm thời
$redirectUrl = BASE_URL . "public/order_success.php?order_id={$orderId}";
$ipnUrl      = $redirectUrl;
$requestId   = time();
$requestType = "captureWallet";

// ✅ Tạo chữ ký
$rawHash = "accessKey=$accessKey&amount=$amount&extraData=&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
$signature = hash_hmac("sha256", $rawHash, $secretKey);

// ✅ Dữ liệu gửi đến MoMo
$data = [
    'partnerCode' => $partnerCode,
    'partnerName' => "Fruit Farm",
    'storeId'     => "FruitFarmStore",
    'requestId'   => $requestId,
    'amount'      => $amount,
    'orderId'     => $orderId,
    'orderInfo'   => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl'      => $ipnUrl,
    'lang'        => 'vi',
    'extraData'   => "",
    'requestType' => $requestType,
    'signature'   => $signature
];

// ✅ Gửi request đến MoMo API
$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);

$result = curl_exec($ch);
curl_close($ch);
$response = json_decode($result, true);

// ✅ Kiểm tra phản hồi và xử lý
if (isset($response['payUrl'])) {
    header('Location: ' . $response['payUrl']);
    exit;
} else {
    echo "<h3>❌ Lỗi khi kết nối MoMo!</h3>";
    echo "<pre>" . htmlspecialchars(print_r($response, true)) . "</pre>";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thanh toán MoMo</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-pink-100 to-white flex items-center justify-center min-h-screen">
  <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-lg">
    <h1 class="text-2xl font-semibold text-center text-pink-600 mb-6">💖 Thanh toán qua MoMo</h1>

    <form id="momoForm" action="" method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 font-medium mb-1">Tên người nhận</label>
        <input type="text" name="fullname" required placeholder="Nhập họ tên"
               class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-pink-300">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Số điện thoại MoMo</label>
        <input type="text" name="phone" required placeholder="VD: 0901234567"
               class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-pink-300">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Số tiền thanh toán (VNĐ)</label>
        <input type="number" name="amount" min="1000" required
               class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-pink-300">
      </div>

      <div class="flex justify-center mt-6">
        <button type="submit"
                class="bg-pink-600 hover:bg-pink-700 text-white font-medium px-6 py-2 rounded-lg transition">
          Thanh toán bằng MoMo
        </button>
      </div>
    </form>

    <div class="text-center mt-6 text-gray-500 text-sm">
      <a href="thanhtoan.php" class="text-pink-600 hover:underline">← Quay lại thanh toán</a>
    </div>
  </div>
</body>
</html>