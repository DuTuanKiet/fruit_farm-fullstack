<?php
require_once 'php/db_connect.php'; 

// Lấy 6 sản phẩm mới nhất từ CSDL để hiển thị ở "Our Menu"
$products = []; // Khởi tạo một mảng rỗng để chứa sản phẩm
// Trong file index.php
$sql = "SELECT id, name, description, image_url FROM products WHERE is_featured = 1 ORDER BY id DESC LIMIT 6";
$result = $conn->query($sql);
// Kiểm tra xem câu lệnh có chạy thành công và có trả về ít nhất một sản phẩm hay không.
if ($result && $result->num_rows > 0) {
    // Lấy tất cả các dòng kết quả vào một mảng
    $products = $result->fetch_all(MYSQLI_ASSOC);
}

// Bắt lỗi Fb
// var_dump($_POST);
// die();

// Xử lý khi người dùng gửi form phản hồi
if (isset($_POST['submit_feedback'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $_SESSION['feedback_status'] = ['type' => 'success', 'message' => 'Cảm ơn bạn! Chúng tôi đã nhận được phản hồi.'];
        } else {
            $_SESSION['feedback_status'] = ['type' => 'error', 'message' => 'Lỗi hệ thống, vui lòng thử lại sau.'];
        }
    } else {
        $_SESSION['feedback_status'] = ['type' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin.'];
    }
    
    header("Location: index.php#contact");
    exit();
}
?>


<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fruit Farm</title>
    <!-- Link font awesome for icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Material+Symbols+Rounded:opsz,wght, FILL, GRAD@48,400,0,0"
    />
    <!-- Link Swiper's CSS -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <?php include 'header.php'; ?>

    <main>
      <!-- Hero section -->
      <section class="hero-section">
        <div class="section-content">
          <div class="hero-details">
            <h2 class="title">Best Fruits</h2>
            <h3 class="subtile">
              Make your day healthier with our natural fruits!
            </h3>
            <p class="description">
              Welcome to Fruit Farm, where every fruit is handpicked with care
              and every bite is full of freshness and joy.
            </p>
            <div class="buttons">
              <a href="#menu" class="button oder-now">Oder now</a>
              <a href="#contact" class="button contact-us">Contact us</a>
            </div>
          </div>
          <div class="hero-image-wrapper">
            <img
              src="images/Fruitfarm-logo.png"
              alt="Hero Image"
              class="hero-image"
            />
          </div>
        </div>
      </section>

      <!-- About section -->
      <section class="about-section" id="about">
        <div class="section-content">
          <div class="about-image-wrapper">
            <img src="images/2.jpg" alt="About" class="about-image" />
          </div>
          <div class="about-details">
            <h2 class="section-title">About Us</h2>
            <p class="text">
              Welcome to Fruit Farm – your friendly place for fresh fruits and
              happy vibes. We love sharing nature’s sweetness through handpicked
              fruits full of flavor and care. Whether you’re here to find
              healthy snacks, treat your family, or simply enjoy the taste of
              freshness, we’ve got you covered. Sit back, explore, and enjoy –
              because every great day starts with fresh fruit.
            </p>
            <div class="social-link-list">
              <a href="#" class="social-link">
                <i class="fa-brands fa-facebook"></i>
              </a>

              <a href="#" class="social-link">
                <i class="fa-brands fa-instagram"></i>
              </a>

              <a href="#" class="social-link">
                <i class="fa-brands fa-youtube"></i>
              </a>
            </div>
          </div>
        </div>
      </section>

      <!-- Menu section-->
      <section class="menu-section" id="menu">
        <h2 class="section-title">Our Menu</h2>
        <div class="section-content">
          <ul class="menu-list">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <a href="chitietsp.php?id=<?php echo $product['id']; ?>" class="product-link">
                <li class="menu-item">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="menu-image" />
                    <div class="menu-content">
                        <h3 class="name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text"><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>

                    <div class="menu-item-overlay">
    <button class="btn-quick-action add-to-cart-btn" data-id="<?php echo $product['id']; ?>">
        <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
    </button>
</div>
                </li>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>
          <div class="see-more">
            <a href="sanpham.php" class="see-more-btn">Xem thêm sản phẩm</a>
          </div>
        </div>
      </section>

      <!-- Testimonials section -->
      <section class="testimonials-section" id="testimonials">
        <h2 class="section-title">Testimonials</h2>
        <div class="section-content">
          <div class="slider-container swiper">
              <ul class="testimonials-list swiper-wrapper">
                <li class="testimonial swiper-slide">
                  <img src="images/user1.jpg" alt="User1" class="user-image" />
                  <h3 class="name">Sarah Johnson</h3>
                  <i class="feedback">
                    This website is so easy to use and beautifully designed – I
                    can find everything I need in just a few clicks
                  </i>
                </li>

                <li class="testimonial swiper-slide">
                  <img src="images/user2.jpg" alt="User2" class="user-image" />
                  <h3 class="name">Marry</h3>
                  <i class="feedback">
                    I love how fast the site loads and how clear the information
                    is. Very professional!
                  </i>
                </li>

                <li class="testimonial swiper-slide">
                  <img src="images/user3.jpg" alt="User3" class="user-image" />
                  <h3 class="name">Emily</h3>
                  <i class="feedback">
                    Great experience! The layout is modern and user-friendly,
                    I’ll definitely come back.
                  </i>
                </li>

                <li class="testimonial swiper-slide">
                  <img src="images/user4.jpg" alt="User4" class="user-image" />
                  <h3 class="name">Taylor</h3>
                  <i class="feedback">
                    The content is well-organized and super helpful. It really
                    exceeded my expectations.
                  </i>
                </li>

                <li class="testimonial swiper-slide">
                  <img src="images/user5.jpg" alt="User5" class="user-image" />
                  <h3 class="name">Ariana</h3>
                  <i class="feedback">
                    Excellent design, smooth navigation, and trustworthy
                    information – highly recommended!
                  </i>
                </li>
              </ul>

              <div class="swiper-pagination"></div>
              <div class="swiper-slide-button swiper-button-prev"></div>
              <div class="swiper-slide-button swiper-button-next"></div>
            
          </div>
        </div>
      </section>

      <!-- Gallery section -->
      <section class="gallery-section" id="gallery">
        <h2 class="section-title">Gallery</h2>
        <div class="section-content">
          <ul class="gallery-list">
            <li class="gallery-item">
              <img
                src="images/nuts dry fruit_gallery.png"
                alt="nuts dry fruit"
                class="gallery-image"
              />
            </li>

            <li class="gallery-item">
              <img src="images/5.jpg" alt="" class="gallery-image" />
            </li>

            <li class="gallery-item">
              <img src="images/5.jpg" alt="" class="gallery-image" />
            </li>

            <li class="gallery-item">
              <img src="images/5.jpg" alt="" class="gallery-image" />
            </li>

            <li class="gallery-item">
              <img src="images/5.jpg" alt="" class="gallery-image" />
            </li>

            <li class="gallery-item">
              <img src="images/5.jpg" alt="" class="gallery-image" />
            </li>
          </ul>
        </div>
      </section>

      <section class="contact-section" id="contact">
    <div class="section-content">
        <h2 class="section-title">Contact Us</h2>

        <div class="contact-container">
            
            <div class="contact-info-panel">
                <div class="contact-item">
                    <i class="fa fa-map-marker-alt"></i>
                    <span>123 Campsite Avenue, Wilderness, CA 98765</span>
                </div>
                <div class="contact-item">
                    <i class="fa fa-envelope"></i>
                    <span>123@gmail.com</span>
                </div>
                <div class="contact-item">
                    <i class="fa fa-phone"></i>
                    <span>0123456789</span>
                </div>
                 <div class="contact-item">
                    <i class="fa fa-clock"></i>
                    <span>Thứ 2 - Thứ 6: 9:00 AM - 5:00 PM</span>
                </div>
            </div>

            <div class="contact-form-wrapper">
                <form action="index.php#contact" method="POST" class="contact-form">
                    
                    <?php if (isset($_SESSION['feedback_status'])): ?>
                        <div class="feedback-message <?php echo $_SESSION['feedback_status']['type']; ?>">
                            <?php echo $_SESSION['feedback_status']['message']; ?>
                        </div>
                        <?php unset($_SESSION['feedback_status']); ?>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Họ và tên</label>
                            <input type="text" id="name" name="name" class="form-input" placeholder="Tên của bạn" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-input" placeholder="Email của bạn" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
            <label for="subject">Chủ đề</label>
            <input type="text" id="subject" name="subject" class="form-input" placeholder="Chủ đề tin nhắn" required>
        </div>

                    <div class="form-group">
                        <label for="message">Nội dung tin nhắn</label>
                        <textarea id="message" name="message" rows="5" class="form-input" placeholder="Nội dung bạn muốn gửi..." required></textarea>
                    </div>
                    <button type="submit" name="submit_feedback" class="submit-button">Gửi Tin Nhắn</button>
                </form>
            </div>
            
        </div>
    </div>
</section>

      <!-- Back to Top Button -->
      <button id="backToTop" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
      </button>

      <!-- Footer section -->
      <footer class="footer-section">
        <div class="section-content">
          <p class="copyright-text">@ Fruit Farm</p>

          <div class="social-link-list">
            <a href="https://www.facebook.com/" class="social-link">
              <i class="fa-brands fa-facebook"></i>
            </a>

            <a href="#" class="social-link">
              <i class="fa-brands fa-instagram"></i>
            </a>

            <a href="#" class="social-link">
              <i class="fa-brands fa-youtube"></i>
            </a>
          </div>

          <p class="policy-text">
            <a href="#" class="policy-link">Privacy policy</a>
            <span class="separator">*</span>
            <a href="#" class="policy-link">Refund policy</a>
          </p>
        </div>
      </footer>
    </main>

    <!--Link Swiper script-->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="js/script.js"></script>
  </body>
</html>
