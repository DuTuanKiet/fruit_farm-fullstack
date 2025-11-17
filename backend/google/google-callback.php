<?php
// File: backend/google/google-callback.php (ĐÃ SỬA)
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/config.php';

use Google\Service\Oauth2;
use Google\Service\PeopleService;

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(BASE_URL . 'backend/google/google-callback.php');
$client->addScope("https://www.googleapis.com/auth/user.phonenumbers.read");
$client->addScope("https://www.googleapis.com/auth/user.addresses.read");
$client->addScope("email");
$client->addScope("profile");

// Kiểm tra mã `code` Google trả về
if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        // Nếu có lỗi từ Google
        if (isset($token['error'])) {
            echo "<pre>";
            print_r($token);
            echo "</pre>";
            exit("Google OAuth error: " . htmlspecialchars($token['error_description'] ?? $token['error']));
        }

       // Nếu lấy token thành công
        if ($token && isset($token['access_token'])) {
        $client->setAccessToken($token['access_token']);

        // 💡 SỬ DỤNG PEOPLE API để lấy thông tin chi tiết
        $peopleService = new Google\Service\PeopleService($client);
    
        // Chỉ định các trường (fields) muốn lấy: names, emailAddresses, phoneNumbers, addresses
        $person = $peopleService->people->get('people/me', [
        'personFields' => 'names,emailAddresses,phoneNumbers,addresses'
        ]);

        // Lấy tên và email (luôn có)
        $email = $person->getEmailAddresses()[0]->getValue() ?? null;
        $name  = $person->getNames()[0]->getDisplayName() ?? null;

        // 💡 LẤY PHONE VÀ ADDRESS TỪ PEOPLE API
        $phone = "";
        if (!empty($person->getPhoneNumbers())) {
        // Lấy số điện thoại đầu tiên
        $phone = $person->getPhoneNumbers()[0]->getValue();
        }
    
        $address = "";
        if (!empty($person->getAddresses())) {
        // Lấy địa chỉ đầu tiên dưới dạng chuỗi định dạng đầy đủ (ví dụ: Tỉnh/Thành phố, Quốc gia)
        $address = $person->getAddresses()[0]->getFormattedValue();
        }

            // Kiểm tra email trong CSDL
$stmt = $conn->prepare("SELECT id, username, status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // user đã tồn tại → check trạng thái
    $user = $result->fetch_assoc();

    // 🚫 NGĂN GOOGLE LOGIN nếu user bị vô hiệu hóa
    if (strtolower(trim($user['status'])) === 'disabled') {
        echo "<script>alert('Tài khoản Google của bạn đã bị vô hiệu hóa. Không thể đăng nhập.'); window.location.href='/fruitfarm/public/index.php';</script>";
        exit;
    }

    // user đã tồn tại → cập nhật phone và address nếu có
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['phone'] = $phone;     
    $_SESSION['address'] = $address;

    $stmt_update = $conn->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
    $stmt_update->bind_param("ssi", $phone, $address, $user['id']);
    $stmt_update->execute();
    $stmt_update->close();

} else {
    // user mới → thêm luôn phone và address
    $password_hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
    $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, phone, address, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt_insert->bind_param("sssss", $name, $email, $password_hash, $phone, $address);
    $stmt_insert->execute();

    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $stmt_insert->insert_id;
    $_SESSION['username'] = $name;
}

            // Thành công → về trang chủ
            header('Location: /fruitfarm/public/index.php');
            exit();
        } else {
            exit("Không lấy được access token từ Google.");
        }

    } catch (Exception $e) {
        exit("Lỗi OAuth: " . $e->getMessage());
    }
}

// Nếu không có code hoặc lỗi khác
header('Location: /fruitfarm/public/index.php?error=google_login_failed');
exit();
?>
