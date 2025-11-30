<?php
// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../backend/core/config.php');

// Lấy sản phẩm nổi bật
$products = [];
$sql = "SELECT id, name, description, image_url, stock FROM products WHERE is_featured = 1 ORDER BY id DESC LIMIT 8";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
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

// Lấy dữ liệu đánh giá từ db
$testimonials = [];
$sql = "SELECT name, user_image, feedback, rating, created_at 
        FROM testimonials 
        ORDER BY created_at DESC 
        LIMIT 10";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $testimonials = $result->fetch_all(MYSQLI_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fruit Farm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Symbols+Rounded:opsz,wght, FILL, GRAD@48,400,0,0"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css" />
  </head>
  <body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
      <!-- Hero section -->
      <section class="hero-section">
        <div class="section-content">
          <div class="hero-details">
            <h2 class="title">Trái cây tươi ngon nhất</h2>
            <h3 class="subtile">
              Hãy thêm khỏe mạnh với những loại trái cây tự nhiên! 🍇🍎
            </h3>
            <p class="description">
              Chào mừng bạn đến với Fruit Farm, nơi mỗi trái cây đều được chọn lựa cẩn thận và mỗi miếng cắn đều tràn đầy sự tươi mới và niềm vui.
            </p>
            <div class="buttons">
              <a href="#menu" class="button oder-now">Mua ngay</a>
              <a href="#contact" class="button contact-us">Liên hệ</a>
            </div>
          </div>
          <div class="hero-image-wrapper">
            <img src="<?= BASE_URL ?>public/assets/images/Fruitfarm-logo.png"
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
            <img src="<?= BASE_URL ?>public/assets/images/logo-about.avif" alt="About" class="about-image" />
          </div>
          <div class="about-details">
            <h2 class="section-title">Về chúng tôi - Fruit Farm</h2>
            <p class="text">
              Chào mừng bạn đến với Fruit Farm – nơi thân thiện mang đến những loại trái cây tươi ngon và tràn đầy năng lượng tích cực.
              Hãy thư giãn, khám phá và tận hưởng – bởi vì mỗi ngày tuyệt vời đều bắt đầu bằng trái cây tươi. 🍎
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
  <h2 class="section-title">Sản phẩm nổi bật</h2>
  <ul class="menu-list">
    <?php foreach ($products as $product):
      $stock = intval($product['stock']);
      $is_out_of_stock = $stock <= 0;
    ?>
    <li class="menu-item <?= $is_out_of_stock ? 'out-of-stock' : '' ?>">
      <a href="chitietsp.php?id=<?= $product['id']; ?>" class="product-link">
        <img src="<?= BASE_URL . htmlspecialchars($product['image_url']); ?>" 
             alt="<?= htmlspecialchars($product['name']); ?>" class="menu-image">
        <div class="menu-content">
          <h3 class="name"><?= htmlspecialchars($product['name']); ?></h3>
          <p class="text"><?= htmlspecialchars($product['description']); ?></p>
        </div>
      </a>
      <div class="menu-item-actions">
           <?php if ($is_out_of_stock): ?>
  <button class="add-to-cart-btn btn-disabled"><i class="fa fa-shopping-cart"></i> Hết hàng</button>
  <button class="buy-now-btn btn-disabled">Mua ngay</button>
<?php else: ?>
  <button class="add-to-cart-btn btn-sm requires-login" 
          data-id="<?= $product['id']; ?>" 
          data-stock="<?= $product['stock']; ?>">
      <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
  </button>
  <button class="buy-now-btn btn-sm requires-login" 
          data-id="<?= $product['id']; ?>" 
          data-stock="<?= $product['stock']; ?>">
      Mua Ngay
  </button>
<?php endif; ?>
          </div>
    </li>
    <?php endforeach; ?>
  </ul>
  <div class="see-more">
    <a href="sanpham.php" class="see-more-btn">Xem thêm sản phẩm</a>
  </div>
