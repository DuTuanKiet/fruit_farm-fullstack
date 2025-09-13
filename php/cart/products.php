<?php
session_start();
require_once '../db_connect.php';
header('Content-Type: application/json');

// --- CẤU HÌNH PHÂN TRANG ---
$products_per_page = 8;

$total_products_result = $conn->query("SELECT COUNT(id) as total FROM products");
$total_products = $total_products_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $products_per_page);

// Lấy số trang hiện tại từ yêu cầu (mặc định là trang 1)
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

// Tính OFFSET để lấy đúng sản phẩm trong CSDL
$offset = ($current_page - 1) * $products_per_page;

// --- TRUY VẤN SẢN PHẨM CHO TRANG HIỆN TẠI ---
$stmt = $conn->prepare("SELECT id, name, price, image_url FROM products ORDER BY id ASC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $products_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result->num_rows > 0) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}

// Trả về cả danh sách sản phẩm và thông tin phân trang
echo json_encode([
    'products' => $products,
    'totalPages' => $total_pages,
    'currentPage' => $current_page
]);

$stmt->close();
$conn->close();
?>