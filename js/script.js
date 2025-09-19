const navbarMenu = document.querySelector(".navbar .main-menu");
const menuOpenButton = document.querySelector("#menu-open-button");
const menuCloseButton = document.querySelector("#menu-close-button");
const showPopupBtn = document.querySelector(".login-btn");
const formPopup = document.querySelector(".form-popup");
const hidePopupBtn = document.querySelector(".form-popup .close-btn");
const loginSignupLink = document.querySelectorAll(".form-box .bottom-link a");
const productsWrapper = document.querySelector(".product-list");
const cartContainer = document.querySelector(".cart-container");
const cartBody = document.getElementById("cart-body");
const grandTotalEl = document.getElementById("grand-total");
const cartCountEl = document.querySelector(".cart .cart-count");
const registerForm = document.getElementById("registerForm");
const loginForm = document.getElementById("loginForm");
const backToTopBtn = document.getElementById("backToTop");
const userHeader = document.querySelector(".user-header");
const logoutBtn = document.querySelector(".logout-btn");
const checkoutBtn = document.getElementById("checkout-btn"); // Đổi selector thành ID
const testimonialsSliderEl = document.querySelector(
  ".testimonials-section .swiper"
);

window.addEventListener("resize", () => {
  if (window.innerWidth > 992) {
    // Nhớ thay đổi con số 992
    document.body.classList.remove("show-mobile-menu");
  }
});
/** Lấy thông tin session của người dùng từ backend. */
async function getSessionUser() {
  const res = await fetch("/fruitfarm/php/get_session_user.php");
  return await res.json();
}

/** Cập nhật header (Xin chào user / Nút Login) dựa vào session. */
async function updateHeaderUser() {
  if (!userHeader) return;
  const user = await getSessionUser();
  if (user.username) {
    userHeader.textContent = `Xin chào, ${user.username}`;
    if (showPopupBtn) showPopupBtn.style.display = "none";
    if (logoutBtn) logoutBtn.style.display = "block";
  } else {
    userHeader.textContent = "";
    if (showPopupBtn) showPopupBtn.style.display = "block";
    if (logoutBtn) logoutBtn.style.display = "none";
  }
}

/** Cập nhật số lượng sản phẩm trên icon giỏ hàng ở header. */
async function updateCartIconCount() {
  if (!cartCountEl) return;
  try {
    const res = await fetch("/fruitfarm/php/cart/get_cart.php");
    const data = await res.json();
    cartCountEl.textContent = data.cartItems ? data.cartItems.length : 0;
  } catch (error) {
    console.error("Lỗi cập nhật icon giỏ hàng:", error);
    cartCountEl.textContent = "!";
  }
}

/** Gửi yêu cầu cập nhật (thêm/sửa/xóa) giỏ hàng lên backend. */
async function updateCartBackend(productId, quantity, showMessage = false) {
  await fetch("/fruitfarm/php/cart/add_to_cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ product_id: productId, quantity }),
  });

  // Tải lại giao diện giỏ hàng (nếu có) và icon
  loadCart();
  updateCartIconCount();

  if (showMessage) {
    alert("Đã thêm sản phẩm vào giỏ hàng!");
  }
}

// =============================================================================
// PHẦN 3: CÁC HÀM CHỨC NĂNG CHÍNH (MAIN FEATURE FUNCTIONS)
// =============================================================================

