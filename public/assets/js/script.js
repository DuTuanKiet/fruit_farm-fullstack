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
const registerForm = document.getElementById("signupForm");
const loginForm = document.getElementById("loginForm");
const backToTopBtn = document.getElementById("backToTop");
const userHeader = document.querySelector(".user-header");
const logoutBtn = document.querySelector(".logout-btn");
const checkoutBtn = document.getElementById("checkout-btn"); 
const testimonialsSliderEl = document.querySelector(
  ".testimonials-section .swiper"
);
const feedbackContainer = document.querySelector('.feedback-container');
const feedbackItems = document.querySelectorAll('.feedback-item');
const feedbackPlaceholder = document.getElementById('feedback-placeholder');
const feedbackContent = document.getElementById('feedback-content');
const detailSubject = document.getElementById('detail-subject');
const detailName = document.getElementById('detail-name');
const detailEmail = document.getElementById('detail-email');
const detailBody = document.getElementById('detail-body');
const btnReply = document.getElementById('btn-reply');
const btnDelete = document.getElementById('btn-delete');
const searchInput = document.getElementById("search-input");
const searchResultsBox = document.getElementById("search-results-box");
let searchTimeout;
const profileIcon = document.querySelector(".profile-icon-btn");
const dropdownMenu = document.getElementById("profile-dropdown");
const loginBtn = document.querySelector(".login-btn");
let revenueChartInstance = null;
let isReportInitialized = false;
const galleryItems = document.querySelectorAll(".gallery-item");
const popup = document.getElementById("galleryPopup");
const popupImg = document.getElementById("popupImage");
const popupTitle = document.getElementById("popupTitle");
const popupDesc = document.getElementById("popupDesc");
const showMoreBtn = document.getElementById("showMoreBtn");
const closeGallery = document.getElementById("closeGallery");
// Biến lưu trữ trạng thái bộ lọc hiện tại (GLOBAL STATE)
let currentCategory = 'all'; 
let currentPriceFilter = 'all'; 
let currentPage = 1;

// =======================================================
// === LOGIC XỬ LÝ ĐÓNG MENU KHI THAY ĐỔI KÍCH THƯỚC ===
// =======================================================
function checkDesktopView() {
    if (!navbarMenu) return; 

    if (window.innerWidth > 992) { 
        navbarMenu.classList.remove("show"); 
        navbarMenu.removeAttribute('style'); 
    } 
}

window.addEventListener('resize', checkDesktopView);
checkDesktopView();

// Khi click vào từng ảnh gallery
if (galleryItems.length > 0 && popup && popupImg && popupTitle && popupDesc) {
  galleryItems.forEach((item) => {
  item.addEventListener("click", () => {
    const imgSrc = item.getAttribute("data-img");
    const title = item.getAttribute("data-name");
    const desc = item.getAttribute("data-desc");

    popupImg.src = imgSrc;
    popupTitle.textContent = title;
    popupDesc.textContent = desc;
    popupDesc.classList.remove("show");

    showMoreBtn.style.display = "inline-block"; 
    showMoreBtn.textContent = "Xem thêm";

    popup.classList.add("active");
    document.body.style.overflow = "hidden"; 

    // Gắn listener nút xem thêm tại đây
    showMoreBtn.onclick = () => {
    popupDesc.classList.toggle("show");
    showMoreBtn.textContent = popupDesc.classList.contains("show") ? "Thu gọn" : "Xem thêm";
    };
  });
});
}

// Thêm hiệu ứng fade in cho popup
if (popup) {
  popup.addEventListener("animationend", () => {
    popup.classList.remove("fade-in");
  });
}

// Đóng popup khi bấm nút close
if (closeGallery && popup) {
  closeGallery.addEventListener("click", () => {
    popup.classList.remove("active");
    document.body.style.overflow = ""; // khôi phục cuộn nền
  });
}

// Đóng khi click ra ngoài popup content
if (popup) {
  popup.addEventListener("click", (e) => {
    if (e.target === popup) {
      popup.classList.remove("active");
      document.body.style.overflow = "";
    }
  });
}


// =============================================================================
// === HÀM HIỂN THỊ THÔNG BÁO TOAST MỚI (PHẦN BẠN YÊU CẦU UPDATE) ===
// =============================================================================
const toastContainer = document.getElementById("toast-container");

/**
 * Hiển thị một thông báo kiểu Toast (đẹp và chuyên nghiệp).
 * Cần có <div id="toast-container"></div> trong HTML.
 * @param {string} message - Nội dung thông báo
 * @param {string} type - Loại thông báo ('success', 'error', 'warning', ...)
 */
function showToast(message, type = "success") {
    // Nếu chưa có container, không làm gì cả (có thể dùng alert dự phòng)
    if (!toastContainer) {
        console.error("Không tìm thấy #toast-container. Vui lòng thêm vào HTML.");
        // Quay lại dùng alert nếu không thể hiển thị Toast
        alert(`[${type.toUpperCase()}] ${message}`); 
        return;
    }

    // 1. Tạo phần tử thông báo mới
    const toast = document.createElement("div");
    toast.classList.add("toast", type); 

    // 2. Chọn icon và tiêu đề phù hợp (Yêu cầu Font Awesome)
    let iconClass = "fa-info-circle";
    let title = "Thông Báo";
    switch (type) {
        case "success":
            iconClass = "fa-check-circle";
            title = "Thành Công";
            break;
        case "error":
            iconClass = "fa-times-circle";
            title = "Lỗi";
            break;
        case "warning":
            iconClass = "fa-exclamation-triangle";
            title = "Cảnh Báo";
            break;
    }

    // 3. Đặt nội dung HTML
    toast.innerHTML = `
        <i class="fa ${iconClass} toast-icon"></i>
        <div class="toast-content">
            <strong class="toast-title">${title}</strong>
            <span class="toast-message">${message}</span>
        </div>
    `;

    // 4. Thêm vào container
    toastContainer.appendChild(toast);

    // 5. Hiển thị thông báo (thêm class 'show')
    setTimeout(() => {
        toast.classList.add("show");
    }, 10); 

    // 6. Tự động ẩn sau 4 giây
    const removeTimeout = setTimeout(() => {
        toast.classList.remove("show"); // Bắt đầu hiệu ứng ẩn

        // Xóa phần tử khỏi DOM sau khi transition kết thúc
        toast.addEventListener('transitionend', () => {
            toast.remove();
        }, { once: true });

    }, 3000);

    // Ngăn chặn tự đóng nếu người dùng hover vào
    let hoverTimeout;
    toast.addEventListener('mouseenter', () => {
        clearTimeout(removeTimeout);
    });
    toast.addEventListener('mouseleave', () => {
        // Đặt lại timeout để ẩn sau khi bỏ hover
        hoverTimeout = setTimeout(() => {
            toast.classList.remove("show");
            toast.addEventListener('transitionend', () => {
                toast.remove();
            }, { once: true });
        }, 500); 
    });
}
// =============================================================================