</section>

        <!-- Testimonials section -->
  <section class="testimonials-section" id="testimonials">
    <h2 class="section-title">Phản hồi từ khách hàng</h2>
    <div class="section-content">
      <div class="slider-container swiper">
       <ul class="testimonials-list swiper-wrapper">
  <?php if (!empty($testimonials)): ?>
    <?php foreach ($testimonials as $t): ?>
      <li class="testimonial swiper-slide">
        <img 
          src="<?= $t['user_image'] 
              ? htmlspecialchars($t['user_image']) 
              : 'https://ui-avatars.com/api/?name=' . urlencode($t['name']) . '&background=random&size=128'; ?>" 
          alt="<?= htmlspecialchars($t['name']); ?>" 
          class="user-image" 
        />

        <h3 class="name"><?= htmlspecialchars($t['name']); ?></h3>
        <div class="rating">
          <?php 
            $stars = (int)$t['rating'];
            for ($i = 1; $i <= 5; $i++) {
              echo $i <= $stars ? '<i class="fa fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
            }
          ?>
        </div>
        <p class="feedback"><?= htmlspecialchars($t['feedback']); ?></p>
      </li>
    <?php endforeach; ?>
  <?php else: ?>
    <li class="testimonial swiper-slide">
      <p class="feedback">Hiện chưa có phản hồi nào. Hãy là người đầu tiên chia sẻ cảm nhận của bạn!</p>
    </li>
  <?php endif; ?>
</ul>

        <div class="swiper-pagination"></div>
        <div class="swiper-slide-button swiper-button-prev"></div>
        <div class="swiper-slide-button swiper-button-next"></div>
      </div>
    </div>
  </section>


<!-- GALLERY SECTION -->
<section class="gallery-section" id="gallery">
  <div class="gallery-header">
    <h2 class="section-title">Triển Lãm Trái Cây Nghệ Thuật Việt Nam</h2>
  </div>

  <div class="gallery-container">
    <ul class="gallery-list">
      <?php 
        $fruits = [
  [
    "img" => "saurieng-gallery.jpg",
    "name" => "Sầu Riêng",
    "desc" => "Sầu riêng Việt Nam – đặc biệt là giống Ri6 và Monthong – được xem là biểu tượng của vùng nhiệt đới Đông Nam Á. Với mùi thơm đặc trưng, vị ngọt béo và lớp cơm vàng mềm mịn, sầu riêng đã chinh phục nhiều thực khách quốc tế. Năm 2023, trái sầu riêng Việt chính thức có mặt tại Trung Quốc, Singapore và Hàn Quốc, trở thành một trong những sản phẩm nông nghiệp xuất khẩu giá trị cao nhất của Việt Nam."
  ],
  [
    "img" => "thanhlong-gallery.jpg",
    "name" => "Thanh Long",
    "desc" => "Thanh long Bình Thuận được mệnh danh là 'ngọc rồng của miền cát trắng'. Loại quả này có vỏ hồng rực rỡ, ruột trắng hoặc đỏ, vị ngọt thanh mát. Đây là một trong những loại trái cây đầu tiên của Việt Nam đạt chứng nhận GlobalG.A.P và được xuất khẩu sang hơn 40 quốc gia, trong đó có Nhật Bản, EU và Hoa Kỳ. Thanh long còn được xem là biểu tượng của sức sống, sự may mắn và thịnh vượng."
  ],
  [
    "img" => "xoaicatloc-gallery.jpg",
    "name" => "Xoài Cát Hòa Lộc",
    "desc" => "Xoài Cát Hòa Lộc – niềm tự hào của vùng sông nước Tiền Giang – nổi tiếng với lớp vỏ vàng óng, thịt dày, hạt mỏng, vị ngọt đậm đà và hương thơm tự nhiên. Nhiều tạp chí ẩm thực Nhật Bản và Hàn Quốc từng giới thiệu đây là một trong những giống xoài ngon nhất thế giới. Hiện nay, xoài Cát Hòa Lộc đã được xuất khẩu chính ngạch sang Nhật Bản, Úc và New Zealand."
  ],
  [
    "img" => "vaithieubacgiang-gallery.jpg",
    "name" => "Vải Thiều Bắc Giang",
    "desc" => "Vải thiều Bắc Giang là 'viên ngọc đỏ' của vùng trung du miền Bắc, với lớp vỏ đỏ hồng, cùi trắng giòn, ngọt thanh và hương thơm nhẹ. Trái vải từng được BBC và Reuters ca ngợi là đặc sản Việt Nam mang tầm quốc tế khi xuất hiện tại các siêu thị ở Anh, Pháp và Nhật Bản. Mỗi mùa thu hoạch, vải thiều không chỉ là nông sản mà còn là biểu tượng của sự thịnh vượng và niềm tự hào văn hóa địa phương."
  ],
  [
    "img" => "nhanlonghungyen-gallery.jpg",
    "name" => "Nhãn Lồng Hưng Yên",
    "desc" => "Nhãn lồng Hưng Yên được mệnh danh là 'vua của các loại nhãn' bởi vị ngọt sắc, cùi dày và hạt nhỏ. Từng được triều đình phong kiến dùng làm vật tiến vua, nay loại quả này đã được xuất khẩu sang Mỹ, Úc và EU. Với màu sắc tự nhiên và hương vị đậm đà, nhãn lồng không chỉ là sản vật trứ danh mà còn là biểu tượng cho sự tinh tế trong ẩm thực Việt Nam."
  ],
  [
    "img" => "buoidaxanh-gallery.jpg",
    "name" => "Bưởi Da Xanh",
    "desc" => "Bưởi Da Xanh Bến Tre – một trong những giống bưởi ngon nhất châu Á – có vỏ mỏng xanh tươi, tép hồng mọng nước, vị ngọt thanh pha chút chua nhẹ. Reuters từng gọi đây là 'ngôi sao mới của trái cây nhiệt đới Việt Nam'. Với hàm lượng vitamin C cao và hương vị độc đáo, bưởi Da Xanh hiện là sản phẩm xuất khẩu chủ lực sang Mỹ, Canada và châu Âu."
  ]
];

        foreach ($fruits as $fruit): 
      ?>
        <li class="gallery-item" 
            data-name="<?= $fruit['name'] ?>" 
            data-desc="<?= $fruit['desc'] ?>" 
            data-img="<?= BASE_URL ?>public/assets/images/<?= $fruit['img'] ?>">
          <div class="gallery-frame">
            <img src="<?= BASE_URL ?>public/assets/images/<?= $fruit['img'] ?>" alt="<?= $fruit['name'] ?>" class="gallery-image" />
            <div class="gallery-caption"><?= $fruit['name'] ?></div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Popup -->