/** Tải và hiển thị chi tiết các sản phẩm trong giỏ hàng. */
async function loadCart() {
  if (!cartBody) return; // Chỉ chạy trên trang giỏ hàng
  try {
    const res = await fetch("/fruitfarm/php/cart/get_cart.php");
    const data = await res.json();
    cartBody.innerHTML = "";

    if (data.cartItems && data.cartItems.length > 0) {
      data.cartItems.forEach((item) => {
        const row = document.createElement("tr");
        row.innerHTML = `
                    <td><img src="${item.image_url}" alt="${
          item.name
        }" class="cart-product-img" /></td>
                    <td>${item.name}</td>
                    <td>${item.price.toLocaleString("vi-VN")}₫</td>
                    <td>
                        <div class="quantity-control">
                            <button class="decrease-btn" data-id="${
                              item.product_id
                            }">-</button>
                            <input type="number" value="${
                              item.quantity
                            }" min="1" readonly />
                            <button class="increase-btn" data-id="${
                              item.product_id
                            }">+</button>
                        </div>
                    </td>
                    <td class="total-price">${item.subtotal.toLocaleString(
                      "vi-VN"
                    )}₫</td>
                    <td><button class="remove-btn" data-id="${
                      item.product_id
                    }"><i class="fa fa-trash"></i></button></td>
                `;
        cartBody.appendChild(row);
      });
    } else {
      cartBody.innerHTML =
        '<tr><td colspan="6" style="text-align: center; padding: 20px;">Giỏ hàng của bạn đang trống.</td></tr>';
    }

    if (grandTotalEl) {
      grandTotalEl.textContent = data.grandTotal.toLocaleString("vi-VN") + "₫";
    }
  } catch (error) {
    console.error("Lỗi khi tải giỏ hàng:", error);
  }
}

/** Tải danh sách sản phẩm theo trang và hiển thị. */
async function loadProducts(page = 1) {
  if (!productsWrapper) return;
  try {
    const res = await fetch(`/fruitfarm/php/cart/products.php?page=${page}`);
    const data = await res.json();
    productsWrapper.innerHTML = "";

    if (data.products && data.products.length > 0) {
      data.products.forEach((p) => {
        productsWrapper.innerHTML += `
                    <div class="product-card-container">
                        <a href="chitietsp.php?id=${p.id}" class="product-link">
                            <div class="product-card">
                                <img src="${p.image_url}" alt="${p.name}" />
                                <h3>${p.name}</h3>
                                <p>${p.price.toLocaleString("vi-VN")}₫</p>
                            </div>
                        </a>
                        <div class="product-card-actions">
                            <button class="btn-sm add-to-cart-btn" data-id="${
                              p.id
                            }"><i class="fa fa-shopping-cart"></i> Thêm vào giỏ</button>
                            <button class="btn-sm buy-now-btn" data-id="${
                              p.id
                            }">Mua Ngay</button>
                        </div>
                    </div>`;
      });
      updatePagination(data.totalPages, data.currentPage);
    } else {
      productsWrapper.innerHTML = "<p>Không có sản phẩm nào.</p>";
    }
  } catch (error) {
    console.error("Lỗi khi tải sản phẩm:", error);
  }
}

/** Cập nhật các nút phân trang. */
function updatePagination(totalPages, currentPage) {
  const paginationWrapper = document.querySelector(".pagination");
  if (!paginationWrapper) return;
  paginationWrapper.innerHTML = "";
  for (let i = 1; i <= totalPages; i++) {
    paginationWrapper.innerHTML += `<a href="#" class="page-link ${
      i == currentPage ? "active" : ""
    }" data-page="${i}">${i}</a>`;
  }
}

// =============================================================================
// PHẦN 4: THIẾT LẬP CÁC TRÌNH NGHE SỰ KIỆN (EVENT LISTENERS SETUP)
// =============================================================================