/** Tải và thiết lập các sự kiện cho trang quản lý Feedback. */
function initializeFeedbackPage() {
    // "Lính gác": Nếu không phải trang feedback, dừng hàm ngay lập tức
    if (!feedbackContainer) {
        return;
    }

    // --- Bắt đầu logic của trang feedback ---
    if (feedbackItems.length > 0) {
        feedbackItems.forEach(item => {
            item.addEventListener('click', function() {
                const dataset = this.dataset;
                const feedbackId = dataset.id;

                // Cập nhật giao diện
                feedbackPlaceholder.classList.add('hidden');
                feedbackContent.classList.add('visible');
                feedbackItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                // Đổ dữ liệu vào khung chi tiết
                detailSubject.textContent = dataset.subject;
                detailName.textContent = dataset.name;
                detailEmail.textContent = dataset.email;
                detailEmail.href = `mailto:${dataset.email}`;
                detailBody.textContent = dataset.message;
                btnReply.href = `mailto:${dataset.email}?subject=Re: ${dataset.subject}`;
                btnDelete.href = `?page=feedback&action=delete&id=${feedbackId}`;

                // Gửi yêu cầu đánh dấu đã đọc
                if (this.dataset.status === 'new') {
                    fetch(`?page=feedback&action=mark_read&id=${feedbackId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const unreadDot = this.querySelector('.unread-dot');
                                if (unreadDot) {
                                    unreadDot.remove();
                                }
                                this.dataset.status = 'read';
                            }
                        })
                        .catch(error => console.error('Lỗi khi đánh dấu đã đọc:', error));
                }
            });
        });

        // Thêm xác nhận trước khi xóa
        if (btnDelete) {
            btnDelete.addEventListener('click', function(e) {
                const confirmation = confirm('Bạn có chắc chắn muốn xóa feedback này không?');
                if (!confirmation) {
                    e.preventDefault(); 
                } else {
                    // Thêm thông báo nhẹ nhàng sau khi xác nhận xóa
                    // showToast("Feedback đang được xử lý xóa.", "warning");
                }
            });
        }

        // Tự động nhấp vào item đầu tiên
        feedbackItems[0].click();
    }
}

window.addEventListener("resize", () => {
  if (window.innerWidth > 992) {
    // Nhớ thay đổi con số 992
    document.body.classList.remove("show-mobile-menu");
  }
});
/** Lấy thông tin session của người dùng từ backend. */
async function getSessionUser() {
   const res = await fetch("/fruitfarm/backend/core/get_session_user.php"); 
  return await res.json();
}

/** Cập nhật header (Xin chào user / Nút Login) dựa vào session. */
async function updateHeaderUser() {
  if (!userHeader) return;
  const user = await getSessionUser();
  if (user.username) {
    window.IS_LOGGED_IN = true; // <<< CẬP NHẬT TRẠNG THÁI LOGIN
    userHeader.textContent = `Xin chào, ${user.username}`;
    if (showPopupBtn) showPopupBtn.style.display = "none";
    if (logoutBtn) logoutBtn.style.display = "block";
  } else {
    window.IS_LOGGED_IN = false; // <<< CẬP NHẬT TRẠNG THÁI LOGOUT
    userHeader.textContent = "";
    if (showPopupBtn) showPopupBtn.style.display = "block";
    if (logoutBtn) logoutBtn.style.display = "none";
  }
}

/** Cập nhật số lượng sản phẩm trên icon giỏ hàng ở header. */
async function updateCartIconCount() {
  if (!cartCountEl) return;
  try {
    const res = await fetch("/fruitfarm/backend/cart/get_cart.php");
    const data = await res.json();
    cartCountEl.textContent = data.cartItems ? data.cartItems.length : 0;
  } catch (error) {
    console.error("Lỗi cập nhật icon giỏ hàng:", error);
    cartCountEl.textContent = "!";
  }
}

/** Gửi yêu cầu thao tác giỏ hàng (add/update/remove) lên backend */
async function handleCartAction(action, productId, quantity = 1, showMessage = false) {
    try {
        const response = await fetch("/fruitfarm/backend/admin/handle_cart.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=${action}&product_id=${productId}&quantity=${quantity}`
        });
        const data = await response.json();

        if (data.success) {
          const subtotalAmountEl = document.getElementById("subtotal-amount"); 
            const grandTotalElLocal = document.getElementById("grand-total");
            if (action === "update") {
                const input = document.querySelector(`.quantity-input[data-id="${productId}"]`);
                if (input) {
                    input.value = quantity;
                    const row = input.closest("tr");
                    const totalEl = row?.querySelector(".cart-subtotal"); //
                    if (totalEl) totalEl.textContent = (parseInt(data.subtotal || 0)).toLocaleString("en-US") + "₫";
                }

                if (grandTotalEl) {
                  const grand = parseInt(data.grandTotal) || 0;
                  grandTotalEl.textContent = grand.toLocaleString("vi-VN") + "₫";
                }
            }

            if (action === "remove") {
                const removeBtn = document.querySelector(`.remove-btn[data-id="${productId}"]`);
                const row = removeBtn?.closest("tr");
                if (row) row.remove();
                if (subtotalAmountEl) {
                    subtotalAmountEl.textContent = (parseInt(data.subtotalAmount || 0)).toLocaleString("en-US") + "₫";
                }
                if (grandTotalElLocal) {
                    grandTotalElLocal.textContent = (parseInt(data.grandTotal || 0)).toLocaleString("en-US") + "₫";
                }
            }
            updateCartIconCount();
            if (showMessage) showToast(data.message || "Thao tác thành công", "success");
        } else {
            if (showMessage) showToast(data.message || "Lỗi thao tác giỏ hàng", "error");
        }
    } catch (err) {
        console.error(err);
        if (showMessage) showToast("Lỗi kết nối khi thao tác giỏ hàng", "error");
    }
}



// Full-text-search
async function performLiveSearch() {
    const query = searchInput.value.trim();

    if (!searchResultsBox) return;
    searchResultsBox.style.display = query.length >= 2 ? 'block' : 'none';

    // 1. Hiển thị trạng thái "Đang tải..." ngay lập tức
    searchResultsBox.style.display = 'block';
    searchResultsBox.innerHTML = `
        <div class="result-state">
            <div class="spinner"></div>
            Đang tìm kiếm...
        </div>
    `;

    try {
        const response = await fetch(`${BASE_URL}backend/cart/live_search.php?q=${encodeURIComponent(query)}`);
        const products = await response.json();
        
        searchResultsBox.innerHTML = ''; // Xóa trạng thái "Đang tải"

        if (products.length > 0) {
            // Thêm tiêu đề
            const headerDiv = document.createElement('div');
            headerDiv.className = 'results-header';
            headerDiv.textContent = 'Sản phẩm gợi ý';
            searchResultsBox.appendChild(headerDiv);

            // Hiển thị sản phẩm
            products.forEach(product => {
                const itemLink = document.createElement('a');
                itemLink.href = `${BASE_URL}public/chitietsp.php?id=${product.id}`;
                itemLink.className = 'result-item';
                itemLink.innerHTML = `
                    <img src="${BASE_URL}${product.image_url}" alt="${product.name}" class="result-img">
                    <div class="result-info">
                        <p class="name">${product.name}</p>
                        <p class="price">${parseInt(product.price).toLocaleString('vi-VN')}₫</p>
                    </div>
                `;
                searchResultsBox.appendChild(itemLink);
            });

            // 2. Thêm nút "Xem tất cả" ở cuối
            const footerLink = document.createElement('a');
            footerLink.href = `${BASE_URL}public/search.php?q=${encodeURIComponent(query)}`;
            footerLink.className = 'results-footer';
            footerLink.textContent = `Xem tất cả kết quả cho "${query}"`;
            searchResultsBox.appendChild(footerLink);

        } else {
            // Hiển thị thông báo không có kết quả
            searchResultsBox.innerHTML = '<div class="result-state">Không tìm thấy sản phẩm nào.</div>';
        }

    } catch (error) {
        console.error('Lỗi khi thực hiện live search:', error);
        searchResultsBox.innerHTML = '<div class="result-state">Có lỗi xảy ra, vui lòng thử lại.</div>';
    }
}

/** Thiết lập logic bật/tắt cho menu dropdown của người dùng. */
function setupProfileDropdown() {
    // "Lính gác": Nếu không tìm thấy nút hoặc menu, không làm gì cả để tránh lỗi.
    if (!profileIcon || !dropdownMenu) {
        return;
    }

    // Sự kiện 1: Khi nhấn vào icon profile
    profileIcon.addEventListener('click', (event) => {
        event.stopPropagation();
        dropdownMenu.classList.toggle('show');
    });

    // Sự kiện 2: Khi nhấn ra ngoài, đóng menu
    window.addEventListener('click', () => {
        if (dropdownMenu.classList.contains('show')) {
            dropdownMenu.classList.remove('show');
        }
    });
}

// Gắn các sự kiện vào ô tìm kiếm
if (searchInput) {
    // Sự kiện "input" sẽ kích hoạt mỗi khi người dùng gõ phím
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout); // Hủy yêu cầu tìm kiếm cũ
        // Đặt một yêu cầu mới, chỉ thực hiện sau khi người dùng ngưng gõ 300ms
        searchTimeout = setTimeout(performLiveSearch, 300);
    });

    // Sự kiện "focus" để hiển thị lại kết quả nếu người dùng click lại vào ô search
    searchInput.addEventListener('focus', () => {
        if (searchInput.value.length >= 2) {
             performLiveSearch();
        }
    });

    // Ẩn box kết quả khi người dùng click ra ngoài khu vực tìm kiếm
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.search-box')) {
             if(searchResultsBox) searchResultsBox.style.display = 'none';
        }
    });
}

