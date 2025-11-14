<?php
// File: backend/google/google-login.php (ĐÃ SỬA)
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../core/config.php';

session_start();

// 1. Khởi tạo Google Client
$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID); // Dùng hằng số
$client->setClientSecret(GOOGLE_CLIENT_SECRET); 
$client->setRedirectUri(BASE_URL . 'backend/google/google-callback.php');
$client->addScope("https://www.googleapis.com/auth/user.phonenumbers.read");
$client->addScope("https://www.googleapis.com/auth/user.addresses.read");
$client->addScope("email");
$client->addScope("profile");

// 2. Tạo URL xác thực và chuyển hướng người dùng
header('Location: ' . $client->createAuthUrl());
exit;
?>
