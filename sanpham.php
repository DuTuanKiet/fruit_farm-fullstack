<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sản phẩm - Fruit Farm</title>
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
    <link rel="stylesheet" href="css/sanpham.css" />
  </head>
  <body>
    <?php include 'header.php'; ?>

    <!-- Product Section -->
    <main>
      <section class="product-section">
        <h2 class="section-title">My Products</h2>
        <div class="product-list">
          
          </div>

        <!-- Pagination -->
        <div class="pagination">
          <a href="#" class="page-link active" data-page="1">1</a>
          <a href="#" class="page-link" data-page="2">2</a>
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
   <script src="/fruitfarm/js/script.js"></script>
  </body>
</html>