/**
 * Thêm sản phẩm vào giỏ hàng.
 * @param {string} productId - ID sản phẩm
 * @param {number} quantity - Số lượng (mặc định 1)
 * @param {boolean} isBuyNow - Nếu là Mua Ngay, sẽ chuyển đến trang thanh toán.
 */
async function addToCart(productId, quantity = 1, isBuyNow = false) {
    if (!window.IS_LOGGED_IN) {
        const user = await getSessionUser();
        window.IS_LOGGED_IN = !!user.username;
        if (!window.IS_LOGGED_IN) {
            showToast("Vui lòng đăng nhập để mua hàng!", "info");
            document.body.classList.add("show-popup");
            return;
            }
    }

    try {
        await handleCartAction("add", productId, quantity, true); 

        if (isBuyNow) {
            window.location.href = `${BASE_URL}public/thanhtoan.php`; 
        }
    } catch (err) {
        console.error("Lỗi khi thêm vào giỏ hàng:", err);
        showToast("Lỗi hệ thống khi thêm sản phẩm.", "error");
    }
}

// =============================================================================
// PHẦN 3: CÁC HÀM CHỨC NĂNG CHÍNH (MAIN FEATURE FUNCTIONS)
// =============================================================================
async function loadCart() {
  if (!cartBody) return;
  try {
    const res = await fetch("/fruitfarm/backend/cart/get_cart.php");
    const data = await res.json();

    cartBody.innerHTML = "";

    if (data.cartItems && data.cartItems.length > 0) {
      data.cartItems.forEach(item => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td><img src="${item.image_url}" alt="${item.name}" class="cart-product-img"></td>
          <td>${item.name}</td>
          <td>
            <textarea class="item-note-input" data-id="${item.product_id}" placeholder="Ghi chú cho sản phẩm này..." maxlength="255">${item.note || ''}</textarea>
          </td>
          <td>${parseInt(item.price).toLocaleString("vi-VN")}₫</td>
          <td>
            <div class="quantity-control">
              <button class="decrease-btn" data-id="${item.product_id}">-</button>
              <input type="number" value="${item.quantity}" min="1" class="quantity-input" data-id="${item.product_id}">
              <button class="increase-btn" data-id="${item.product_id}">+</button>
            </div>
          </td>
          <td class="cart-subtotal">${parseInt(item.subtotal).toLocaleString("vi-VN")}₫</td>
          <td><button class="remove-btn" data-id="${item.product_id}"><i class="fa fa-trash"></i></button></td>
        `;
        cartBody.appendChild(row);
      });
    } else {
      cartBody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;">Giỏ hàng của bạn đang trống.</td></tr>';
    }

    // Cập nhật tổng tiền
    if (grandTotalEl) {
      grandTotalEl.textContent = parseInt(data.grandTotal || 0).toLocaleString("vi-VN") + "₫";
    }

    // Cập nhật số lượng hiển thị trên icon giỏ hàng
    updateCartIconCount();

    // Thiết lập lắng nghe ghi chú
    setupItemNoteListeners();
  } catch (err) {
    console.error("Lỗi khi tải giỏ hàng:", err);
    cartBody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;">Không thể tải giỏ hàng.</td></tr>';
  }
}

// Thêm đoạn này vào file script.js nếu muốn dùng AJAX
const checkoutForm = document.getElementById("checkoutForm");

if (checkoutForm) {
    checkoutForm.addEventListener('submit', async (event) => {
        // Ngăn chặn form gửi đi theo cách truyền thống (lúc này mới cần JS để can thiệp)
        event.preventDefault(); 
        
        const formAction = checkoutForm.action;
        const formData = new FormData(checkoutForm);
        
        try {
            const response = await fetch(formAction, {
                method: 'POST',
                body: formData,
            });

            const result = await response.text();
            
            // Xử lý kết quả trả về từ process_order.php
            if (response.ok) {
                 // Nếu process_order.php chỉ trả về thông báo thành công hoặc redirect
                if (result.includes("success")) {
                    window.location.href = "hoanthanh.php"; // Chuyển sang trang hoàn thành
                } else if (result.includes("error")) {
                    alert("Lỗi: " + result); // Hiển thị lỗi từ server
                } else {
                    // Nếu process_order.php trả về HTML (thông thường là redirect thành công)
                    // Vì process_order.php dùng `header('Location: ...')`, nên chỉ cần redirect
                    // Do đó, nếu dùng AJAX, cần chỉnh lại process_order.php để trả về JSON
                    
                    // Hiện tại, nếu process_order.php không được sửa, ta có thể tạm thời submit lại
                    // Nhưng cách này không phải là AJAX.
                    
                    // Để đơn giản nhất, bạn nên **chỉ sửa lỗi 404** trong thanhtoan.php 
                    // thay vì can thiệp vào script.js.
                    
                    // Nếu process_order.php trả về lỗi (ví dụ: thông tin giao hàng thiếu)
                    if (result.includes("Vui lòng điền đầy đủ thông tin giao hàng")) {
                         alert(result);
                    }
                    
                    // Nếu process_order.php thành công, nó sẽ redirect.
                    // Để xử lý tốt hơn, process_order.php nên trả về JSON {success: true}
                }
                
                // Cần làm sạch giỏ hàng trên giao diện nếu thành công
                updateCartIconCount?.();
            } else {
                alert(`Lỗi Server: ${response.status} - Không thể xử lý đơn hàng.`);
            }
        } catch (error) {
            console.error('Lỗi khi gửi đơn hàng:', error);
            alert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.');
        }
    });
}

// =============================================================================
// LOGIC: LƯU GHI CHÚ TỪNG SẢN PHẨM (YÊU CẦU CỦA BẠN)
// =============================================================================

/** * Hàm gửi yêu cầu lưu ghi chú sản phẩm lên backend. */
async function handleItemNoteUpdate(productId, note) {
    try {
        const response = await fetch("/fruitfarm/backend/admin/handle_cart.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update_note&product_id=${productId}&note=${encodeURIComponent(note)}`
        });

        const text = await response.text(); // đọc raw text trước
        let data;
        try {
            data = JSON.parse(text);
        } catch(err) {
            console.error("Lỗi parse JSON:", err, "Server trả về:", text);
            showToast("Lỗi kết nối khi lưu ghi chú!", "error");
            return;
        }

        if (data.success) {
            showToast("Đã lưu ghi chú sản phẩm.", "success");
        } else {
            showToast(data.message || "Lỗi khi lưu ghi chú!", "error");
        }

    } catch (error) {
        console.error("Lỗi khi lưu ghi chú sản phẩm:", error);
        showToast("Lỗi kết nối khi lưu ghi chú!", "error");
    }
}


