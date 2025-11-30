<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . '/../core/config.php');
require_once(__DIR__ . '/../core/db_connect.php');

// Nhận tham số GET
$priceFilter = $_GET['price'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$products_per_page = 15;
$offset = ($page - 1) * $products_per_page;

// --- Bộ lọc trạng thái và category ---
$where = "WHERE p.status = 'active'";

if ($category !== 'all') {
    $safeCategory = $conn->real_escape_string($category);
    $where .= " AND LOWER(c.slug) = LOWER('$safeCategory')";
}

// --- Bộ lọc giá ---
$priceRanges = [
    '0-100'   => ['min' => 0,       'max' => 100000],
    '100-300' => ['min' => 100000,  'max' => 300000],
    '300-500' => ['min' => 300000,  'max' => 500000],
    '500+'    => ['min' => 500000,  'max' => null]
];

if (isset($priceRanges[$priceFilter])) {
    $range = $priceRanges[$priceFilter];
    if (is_null($range['max'])) {
        $where .= " AND CAST(p.price AS UNSIGNED) > {$range['min']}";
    } else {
        $where .= " AND CAST(p.price AS UNSIGNED) BETWEEN {$range['min']} AND {$range['max']}";
    }
}

// --- Đếm tổng sản phẩm (join categories để dùng c.slug) ---
$totalQuery = "SELECT COUNT(*) AS total 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id
               $where";

$totalResult = $conn->query($totalQuery);
$total_products = 0;
if ($totalResult && $totalResult->num_rows > 0) {
    $row = $totalResult->fetch_assoc();
    $total_products = (int)$row['total'];
}
$total_pages = ($total_products > 0) ? ceil($total_products / $products_per_page) : 1;

// --- Lấy danh sách sản phẩm ---
$query = "SELECT p.id, p.name, p.description, p.image_url, p.price, p.stock, c.slug AS category_slug
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          $where
          ORDER BY (p.stock = 0), CAST(p.price AS UNSIGNED) ASC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Lỗi prepare SQL: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $products_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['image_url'] = !empty($row['image_url']) 
        ? BASE_URL . $row['image_url'] 
        : BASE_URL . 'public/assets/images/no-image.png';
    $row['stock'] = isset($row['stock']) && is_numeric($row['stock']) ? (int)$row['stock'] : 0;
    $products[] = $row;
}

$stmt->close();
$conn->close();

// --- Trả JSON ---
echo json_encode([
    'success' => true,
    'products' => $products,
    'current_page' => $page,
    'total_pages' => $total_pages,
    'total_products' => $total_products
], JSON_UNESCAPED_UNICODE);
?>
