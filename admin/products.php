<?php
// admin/products.php (PHIÊN BẢN HOÀN CHỈNH)

// --- PHẦN 1: XỬ LÝ DỮ LIỆU FORM KHI GỬI LÊN (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Xử lý LƯU SẢN PHẨM MỚI
    if ($action === 'save_new') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];
        $description = $_POST['description'];
        
        $stmt = $conn->prepare("INSERT INTO products (name, price, image_url, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $name, $price, $image_url, $description);
        $stmt->execute();
        echo "<script>alert('Product added successfully!'); window.location.href='?page=products';</script>";
        exit;
    }

    // Xử lý CẬP NHẬT SẢN PHẨM
    if ($action === 'update') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image_url = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sdssi", $name, $price, $image_url, $description, $id);
        $stmt->execute();
        echo "<script>alert('Product updated successfully!'); window.location.href='?page=products';</script>";
        exit;
    }
}

// --- PHẦN 2: XỬ LÝ HÀNH ĐỘNG TỪ URL (GET) ---
$action = $_GET['action'] ?? 'list'; // Mặc định là hiển thị danh sách

// Xử lý XÓA SẢN PHẨM
if ($action === 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    echo "<script>alert('Product deleted!'); window.location.href='?page=products';</script>";
    exit;
}

// --- PHẦN 3: HIỂN THỊ GIAO DIỆN TƯƠNG ỨNG ---
if ($action === 'add') {
    // Nếu action là 'add', gọi form thêm mới
    include 'product_add.php';

} elseif ($action === 'edit' && isset($_GET['id'])) {
    // Nếu action là 'edit', gọi form sửa
    include 'product_edit.php';

} else {
    // Mặc định, hiển thị danh sách sản phẩm
    $result = $conn->query("SELECT * FROM products ORDER BY id ASC");
?>
    <a href="?page=products&action=add" class="btn">Add New Product</a>
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="" width="50"></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo number_format($row['price']); ?>₫</td>
                <td class="actions">
                    <a href="?page=products&action=edit&id=<?php echo $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                    <a href="?page=products&action=delete&id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fa-solid fa-trash"></i> Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php
} 
?>