/** Hàm xử lý sự kiện input với Debounce. */
let noteUpdateTimeouts = {};
function handleNoteInput() {
  const productId = this.dataset.id;
  clearTimeout(noteUpdateTimeouts[productId]);
  noteUpdateTimeouts[productId] = setTimeout(() => handleItemNoteUpdate(productId, this.value), 1000);
}


/** Thiết lập trình lắng nghe sự kiện cho các ô ghi chú. */
function setupItemNoteListeners() {
    document.querySelectorAll(".item-note-input").forEach(input => {
        input.oninput = () => {
            clearTimeout(noteUpdateTimeouts[input.dataset.id]);
            noteUpdateTimeouts[input.dataset.id] = setTimeout(
                () => handleItemNoteUpdate(input.dataset.id, input.value),
                1000
            );
        };
    });
}

// =======================================================
// === HÀM RENDER PHÂN TRANG (Pagination) ===
// =======================================================

function updatePagination(totalPages, currentPage) {
    const paginationWrapper = document.querySelector(".pagination");
    if (!paginationWrapper) return;

    paginationWrapper.innerHTML = ''; // Xóa phân trang cũ

    if (totalPages <= 1) return; // Không hiển thị nếu chỉ có 1 trang

    // Tạo nút Previous (Trang trước)
    const prevPage = currentPage > 1 ? currentPage - 1 : 1;
    const prevLink = document.createElement('a');
    prevLink.href = "#";
    prevLink.className = `page-link ${currentPage === 1 ? 'disabled' : ''}`;
    prevLink.textContent = "«";
    prevLink.dataset.page = prevPage;
    paginationWrapper.appendChild(prevLink);

    // Tạo các nút trang
    for (let i = 1; i <= totalPages; i++) {
        const link = document.createElement('a');
        link.href = "#";
        link.className = `page-link ${i === currentPage ? 'active' : ''}`;
        link.textContent = i;
        link.dataset.page = i;
        paginationWrapper.appendChild(link);
    }

    // Tạo nút Next (Trang sau)
    const nextPage = currentPage < totalPages ? currentPage + 1 : totalPages;
    const nextLink = document.createElement('a');
    nextLink.href = "#";
    nextLink.className = `page-link ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLink.textContent = "»";
    nextLink.dataset.page = nextPage;
    paginationWrapper.appendChild(nextLink);
    
    // Gắn sự kiện click cho các nút phân trang MỚI
    document.querySelectorAll('.pagination .page-link:not(.disabled)').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = parseInt(link.dataset.page);
            // Gọi loadProducts với các bộ lọc hiện tại
            loadProducts(page, 'default', currentPriceFilter, currentCategory);
            // Cuộn lên đầu trang sản phẩm (optional)
            productsWrapper?.scrollIntoView({ behavior: 'smooth' });
        });
    });
}

// =======================================================
// === LOGIC LỌC SẢN PHẨM THEO DANH MỤC ===
// =======================================================

function setupCategoryFilter() {
    const categoryBtns = document.querySelectorAll('.category-bar [data-category]');

    categoryBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const newCategory = btn.dataset.category;
            
            // 1. Cập nhật UI (nút active, tiêu đề dropdown)
            updateCategoryBarUI(newCategory); 

            // 2. Tải sản phẩm mới (chuyển về trang 1)
            // Gọi loadProducts với category mới, giữ nguyên bộ lọc giá
            loadProducts(1, 'default', currentPriceFilter, newCategory); 
        });
    });
}

// Thay đổi trạng thái active trên các nút danh mục và cập nhật nút dropdown chính
function updateCategoryBarUI(activeSlug) {
    const categoryBtnDropdown = document.querySelector('.category-dropdown .category-btn');
    const categoryBtns = document.querySelectorAll('.category-bar [data-category]');

    categoryBtns.forEach(btn => btn.classList.remove('active'));

    const activeBtn = document.querySelector(`.category-bar [data-category="${activeSlug}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
        if (activeBtn.closest('.dropdown-menu')) {
             categoryBtnDropdown?.classList.add('active');
        }
    }
    
    if (categoryBtnDropdown) {
        let title = (activeSlug === 'all')
            ? 'Tất cả'
            : ((activeBtn?.textContent?.trim()) || 'Tất cả');
        categoryBtnDropdown.innerHTML = `<i class="fa-solid fa-list"></i> ${title} <i class="fa-solid fa-chevron-down caret"></i>`;
    }
}

// --- Event listener cho dropdown lọc giá ---
const priceFilter = document.getElementById("price-filter");
if (priceFilter) {
    priceFilter.addEventListener("change", () => {
        const selectedPrice = priceFilter.value;
        loadProducts(1, 'default', selectedPrice); // load lại trang 1 với lọc giá mới
    });
}

// --- loadProducts nhận page, sort, priceFilter ---
async function loadProducts(page = 1, sort = 'default', priceFilter = 'all', categorySlug = 'all') { 
    // === 1. CẬP NHẬT TRẠNG THÁI TOÀN CỤC (SỬ DỤNG 3 BIẾN MỚI) ===
    currentPage = page;
    currentPriceFilter = priceFilter;
    currentCategory = categorySlug; 

    const productsWrapper = document.querySelector(".product-list");
    if (!productsWrapper) { console.warn("Không tìm thấy .product-list, bỏ qua loadProducts."); return; }
    
    productsWrapper.innerHTML = '<div class="loading-spinner"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>'; 
    
    try {
        // === 2. CẬP NHẬT URL API (THÊM THAM SỐ category) ===
        const res = await fetch(`${BASE_URL}backend/cart/products.php?page=${page}&sort=${sort}&price=${priceFilter}&category=${categorySlug}`);
        
        if (!res.ok) throw new Error("Lỗi khi tải sản phẩm");
        const data = await res.json();
        const products = data.products || [];
        
        const inStock = products.filter(p => p.stock > 0)
            .sort((a,b)=> Number(a.price) - Number(b.price));
        const outOfStock = products.filter(p => p.stock <= 0)
            .sort((a,b)=> Number(a.price) - Number(b.price));
        const finalProducts = [...inStock, ...outOfStock];

        // --- Render ---
        productsWrapper.innerHTML = ""; // Xóa loading

        if (finalProducts.length > 0) {
             finalProducts.forEach(product => {
                 const stock = parseInt(product.stock || 0);
                 const isOutOfStock = stock <= 0;

                 const productHTML = `
                 <div class="product-card-container ${isOutOfStock ? 'out-of-stock' : ''}">
                     <a href="chitietsp.php?id=${product.id}" class="product-image-link">
                         <div class="product-image-wrapper">
                             <img src="${product.image_url || BASE_URL + 'public/assets/images/no-image.png'}" 
                                  alt="${product.name}" class="product-image">
                         </div>
                     </a>
                     <div class="product-info">
                         <h3 class="product-name">
                             <a href="chitietsp.php?id=${product.id}">${product.name}</a>
                         </h3>
                         <p class="product-price">${Number(product.price).toLocaleString()}₫</p>
                         <p class="product-stock">Tồn kho: ${stock}</p>
                         <div class="product-actions">
                             ${isOutOfStock
                                 ? `<button class="add-to-cart-btn btn-disabled" disabled>
                                         <i class="fa fa-shopping-cart"></i> Hết hàng
                                     </button>
                                     <button class="buy-now-btn btn-disabled" disabled>Mua ngay</button>`
                                 : `<button class="add-to-cart-btn btn-sm requires-login" data-id="${product.id}" data-stock="${stock}">
                                         <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                                     </button>
                                     <button class="buy-now-btn btn-sm requires-login" data-id="${product.id}" data-stock="${stock}">
                                         Mua Ngay
                                     </button>`
                             }
                         </div>
                     </div>
                 </div>
                 `;
                 productsWrapper.insertAdjacentHTML("beforeend", productHTML);
             });

            // Cập nhật phân trang
            if (typeof updatePagination === "function") {
                // Sử dụng data.total_pages và data.current_page trả về từ products.php
                updatePagination(data.total_pages, data.current_page); 
            }

            // Cập nhật số lượng sản phẩm hiển thị
            const countEl = document.querySelector('.product-count') || document.getElementById('product-count');
            if (countEl) {
                // data.total_products là biến trả về từ products.php
                const total = data.total_products || data.products?.length || 0; 
                countEl.textContent = `Hiển thị ${total} sản phẩm`;
            }

        } else {
            productsWrapper.innerHTML = '<p class="text-center">Không tìm thấy sản phẩm nào.</p>';
            const paginationEl = document.querySelector('.pagination');
            if (paginationEl) paginationEl.innerHTML = ''; // Ẩn phân trang
        }
        
        // === 3. GỌI HÀM CẬP NHẬT GIAO DIỆN DANH MỤC ===
        if (typeof updateCategoryBarUI === "function") {
            updateCategoryBarUI(categorySlug); 
        }

    } catch (err) {
        console.error(err);
        productsWrapper.innerHTML = '<p class="text-center text-danger">Đã xảy ra lỗi khi tải sản phẩm. Vui lòng thử lại.</p>';
        showToast("Không thể tải sản phẩm!", "error");
    }
}