function setupEventListeners() {
  // --- Sự kiện cho các hành động trên toàn trang (Event Delegation) ---
  document.addEventListener("click", async (event) => {
    const target = event.target;

    // Xử lý nút "Thêm vào giỏ hàng"
    if (target.closest(".add-to-cart-btn")) {
      const productId = target.closest(".add-to-cart-btn").dataset.id;
      updateCartBackend(productId, 1, true); // Thêm 1 sản phẩm và hiện thông báo
    }

    // Xử lý nút "Mua Ngay"
    if (target.closest(".buy-now-btn")) {
      event.preventDefault();
      const productId = target.closest(".buy-now-btn").dataset.id;
      const res = await fetch("/fruitfarm/php/cart/buy_now_process.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ product_id: productId }),
      });
      const data = await res.json();
      if (data.success) window.location.href = data.redirect;
      else alert(data.message || "Có lỗi xảy ra.");
    }

    // Xử lý nút phân trang
    if (target.closest(".page-link")) {
      event.preventDefault();
      const page = target.closest(".page-link").dataset.page;
      loadProducts(page);
    }
  });

  // --- Sự kiện cho các hành động trong giỏ hàng ---
  if (cartContainer) {
    cartContainer.addEventListener("click", (event) => {
      const target = event.target;
      const increaseBtn = target.closest(".increase-btn");
      const decreaseBtn = target.closest(".decrease-btn");
      const removeBtn = target.closest(".remove-btn");

      if (increaseBtn) {
        const productId = increaseBtn.dataset.id;
        const newQuantity =
          parseInt(increaseBtn.previousElementSibling.value) + 1;
        updateCartBackend(productId, newQuantity);
      } else if (decreaseBtn) {
        const productId = decreaseBtn.dataset.id;
        const newQuantity = parseInt(decreaseBtn.nextElementSibling.value) - 1;
        if (newQuantity >= 0) updateCartBackend(productId, newQuantity);
      } else if (removeBtn) {
        const productId = removeBtn.dataset.id;
        if (confirm("Bạn có chắc muốn xóa sản phẩm này?"))
          updateCartBackend(productId, 0);
      }
    });
  }

  // --- Logout ---
  if (logoutBtn) {
    logoutBtn.addEventListener("click", async () => {
      await fetch("/fruitfarm/php/auth/logout.php");
      window.location.reload();
    });
  }

  // --- Login/Signup Popup ---
  if (showPopupBtn)
    showPopupBtn.addEventListener("click", () =>
      document.body.classList.add("show-popup")
    );
  if (hidePopupBtn)
    hidePopupBtn.addEventListener("click", () =>
      document.body.classList.remove("show-popup")
    );
  loginSignupLink.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      formPopup.classList.toggle("show-signup");
    });
  });

  // --- Form Đăng ký ---
  if (registerForm) {
    registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const res = await fetch("/fruitfarm/php/auth/signup.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          username: registerForm.username.value,
          email: registerForm.email.value,
          password: registerForm.password.value,
        }),
      });
      const data = await res.json();
      alert(data.message);
      if (data.success) formPopup.classList.remove("show-signup");
    });
  }

  // --- Form Đăng nhập ---
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const res = await fetch("/fruitfarm/php/auth/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          username: loginForm.username.value,
          password: loginForm.password.value,
        }),
      });
      const data = await res.json();
      if (data.success) {
        alert(data.message);
        window.location.href = data.redirect || window.location.href; // Chuyển hướng hoặc tải lại trang
      } else {
        alert(data.message);
      }
    });
  }

  // --- Nút Thanh toán ---
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", async () => {
      const res = await fetch("/fruitfarm/php/cart/checkout.php", {
        method: "POST",
      });
      const data = await res.json();
      if (data.status === "success") {
        alert(
          `Đặt hàng thành công!\nTổng tiền: ${data.total_price.toLocaleString(
            "vi-VN"
          )}₫`
        );
        await loadCart();
        await updateCartIconCount();
      } else {
        alert(data.message);
      }
    });
  }

  // --- Nút Back to Top ---
  if (backToTopBtn) {
    window.addEventListener("scroll", () => {
      backToTopBtn.style.display = window.scrollY > 300 ? "flex" : "none";
    });
    backToTopBtn.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  // --- Mobile Menu ---
  if (menuOpenButton)
    menuOpenButton.addEventListener("click", () =>
      document.body.classList.toggle("show-mobile-menu")
    );
  if (menuCloseButton)
    menuCloseButton.addEventListener("click", () => menuOpenButton.click());

  // --- Slider ---
  if (testimonialsSliderEl) {
    new Swiper(testimonialsSliderEl, {
      loop: true,
      autoplay: { delay: 5000, disableOnInteraction: false },
      pagination: { el: ".swiper-pagination", clickable: true },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    });
  }
}

// =============================================================================
// PHẦN 5: KHỞI CHẠY (INITIALIZATION)
// =============================================================================

document.addEventListener("DOMContentLoaded", () => {
  // Chạy các hàm khởi tạo
  updateHeaderUser();
  updateCartIconCount();
  loadProducts(1);
  loadCart();

  // Kích hoạt tất cả các trình nghe sự kiện
  setupEventListeners();
});
