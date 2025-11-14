<?php 
require_once __DIR__ . '/../../backend/core/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fruit Farm</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <!-- Google Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Custom CSS -->
   <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">

    <script>
      const IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false'; ?>;
    </script>
</head>
<body>
    <header>
    <nav class="navbar section-content">
        <a href="<?= BASE_URL ?>public/index.php" class="nav-logo"><h2 class="logo-text">🥑Fruit Farm</h2></a>
        <ul class="nav-menu main-menu mobile-only">
            <li class="nav-item mobile-only"><button id="menu-close-button" class="close-btn">✖</button></li>
            <li class="nav-item"><a href="<?= BASE_URL ?>public/index.php" class="nav-link">Trang chủ</a></li>
            <li class="nav-item"><a href="<?= BASE_URL ?>public/sanpham.php" class="nav-link">Sản phẩm</a></li>
            <li class="nav-item"><a href="<?= BASE_URL ?>public/index.php#contact" class="nav-link">Liên hệ</a></a></li>
        </ul>
        <ul class="nav-menu utilities">
            <li class="nav-item search-box">
                <form action="<?= BASE_URL ?>public/search.php" method="get" autocomplete="off">
                <input type="text" id="search-input" name="q" placeholder="Tìm trái cây tươi..." />
                <button type="submit"><i class="fa fa-search"></i></button>
                </form>
               <div id="search-results-box"></div>
            </li>
            
            <li class="nav-item cart cart-dropdown-menu">
                <button class="cart-icon-btn nav-link cart-dropdown-toggle" title="Giỏ hàng của tôi">
                    <i class="fa fa-shopping-bag"></i>Giỏ hàng<span class="cart-count">0</span>
                </button>
                
                <div id="cart-dropdown" class="dropdown-menu">
                    <div class="dropdown-header">
                        <span class="username">Giỏ hàng của bạn</span>
                    </div>
                    <a href="<?= BASE_URL ?>public/giohang.php" class="dropdown-item">
                        <i class="fa fa-shopping-cart"></i>
                        <span>Xem Giỏ hàng</span>
                    </a>
                    <a href="<?= BASE_URL ?>public/xemdonhang.php" class="dropdown-item">
                        <i class="fa fa-box-open"></i>
                        <span>Xem Đơn hàng</span>
                    </a>
                </div>
            </li>
            <li class="nav-item user-profile-menu">
                <button class="profile-icon-btn" style="display: <?= $isLoggedIn ? 'flex' : 'none'; ?>;">
                    <i class="fa-solid fa-circle-user"></i>
                </button>
                <div id="profile-dropdown" class="dropdown-menu">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown-header">
                            <span class="username">Xin chào, <?= htmlspecialchars($username) ?> 👋</span>
                        </div>
        
                        <a href="<?= BASE_URL ?>backend/auth/logout.php" class="dropdown-item">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Đăng xuất</span>
                        </a>
                    <?php endif; ?>
                </div>
            </li>

            <li class="nav-item">
                <button class="login-btn" style="display: <?= $isLoggedIn ? 'none' : 'inline-block'; ?>;">Đăng nhập</button>
            </li>
        </ul>
        <button id="menu-open-button" class="fas fa-bars mobile-only"></button>
    </nav>
</header>

    <div class="blur-bg-overplay"></div>
    <div class="form-popup">
        <span class="close-btn material-symbols-rounded">close</span>

        <!-- LOGIN BOX -->
        <div class="form-box login">
            <div class="form-details">
                <h2>Welcome Back</h2>
                <p>Please log in using your personal information to stay connected with us.</p>
            </div>
            <div class="form-content">
                <h2>ĐĂNG NHẬP</h2>
                <form id="loginForm">
                    <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                    <div class="input-field"><input type="text" name="username" required /><label>Email hoặc Họ tên</label></div>
                    <div class="input-field"><input type="password" name="password" required /><label>Mật khẩu</label></div>
                    <a href="#" class="forgot-pass">Quên mật khẩu?</a>
                    <button type="submit">Đăng nhập</button>
                    <div class="middle-text"><hr><span class="or-text">hoặc</span></div>
                    <a href="<?= BASE_URL ?>backend/google/google-login.php" class="btn-google">
                        <img src="<?= BASE_URL ?>public/assets/images/login-google-logo.png" alt="Google logo">Đăng nhập với Google
                    </a>
                </form>
                <div class="bottom-link">Bạn chưa có tài khoản?<a href="#" id="signup-link">Đăng ký</a></div>
            </div>
        </div>

        <!-- SIGNUP BOX -->
        <div class="form-box signup">
            <div class="form-details">
                <h2>Join Us</h2>
                <p>Create your account to start enjoying Fruit Farm!</p>
            </div>
            <div class="form-content">
                <h2>ĐĂNG KÝ</h2>
                <form id="signupForm">
                    <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                    <div class="input-field"><input type="text" name="username" required /><label>Họ tên</label></div>
                    <div class="input-field"><input type="email" name="email" required /><label>Email</label></div>
                    <div class="input-field"><input type="password" name="password" required /><label>Mật khẩu</label></div>
                    <div class="input-field"><input type="password" name="confirm_password" required /><label>Xác nhận mật khẩu</label></div>
                    <button type="submit">Đăng ký</button>
                    <div class="middle-text"><hr><span class="or-text">hoặc</span></div>
                    <a href="<?= BASE_URL ?>backend/google/google-login.php" class="btn-google">
                        <img src="<?= BASE_URL ?>public/assets/images/login-google-logo.png" alt="Google logo">Đăng nhập với Google
                    </a>
                </form>
                <div class="bottom-link">Bạn đã có tài khoản?<a href="#" id="login-link">Đăng nhập</a></div>
            </div>
        </div>
    </div>
</body>
</html>