// =============================================================================
// PHẦN 4: THIẾT LẬP CÁC TRÌNH NGHE SỰ KIỆN (EVENT LISTENERS SETUP)
// =============================================================================

function setupEventListeners() {

// --- Mở/đóng menu chính ---
  menuOpenButton?.addEventListener("click", () => {
    navbarMenu.classList.add("show"); 
  });

  menuCloseButton?.addEventListener("click", () => {
    navbarMenu.classList.remove("show");
  });

  // =======================================================
  // === THANH DANH MỤC SẢN PHẨM (CATEGORY BAR) ============
  // =======================================================
  const categoryButtons = document.querySelectorAll(".category-btn");
  const dropdownWrapper = document.querySelector(".category-dropdown");
  // --- Lắng nghe sự kiện Lọc sản phẩm
  const categoryBar = document.querySelector('.category-bar');
  const priceFilterEl = document.getElementById('price-filter');
    
    // 1. Lắng nghe click danh mục
    if (categoryBar) {
        categoryBar.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-category]');
            if (btn) {
                e.preventDefault();
                const categorySlug = btn.getAttribute('data-category');
                
                // GỌI loadProducts: Trang 1, giữ sắp xếp, giữ giá (currentPriceFilter), danh mục mới (categorySlug)
                loadProducts(1, 'default', currentPriceFilter, categorySlug); 
            }
        });
    }

    // 2. Lắng nghe thay đổi lọc giá (Đồng bộ tham số category)
    if (priceFilterEl) {
        // Dựa trên file của bạn, bạn đang lắng nghe sự kiện change của bộ lọc giá
        priceFilterEl.addEventListener("change", () => { 
            const selectedPrice = priceFilterEl.value;
            // GỌI loadProducts: Trang 1, giữ sắp xếp, giá mới (selectedPrice), GIỮ DANH MỤC CŨ (currentCategory)
            loadProducts(1, 'default', selectedPrice, currentCategory); 
        });
    }

  // Sự kiện mở/đóng dropdown "Tất cả"
  if (dropdownWrapper) {
    const dropdownBtn = dropdownWrapper.querySelector(".category-btn");
    const dropdownMenu = dropdownWrapper.querySelector(".dropdown-menu");

    document.addEventListener("click", (e) => {
      if (dropdownBtn.contains(e.target)) {
        dropdownWrapper.classList.toggle("open");
      } else if (!dropdownMenu.contains(e.target)) {
        dropdownWrapper.classList.remove("open");
      }
    });
  }

  // Sự kiện click vào danh mục (kể cả trong dropdown)
  categoryButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      // 1️⃣ Đổi trạng thái active
      categoryButtons.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");

      // 2️⃣ Đóng dropdown nếu chọn từ trong đó
      if (dropdownWrapper) dropdownWrapper.classList.remove("open");

      // 3️⃣ Lấy category và tải lại sản phẩm
      const selectedCategory = btn.dataset.category || "all";
      loadProducts(1, "default", "all", selectedCategory);
    });
  });

  // --- Logout ---
  if (logoutBtn) {
    logoutBtn.addEventListener("click", async () => {
      await fetch("/fruitfarm/backend/auth/logout.php");
      window.location.reload();
    });
  }

  // --- Nút Thanh toán ---
const checkoutBtn = document.querySelector('.btn-submit');
if (checkoutBtn) {
    checkoutBtn.addEventListener("click", (event) => {
        event.preventDefault();
        document.getElementById('checkoutForm').submit(); // gửi POST
    });
}

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

// Hàm xử lí xem thêm/thu gọn chi tiết sản phẩm
  function setupReadMoreToggle() {
    const container = document.querySelector('.read-more-container'); 
    const content = document.getElementById('productDetailsContent');
    const button = document.getElementById('readMoreBtn');
    
    // Nếu không tìm thấy, thoát ngay lập tức
    if (!container || !content || !button) return; 

    const maxHeightLimit = 280; 
    setTimeout(() => {
        // console.log("Content scrollHeight:", content.scrollHeight); // Dùng để debug
        if (content.scrollHeight <= maxHeightLimit + 5) { 
            container.classList.add('expanded'); 
            button.style.display = 'none';     
            return;
        }
    }, 0);


    button.addEventListener('click', function() {
        // Dùng toggle để chuyển đổi class 'expanded'
        const isExpanded = container.classList.toggle('expanded');
        
        // Cập nhật chữ trên nút
        if (isExpanded) {
            button.innerHTML = 'Thu gọn <i class="fa fa-angle-up"></i>';
        } else {
            button.innerHTML = 'Xem thêm <i class="fa fa-angle-down"></i>';
            
            // Rất quan trọng: Cuộn lên đầu phần nội dung nếu người dùng thu gọn
            // Nếu bạn muốn cuộn lên đầu section chứ không phải nội dung:
            // document.querySelector('.product-full-details').scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Hiện tại tôi dùng cuộn đến đầu nội dung
            content.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
  }

// =============================================================================
// PHẦN 5: DASHBOARD BÁO CÁO ĐỘNG
// =============================================================================

async function updateReportData() {
    try {
        const res = await fetch("/fruitfarm/backend/admin/statistics_api.php");
        if (!res.ok) throw new Error("API lỗi " + res.status);
        const data = await res.json();
        console.log("API data:", data);

        // ✅ Kiểm tra phần tử tồn tại trước khi cập nhật
        const revenueEl = document.getElementById("totalRevenueValue");
        const ordersEl = document.getElementById("totalOrdersValue");

        if (revenueEl) {
            revenueEl.textContent = new Intl.NumberFormat("vi-VN").format(data.totalRevenue) + " VNĐ";
        }
        if (ordersEl) {
            ordersEl.textContent = data.totalOrders;
        }

        // Update bảng top products
        const topProductsBody = document.getElementById("topProductsTableBody");
        if (topProductsBody) {
            topProductsBody.innerHTML = data.topProducts.map(p =>
                `<tr><td>${p.name}</td><td style="text-align:right">${p.total_sold}</td></tr>`
            ).join("");
        }

        // Update bảng most viewed
        const mostViewedBody = document.getElementById("mostViewedTableBody");
        if (mostViewedBody) {
            mostViewedBody.innerHTML = data.mostViewed.map(p =>
                `<tr><td>${p.name}</td><td style="text-align:right">${p.views}</td></tr>`
            ).join("");
        }

        // Update chart nếu có
        if (revenueChartInstance && data.revenueByDate) {
            revenueChartInstance.data.labels = data.revenueByDate.map(r => r.day);
            revenueChartInstance.data.datasets[0].data = data.revenueByDate.map(r => r.revenue);
            revenueChartInstance.update();
        }

    } catch (err) {
        console.error("Lỗi load thống kê:", err);
    }
}

// =======================================================
// === LOGIC ẨN/HIỆN HEADER KHI CUỘN VÀ DI CHUỘT (CẬP NHẬT) ===
// =======================================================
(function() {
    const header = document.querySelector('header');
    let lastScrollTop = 0;
    const scrollThreshold = 50; 

    const hideHeader = () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > scrollThreshold) {
            header.classList.add('header-hidden');
        }
    };

    const showHeader = () => {
        header.classList.remove('header-hidden');
    };

    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop < lastScrollTop) {
            showHeader();
        } else if (scrollTop > lastScrollTop && scrollTop > scrollThreshold) {
            hideHeader();
        } else if (scrollTop <= scrollThreshold) {
            showHeader();
        }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    });

})();

