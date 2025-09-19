<?php
// FILE: admin/products.php (PHIÊN BẢN ĐÃ SỬA LỖI HOÀN CHỈNH)

// --- PHẦN 1: XỬ LÝ DỮ LIỆU FORM KHI GỬI LÊN (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // === XỬ LÝ LƯU SẢN PHẨM MỚI ===
    if ($action === 'save_new') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        $image_path_for_db = "";
        if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
            $target_dir = "../images/";
            $file_name = basename($_FILES["product_image"]["name"]);
            $new_file_name = uniqid() . '_' . $file_name;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image_path_for_db = "images/" . $new_file_name;
            }
        }
        
        if (!empty($image_path_for_db)) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, image_url, description, is_featured) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssi", $name, $price, $image_path_for_db, $description, $is_featured);
            if ($stmt->execute()) {
                echo "<script>alert('Product added successfully!'); window.location.href='?page=products';</script>";
            } else {
                echo "<script>alert('Error adding product to database.');</script>";
            }
            exit;
        } else {
             echo "<script>alert('Image upload failed. Product not saved.'); window.location.href='?page=products&action=add';</script>";
             exit;
        }
    }

    // === XỬ LÝ CẬP NHẬT SẢN PHẨM ===
    if ($action === 'update') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $current_image_url = $_POST['current_image_url'];

        $image_path_for_db = $current_image_url;

        if (isset($_FILES["product_image"]) && $_FILES["product_image"]["size"] > 0) {
            $target_dir = "../images/";
            $file_name = basename($_FILES["product_image"]["name"]);
            $new_file_name = uniqid() . '_' . $file_name;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image_path_for_db = "images/" . $new_file_name;
                if (!empty($current_image_url) && file_exists("../" . $current_image_url)) {
                    unlink("../" . $current_image_url);
                }
            }
        }

        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image_url = ?, description = ?, is_featured = ? WHERE id = ?");
        $stmt->bind_param("sdssii", $name, $price, $image_path_for_db, $description, $is_featured, $id);
        $stmt->execute();
        echo "<script>alert('Product updated successfully!'); window.location.href='?page=products';</script>";
        exit;
    }
}

// --- PHẦN 2: XỬ LÝ CÁC HÀNH ĐỘNG GET (HIỂN THỊ TRANG) ---
$action = $_GET['action'] ?? 'list';

if ($action === 'delete' && isset($_GET['id'])) {
    // Logic xóa sản phẩm (đã đúng)
    $id_to_delete = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    echo "<script>alert('Product deleted!'); window.location.href='?page=products';</script>";
    exit;
}

// --- PHẦN 3: HIỂN THỊ GIAO DIỆN TƯƠNG ỨNG ---
if ($action === 'add') {
    include 'product_add.php';
} elseif ($action === 'edit' && isset($_GET['id'])) {
    // Chỉ LẤY DỮ LIỆU (SELECT) để hiển thị form
    $id_to_edit = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
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
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" width="60" style="border-radius: 5px;"></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo number_format($row['price']); ?>₫</td>
                <td><?php echo $row['is_featured'] == 1 ? '<span style="color: green; font-weight: bold;">Yes</span>' : '<span style="color: #999;">No</span>'; ?></td>
                <td class="actions">
                    <a href="?page=products&action=edit&id=<?php echo $row['id']; ?>">Edit</a>
                    <a href="?page=products&action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php
}
?>