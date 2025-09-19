<?php
// Trang quản lí sản phẩm

// Lấy id sản phẩm từ URL và lấy thông tin từ CSDL
$product_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
?>
<h3>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h3>
<div class="form-container">
    <form action="?page=products" method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="price">Price (₫)</label>
            <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        </div>
        <div class="form-group">
            <label for="image_url">Image URL</label>
            <input type="text" id="image_url" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <button type="submit" class="btn">Update Product</button>
        <a href="?page=products" style="margin-left: 10px;">Cancel</a>
    </form>
</div>