// Lắng nghe sự kiện click trên toàn bộ body để xử lý các phần tử được thêm động
document.addEventListener('click', async (e) => {
  const confirmBtn = e.target.closest('.confirm-order-btn');
  if (!confirmBtn) return;
  e.preventDefault();

  const orderId = confirmBtn.dataset.id;
  const actionCell = confirmBtn.parentElement;

  if (!orderId) return;

  try {
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...'; 

    // Gửi yêu cầu AJAX xác nhận đơn hàng
    const res = await fetch(BASE_URL_JS + 'backend/admin/order_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'confirm_order', order_id: orderId })
    });
    const data = await res.json();

    if (data.success) {
      showToast(data.message || `Đơn hàng #${orderId} đã được xác nhận!`, 'success');

      // === Cập nhật ngay trên giao diện ===
      const row = confirmBtn.closest('tr');
      const statusCell = row.querySelector('td[data-label="Trạng Thái"]');

      if (statusCell) {
        // Đảm bảo class đồng bộ với CSS hiện tại
        statusCell.innerHTML = `
          <span class="status-badge status-completed">
            Đã hoàn thành
          </span>
        `;

        // Hiệu ứng highlight nhẹ
        const badge = statusCell.querySelector('.status-badge');
        badge.style.opacity = '0';
        setTimeout(() => {
          badge.style.transition = 'opacity 0.3s ease';
          badge.style.opacity = '1';
        }, 50);
      }

      // Thay nút "Xác nhận" bằng "Cập nhật"
      actionCell.innerHTML = `
        <a href="chitietdonhang.php?id=${orderId}" class="btn btn-sm btn-info" title="Xem chi tiết">
          <i class="fas fa-eye"></i>
        </a>
        <a href="capnhattrangthai.php?id=${orderId}" class="btn btn-sm btn-warning" title="Cập nhật trạng thái">
          <i class="fas fa-edit"></i> Cập nhật
        </a>
      `;

      // Cập nhật thống kê (nếu có)
      if (typeof updateReportData === 'function') updateReportData();

    } else {
      showToast(data.message || 'Lỗi xử lý. Vui lòng kiểm tra console.', 'error');
      confirmBtn.disabled = false;
      confirmBtn.innerHTML = '<i class="fas fa-check"></i> Xác nhận';
    }

  } catch (error) {
    console.error('Lỗi AJAX:', error);
    showToast('Lỗi kết nối hoặc xử lý dữ liệu.', 'error');
    confirmBtn.disabled = false;
    confirmBtn.innerHTML = '<i class="fas fa-check"></i> Xác nhận';
  }
});

// Bắt sự kiện click trên các nút phân trang (AJAX không reload)
document.addEventListener('click', function(e) {
  const pageLink = e.target.closest('.pagination .page-link');
  if (!pageLink) return;

  e.preventDefault(); // chặn reload
  const page = pageLink.dataset.page;

  const sortSelect = document.getElementById("sort-by");
  const sortValue = sortSelect ? sortSelect.value : "default";

  // Gọi lại loadProducts với trang và kiểu sắp xếp hiện tại
  loadProducts(page, sortValue, currentPriceFilter, currentCategory);
});

// --- Popup OTP giả lập ---
function showOTPModal(method, onSuccess) {
  const modal = document.createElement("div");
  modal.className = "otp-modal";
  modal.innerHTML = `
    <div class="otp-box">
      <h3>Xác nhận thanh toán ${method.toUpperCase()}</h3>
      <p>Nhập mã OTP giả lập được gửi đến số điện thoại của bạn.</p>
      <input type="text" class="otp-input" maxlength="6" placeholder="Nhập OTP (ví dụ: 123456)" />
      <div class="otp-actions">
        <button class="confirm-otp-btn">Xác nhận</button>
        <button class="cancel-otp-btn">Hủy</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);

  const input = modal.querySelector(".otp-input");
  const confirmBtn = modal.querySelector(".confirm-otp-btn");
  const cancelBtn = modal.querySelector(".cancel-otp-btn");

  setTimeout(() => modal.classList.add("show"), 10);
  input.focus();

  confirmBtn.addEventListener("click", () => {
    const otp = input.value.trim();
    if (otp === "123456") {
      showToast("Xác thực OTP thành công!", "success");
      modal.remove();
      onSuccess?.();
    } else {
      showToast("Mã OTP không hợp lệ!", "error");
    }
  });

  cancelBtn.addEventListener("click", () => modal.remove());

  modal.addEventListener("click", e => {
    if (e.target === modal) modal.remove();
  });
}

// ============================================================================
// 🧾 THANH TOÁN + VOUCHER MODULE
// ============================================================================

// --- Biến toàn cục (được khởi tạo sau DOMContentLoaded) ---
let selectItems, selectedDiv, paymentInput, confirmButton;
let applyVoucherBtn, voucherCodeInput, voucherMessage, discountAmountInput;
let subtotalDisplay, discountDisplay, voucherCodeApplied, finalTotalDisplay;

let currentDiscount = 0;
const shippingFee = 20000;

// --- Hàm cập nhật tổng tiền ---
function updateSummary(newDiscount = 0, appliedCode = '') {
    if (!subtotalDisplay || !discountDisplay || !finalTotalDisplay) return;

    const subtotalText = subtotalDisplay.textContent.replace(/[₫,.]/g, "");
    const subtotal = parseInt(subtotalText) || 0;

    currentDiscount = newDiscount;
    const finalTotal = Math.max(0, subtotal - currentDiscount + shippingFee);

    discountDisplay.textContent = '- ' + currentDiscount.toLocaleString('vi-VN') + '₫';
    finalTotalDisplay.innerHTML = `<strong>${finalTotal.toLocaleString('vi-VN')}₫</strong>`;
    if (discountAmountInput) discountAmountInput.value = currentDiscount;
    if (voucherCodeApplied) voucherCodeApplied.textContent = appliedCode ? `(${appliedCode})` : "";
}

// --- Hàm áp dụng mã giảm giá ---
async function handleApplyVoucher() {
    const voucherCode = voucherCodeInput.value.trim();
    voucherMessage.textContent = "Đang kiểm tra...";
    voucherMessage.className = "voucher-message";

    if (!voucherCode) {
        voucherMessage.textContent = "Vui lòng nhập mã giảm giá.";
        voucherMessage.className = "voucher-message error";
        updateSummary(0);
        return;
    }

    try {
        const response = await fetch(`${BASE_URL}backend/cart/apply_voucher.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `voucher_code=${encodeURIComponent(voucherCode)}`
        });
        const data = await response.json();

        if (data.success) {
            const discount = parseInt(data.discount_amount);
            updateSummary(discount, voucherCode);
            voucherMessage.textContent = data.message || `Áp dụng mã thành công! Giảm ${discount.toLocaleString('vi-VN')}₫.`;
            voucherMessage.className = "voucher-message success";
        } else {
            updateSummary(0);
            voucherMessage.textContent = data.message || "Mã giảm giá không hợp lệ hoặc đã hết hạn.";
            voucherMessage.className = "voucher-message error";
        }
    } catch (error) {
        console.error("Lỗi áp dụng voucher:", error);
        updateSummary(0);
        voucherMessage.textContent = "Đã xảy ra lỗi khi kiểm tra mã giảm giá.";
        voucherMessage.className = "voucher-message error";
    }
}