<div class="gallery-popup" id="galleryPopup">
  <div class="gallery-popup-content">
    <span class="close-popup material-symbols-rounded" id="closeGallery">close</span>

    <div class="popup-left">
      <img id="popupImage" src="" alt="gallery preview" />
    </div>

    <div class="popup-right">
      <h3 id="popupTitle"></h3>
      <p id="popupDesc" class="short-desc"></p>
      <button id="showMoreBtn" class="show-more">Xem thêm</button>
    </div>
  </div>
</div>
</section>

      <section class="contact-section" id="contact">
    <div class="section-content">
        <h2 class="section-title">Liên hệ với chúng tôi</h2>

        <div class="contact-container">
            
            <div class="contact-info-panel">
                <div class="contact-item">
                    <i class="fa fa-map-marker-alt"></i>
                    <span>256 Nguyễn Văn Cừ, Quận Ninh Kiều, Thành phố Cần Thơ</span>
                </div>
                <div class="map-responsive-wrapper">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.6370299111722!2d105.76547037487202!3d10.046780690061032!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0880f08006ffb%3A0x9a745510330faf4e!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBL4bu5IHRodeG6rXQgLSBDw7RuZyBuZ2jhu4cgQ-G6p24gVGjGoQ!5e0!3m2!1svi!2s!4v1760115171218!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="contact-item">
                    <i class="fa fa-envelope"></i>
                    <span>dukiet205@gmail.com</span>
                </div>
                <div class="contact-item">
                    <i class="fa fa-phone"></i>
                    <span>0819767357</span>
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
<div class="toast-container" id="toast-container"></div>
<!--Xử lí thông báo vô hiệu tài khoản từ gg callback-->
<?php
if (isset($_GET['error']) && $_GET['error'] === 'disabled_account') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('Tài khoản của bạn đã bị vô hiệu hóa. Không thể đăng nhập!', 'error');
        });
    </script>";
}
?>
      <!-- Back to Top Button -->
      <button id="backToTop" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
      </button>
      <!-- Footer section -->
      <?php include __DIR__ . '/includes/footer.php'; ?>
    </main>
    
  </body>
  
</html>
