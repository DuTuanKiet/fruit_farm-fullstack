<?php
// --- PHẦN 1: XỬ LÝ DỮ LIỆU FORM KHI GỬI LÊN (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Xử lý LƯU SẢN PHẨM MỚI
    if ($action === 'save_new') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];
        $description = $_POST['description'];
        // [FIXED] Lấy giá trị checkbox is_featured ngay tại đây
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        // [FIXED] Chuẩn bị câu lệnh INSERT đầy đủ
        $stmt = $conn->prepare("INSERT INTO products (name, price, image_url, description, is_featured) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssi", $name, $price, $image_url, $description, $is_featured);
        
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
        // [UPGRADED] Thêm khả năng cập nhật trạng thái is_featured
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        // [UPGRADED] Cập nhật câu lệnh UPDATE để bao gồm cả is_featured
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image_url = ?, description = ?, is_featured = ? WHERE id = ?");
        $stmt->bind_param("sdssii", $name, $price, $image_url, $description, $is_featured, $id);
        
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
    // Lấy thông tin sản phẩm hiện tại để điền vào form
    $id_to_edit = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc(); // Biến $product này sẽ được dùng trong product_edit.php
    
    include 'product_edit.php';

} else {
    // Mặc định, hiển thị danh sách sản phẩm
    $result = $conn->query("SELECT * FROM products ORDER BY id ASC");
?>
    <h2>Product Management</h2>
    <a href="?page=products&action=add" class="btn">Add New Product</a>
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Featured</th> <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="" width="50"></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo number_format($row['price']); ?>₫</td>
                <td>
                    <?php if ($row['is_featured'] == 1): ?>
                        <span style="color: green; font-weight: bold;">Yes</span>
                    <?php else: ?>
                        <span style="color: #999;">No</span>
                    <?php endif; ?>
                </td>
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