// --- Hàm chọn phương thức thanh toán ---
function handlePaymentSelection() {
    selectItems.forEach(item => {
        item.addEventListener("click", () => {
            selectItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");

            const arrowIcon = selectedDiv.querySelector('.arrow');
            selectedDiv.innerHTML = item.innerHTML.trim();
            selectedDiv.appendChild(arrowIcon);

            paymentInput.value = item.dataset.value;
        });
    });
}

// --- Hàm xác nhận đặt hàng ---
function handleConfirmOrder(e) {
    e.preventDefault();

    const method = paymentInput.value;
    const fullname = checkoutForm.querySelector("[name='fullname']").value.trim();
    const phone = checkoutForm.querySelector("[name='phone']").value.trim();
    const address = checkoutForm.querySelector("[name='address']").value.trim();

    const totalText = finalTotalDisplay.textContent.replace(/[₫,.]/g, "");
    const totalAmount = parseInt(totalText) || 0;

    const voucherCode = voucherCodeInput.value.trim();
    const discountAmount = discountAmountInput.value;

    if (!fullname || !phone || !address) {
        showToast("Vui lòng nhập đầy đủ thông tin giao hàng!", "warning");
        return;
    }

    if (method === "cod") {
        checkoutForm.submit();
    } else if (method === "vnpay") {
        window.location.href = `vnpay.php?fullname=${encodeURIComponent(fullname)}&amount=${totalAmount}&phone=${encodeURIComponent(phone)}&address=${encodeURIComponent(address)}&voucher_code=${encodeURIComponent(voucherCode)}&discount=${discountAmount}`;
    } else if (method === "momo") {
        window.location.href = `momo.php?fullname=${encodeURIComponent(fullname)}&amount=${totalAmount}&phone=${encodeURIComponent(phone)}&address=${encodeURIComponent(address)}&voucher_code=${encodeURIComponent(voucherCode)}&discount=${discountAmount}`;
    }
}

// --- Hàm khởi tạo toàn bộ sự kiện trên trang thanh toán ---
function initCheckoutPage() {
    checkoutForm = document.getElementById("checkoutForm");
    if (!checkoutForm) return; // Không phải trang thanh toán -> bỏ qua

    // Gán biến DOM
    selectItems = document.querySelectorAll(".select-items li");
    selectedDiv = document.querySelector(".select-selected");
    paymentInput = document.getElementById("payment-method-input");
    confirmButton = document.querySelector(".btn-submit");

    applyVoucherBtn = document.getElementById("applyVoucherBtn");
    voucherCodeInput = document.getElementById("voucher_code");
    voucherMessage = document.getElementById("voucher_message");
    discountAmountInput = document.getElementById("discount_amount_input");
    subtotalDisplay = document.getElementById("subtotal_display");
    discountDisplay = document.getElementById("discount_display");
    voucherCodeApplied = document.getElementById("voucher_code_applied");
    finalTotalDisplay = document.getElementById("final_total_display");

    // Gán sự kiện
    if (applyVoucherBtn) applyVoucherBtn.addEventListener("click", handleApplyVoucher);
    if (selectItems.length > 0) handlePaymentSelection();
    if (confirmButton) confirmButton.addEventListener("click", handleConfirmOrder);

    // Khởi tạo tổng tiền
    updateSummary(currentDiscount);
}

// =============================================================================
// PHẦN 6: KHỞI CHẠY (INITIALIZATION)
// =============================================================================

