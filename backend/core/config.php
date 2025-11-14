<?php
// BASE_URL trỏ tới public/ để trình duyệt có thể truy cập
define('BASE_URL', 'http://localhost:8080/fruitfarm/'); 

// Đường dẫn tuyệt đối trên server
define('BASE_PATH', dirname(__DIR__, 2)); // C:\xampp\htdocs\fruitfarm
define('BACKEND_PATH', BASE_PATH . '/backend');
define('PUBLIC_PATH', BASE_PATH . '/public');

// DB
define('DB_PATH', BACKEND_PATH . '/core/db_connect.php');
if (file_exists(DB_PATH)) {
    require_once DB_PATH;
} else {
    die("❌ Không tìm thấy file kết nối CSDL: " . DB_PATH);
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
define('GOOGLE_CLIENT_ID', '80390356779-ri3d3ovtmplgcgovfmd0pfch2tt7dub6.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');