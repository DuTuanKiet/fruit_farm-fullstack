<?php session_start(); // Bắt đầu session để quản lý trạng thái đăng nhập ?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fruit Farm</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <link rel="stylesheet" href="/fruitfarm/css/style.css" />
    <link rel="stylesheet" href="/fruitfarm/css/chitietsp.css" /> 
    <link rel="stylesheet" href="/fruitfarm/css/phanhoi.css" /> 

    </head>
  <body>

    <header>
      <nav class="navbar section-content">
        <a href="/fruitfarm/index.php" class="nav-logo">
          <h2 class="logo-text">🥑Fruit Farm</h2>
        </a>

        <ul class="nav-menu main-menu mobile-only">
          <li class="nav-item mobile-only">
            <button id="menu-close-button" class="close-btn">✖</button>
          </li>
          <li class="nav-item">
            <a href="/fruitfarm/index.php" class="nav-link">Home</a>
          </li>
          <li class="nav-item">
            <a href="/fruitfarm/sanpham.php" class="nav-link">Menu</a>
          </li>
          <li class="nav-item">
            <a href="/fruitfarm/index.php#contact" class="nav-link">Contact</a>
          </li>
        </ul>

        <ul class="nav-menu utilities">
          <li class="nav-item search-box">
            <form action="search.php" method="get">
              <input type="text" name="q" placeholder="Tìm sản phẩm..." />
              <button type="submit"><i class="fa fa-search"></i></button>
            </form>
          </li>
          <li class="nav-item cart">
            <a href="/fruitfarm/giohang.php" class="nav-link">
              <i class="fa fa-shopping-bag"></i>
              Cart
              <span class="cart-count">0</span>
            </a>
          </li>
          <li class="nav-item user-info">
    <?php $isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true; ?>

    <span class="welcome-message user-header">
        <?php if ($isLoggedIn): ?>
            Xin chào, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        <?php endif; ?>
    </span>

        <a href="/fruitfarm/php/logout.php" class="logout-btn" style="display: <?php echo $isLoggedIn ? 'inline-block' : 'none'; ?>;">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>

        <button class="login-btn" style="display: <?php echo $isLoggedIn ? 'none' : 'inline-block'; ?>;">LOG IN</button>
        </li>
        </ul>

        <button id="menu-open-button" class="fas fa-bars mobile-only"></button>
      </nav>
    </header>

    <div class="blur-bg-overplay"></div>
    <div class="form-popup">
      <span class="close-btn material-symbols-rounded">close</span>
      
      <div class="form-box login">
        <div class="form-details">
          <h2>Welcome Back</h2>
          <p>Please log in using your personal information to stay connected with us.</p>
        </div>
        <div class="form-content">
          <h2>LOG IN</h2>
          <form id="loginForm">
            <div class="input-field">
              <input type="email" name="username" required />
              <label>Email</label>
            </div>
            <div class="input-field">
              <input type="password" name="password" required />
              <label>Password</label>
            </div>
            <a href="#" class="forgot-pass">Forgot password?</a>
            <button type="submit">Log In</button>
          </form>
          <div class="bottom-link">
            Don't have an account?
            <a href="#" id="signup-link">Sign Up</a>
          </div>
        </div>
      </div>

      <div class="form-box signup">
        <div class="form-details">
          <h2>Create Account</h2>
          <p>To become a part of our community, please sign up using your personal information.</p>
        </div>
        <div class="form-content">
          <h2>SIGN UP</h2>
          <form id="registerForm">
            <div class="input-field">
              <input type="text" name="username" required />
              <label>Username</label>
            </div>
            <div class="input-field">
              <input type="email" name="email" />
              <label>Email (optional)</label>
            </div>
            <div class="input-field">
              <input type="password" name="password" required />
              <label>Password</label>
            </div>
            <div class="policy-text">
              <input type="checkbox" id="policy" required />
              <label for="policy">I agree to the <a href="#">Terms & Conditions</a></label>
            </div>
            <button type="submit">Sign Up</button>
          </form>
          <div class="bottom-link">
            Already have an account?
            <a href="#" id="login-link">Login</a>
          </div>
        </div>
      </div>
    </div>