document.addEventListener("DOMContentLoaded", async () => {
  const body = document.body;

  // Lấy query string từ URL
  const urlParams = new URLSearchParams(window.location.search);
  const searchQuery = urlParams.get('q'); // ?q=cam

  // ==== CẬP NHẬT HEADER & LOAD PRODUCTS ====
  await updateHeaderUser?.();

  if (!searchQuery) {
    // Chỉ load sản phẩm mặc định khi không có search query
    loadProducts(1, 'default', 'all', 'all');
  }

  // ==== LOGIN/SIGNUP POPUP ====
  function setupFormPopup() {
    if (!formPopup || !showPopupBtn) return;

    showPopupBtn.addEventListener("click", () => {
      body.classList.add("show-popup");
      if (popup?.classList.contains("active")) popup.classList.remove("active");
      body.style.overflow = "";
    });

    hidePopupBtn?.addEventListener("click", () => body.classList.remove("show-popup"));

    loginSignupLink?.forEach(link =>
      link.addEventListener("click", e => {
        e.preventDefault();
        formPopup.classList.toggle("show-signup");
      })
    );
  }

  // ==== LOGIN/REGISTER AJAX ====
  if (registerForm) {
    registerForm.addEventListener("submit", async e => {
      e.preventDefault();
      const username = registerForm.username.value.trim();
      const email = registerForm.email.value.trim();
      const password = registerForm.password.value;
      const confirm_password = registerForm.confirm_password.value;

      if (!username || !email || !password || !confirm_password) {
        showToast("Vui lòng điền đầy đủ các trường.", "error");
        return;
      }

      try {
        const res = await fetch("/fruitfarm/backend/auth/signup.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username, email, password, confirm_password })
        });

        const data = await res.json();
        console.log("PHP response:", data); // debug

        showToast(data.message, data.success ? "success" : "error");

        if (data.success) {
          setTimeout(() => {
            document.querySelector('.form-box.signup').style.display = 'none';
            const loginBox = document.querySelector('.form-box.login');
            loginBox.style.display = 'flex';
            formPopup.classList.remove('show-signup');
            loginBox.querySelector('input[name="username"]').focus();
          }, 400);
        }

      } catch (err) {
        console.error(err);
        showToast("Có lỗi xảy ra. Vui lòng thử lại.", "error");
      }
    });
  }

  if (loginForm) {
    loginForm.addEventListener("submit", async e => {
      e.preventDefault();
      const dataObj = Object.fromEntries(new FormData(loginForm).entries());
      const res = await fetch("/fruitfarm/backend/auth/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dataObj),
      });

      const data = await res.json();
      showToast(data.message, data.success ? "success" : "error");

      if (data.success) {
        if (data.redirect) {
          setTimeout(() => window.location.href = data.redirect, 800);
        } else {
          setTimeout(async () => {
            await updateHeaderUser();
            if (!searchQuery) loadProducts(1, 'default', 'all', 'all');
            document.body.classList.remove("show-popup");
          }, 500);
        }
      }
    });
  }

  // ==== BUTTON ACTIONS (ADD TO CART / BUY NOW / CART OPERATIONS) ====
  document.addEventListener("click", async (e) => {
    const target = e.target.closest('button');
    if (!target) return;

    // Kiểm tra login trước khi thêm giỏ / mua ngay
    if (target.classList.contains('requires-login')) {
      const user = await getSessionUser();
      window.IS_LOGGED_IN = !!user.username;

      if (!window.IS_LOGGED_IN) {
        e.preventDefault();
        document.body.classList.add('show-popup');
        showToast("Vui lòng đăng nhập để mua hàng!", "info");
        return;
      }
    }

    // Thêm vào giỏ hàng
    if (target.classList.contains("add-to-cart-btn")) {
      e.preventDefault();
      const stock = parseInt(target.dataset.stock || 0);
      if (stock <= 0) { showToast("Sản phẩm này đã hết hàng!", "warning"); return; }
      addToCart(target.dataset.id, 1, false); 
      return;
    }

    // Mua ngay
    if (target.classList.contains("buy-now-btn")) {
      e.preventDefault();
      const stock = parseInt(target.dataset.stock || 0);
      if (stock <= 0) { showToast("Sản phẩm này đã hết hàng!", "warning"); return; }
      addToCart(target.dataset.id, 1, true);
    }

    // Hết hàng (btn-disabled)
    if (target.classList.contains("btn-disabled")) {
      showToast("Sản phẩm này đã hết hàng!", "warning");
      return;
    }

    // Cart actions
    if (target.classList.contains("increase-btn") || target.classList.contains("decrease-btn") || target.classList.contains("remove-btn")) {
      const cartRow = target.closest("tr");
      const input = cartRow?.querySelector(".quantity-input");
      const productId = target.dataset.id;
      if (!cartRow || !productId || !input) return;

      if (target.classList.contains("increase-btn")) {
        const newQty = parseInt(input.value) + 1;
        input.value = newQty;
        handleCartAction("update", productId, newQty, true);
      }

      if (target.classList.contains("decrease-btn")) {
        const newQty = parseInt(input.value) - 1;
        if (newQty > 0) {
          input.value = newQty;
          handleCartAction("update", productId, newQty, true);
        } else {
          handleCartAction("remove", productId, 0, true);
          cartRow.remove();
        }
      }

      if (target.classList.contains("remove-btn")) {
        handleCartAction("remove", productId, 0, true);
        cartRow.remove();
      }
    }
  });

  // ==== ADMIN PRODUCTS PAGE LOGIC ====
  (function () {
    const qv = document.getElementById("quickView");
    if (qv) {
      qv.addEventListener("click", e => {
        if (e.target === qv) qv.classList.remove("active");
      });
    }

    const searchInput = document.getElementById("searchInput");
    const filterCategory = document.getElementById("filterCategory");
    const productsTable = document.getElementById("productsTable");

    if (searchInput && productsTable) {
      searchInput.addEventListener("input", function () {
        const q = this.value.trim().toLowerCase();
        const rows = productsTable.querySelectorAll("tbody tr.product-row");
        rows.forEach(r => {
          const name = (r.dataset.name || "").toLowerCase();
          const desc = (r.dataset.desc || "").toLowerCase();
          const cat = (r.dataset.category || "").toLowerCase();
          const match = q === "" || name.includes(q) || desc.includes(q) || cat.includes(q);
          r.style.display = match ? "" : "none";
        });
      });
    }

    if (filterCategory && productsTable) {
      filterCategory.addEventListener("change", function () {
        const val = this.value;
        const rows = productsTable.querySelectorAll("tbody tr.product-row");
        rows.forEach(r => {
          if (!val || val === "0") r.style.display = "";
          else r.style.display = r.dataset.categoryId === val ? "" : "none";
        });
      });
    }

    const qvDeleteForm = document.getElementById("qv-delete-form");
    if (qvDeleteForm) {
      qvDeleteForm.addEventListener("submit", function (e) {
        if (!confirm("Bạn có muốn xóa sản phẩm này?")) e.preventDefault();
      });
    }

    const toastGlobal = document.querySelector(".toast-global");
    if (toastGlobal) {
      setTimeout(() => toastGlobal.classList.add("show"), 50);
      setTimeout(() => toastGlobal.classList.remove("show"), 4000);
    }
  })();

  // ==== CHECKOUT / PAYMENT ====
  const paymentSelect = document.getElementById("paymentSelect");
  const paymentInput = document.getElementById("payment-method-input");
  const checkoutForm = document.getElementById("checkoutForm");

  if (paymentSelect && paymentInput) {
    const selected = paymentSelect.querySelector(".select-selected");
    const selectedText = selected?.querySelector("span");
    const optionsList = paymentSelect.querySelector(".select-items");
    const options = optionsList?.querySelectorAll("li") || [];

    selected?.addEventListener("click", e => {
      e.stopPropagation();
      optionsList?.classList.toggle("show");
      selected?.classList.toggle("active");
    });

    options.forEach(option => {
      option.addEventListener("click", () => {
        options.forEach(opt => opt.classList.remove("active"));
        option.classList.add("active");
        if (selectedText) selectedText.textContent = option.textContent.trim();
        paymentInput.value = option.dataset.value;
        optionsList?.classList.remove("show");
        selected?.classList.remove("active");
      });
    });

    document.addEventListener("click", e => {
      if (!paymentSelect.contains(e.target)) {
        optionsList?.classList.remove("show");
        selected?.classList.remove("active");
      }
    });
  }

  if (checkoutForm && paymentInput) {
    let isPaymentConfirmed = false;
    checkoutForm.addEventListener("submit", function (e) {
      e.preventDefault();
      if (isPaymentConfirmed) return;
      const method = paymentInput.value;

      const onPaymentSuccess = () => {
        isPaymentConfirmed = true;
        checkoutForm.submit();
      };

      if (method === "vnpay" || method === "momo") {
        showOTPModal(method, onPaymentSuccess);
      } else {
        onPaymentSuccess();
      }
    });
  }

  // ==== PROFILE DROPDOWN ====
  if (profileIcon && dropdownMenu) {
    profileIcon.addEventListener("click", e => {
      e.stopPropagation();
      dropdownMenu.classList.toggle("show");
    });

    document.addEventListener("click", e => {
      if (!dropdownMenu.contains(e.target) && !profileIcon.contains(e.target))
        dropdownMenu.classList.remove("show");
    });
  }

  // ==== BACK TO TOP ====
  if (backToTopBtn) {
    window.addEventListener("scroll", () => backToTopBtn.style.display = window.scrollY > 300 ? "flex" : "none");
    backToTopBtn.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
  }

  // ==== MOBILE MENU ====
  if (menuOpenButton && menuCloseButton) {
    menuOpenButton.addEventListener("click", () => body.classList.toggle("show-mobile-menu"));
    menuCloseButton.addEventListener("click", () => body.classList.remove("show-mobile-menu"));
  }

  // ==== Đóng popup gallery ====
  if (closeGallery && popup) {
    closeGallery.addEventListener("click", () => {
      popup.classList.remove("active");
      document.body.style.overflow = "";
    });
  }

  // ==== Logout ====
  if (logoutBtn) {
    logoutBtn.addEventListener("click", async () => {
      await fetch("/fruitfarm/backend/auth/logout.php");
      window.location.reload();
    });
  }

  // ==== Slider Testimonials ====
  if (testimonialsSliderEl && typeof Swiper !== "undefined") {
    const testimonialSwiper = new Swiper(testimonialsSliderEl, {
      loop: true,
      speed: 800,
      autoplay: { delay: 5000, disableOnInteraction: false },
      slidesPerView: 1,
      spaceBetween: 20,
      pagination: { el: ".swiper-pagination", clickable: true },
      navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
      watchOverflow: true,
      observer: true,
      observeParents: true,
      preloadImages: true,
      updateOnImagesReady: true,
    });

    testimonialsSliderEl.addEventListener("mouseenter", () => testimonialSwiper.autoplay.stop());
    testimonialsSliderEl.addEventListener("mouseleave", () => testimonialSwiper.autoplay.start());
  }

  // ==== INITIALIZATION ====
  loadCart?.();
  setupFormPopup?.();
  updateCartIconCount?.();
  setupEventListeners?.();
  setupReadMoreToggle();
  setupItemNoteListeners?.();
  initializeFeedbackPage?.();
  setupCategoryFilter?.();
});
