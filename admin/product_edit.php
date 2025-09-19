<?php
// File này được include bởi products.php khi action là 'edit'
// Biến $product đã được lấy từ products.php
?>
<h3>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h3>
<div class="form-container">
    <form action="?page=products" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>">

        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="price">Price (₫)</label>
            <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        </div>

        <div class="form-group">
            <label>Current Image</label>
            <div>
                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current Image" width="150" style="border-radius: 5px; border: 1px solid #ddd;">
            </div>
        </div>
        
        <div class="form-group">
            <label for="product_image">Change Image (optional)</label>
            <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg, image/gif">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <div class="form-group checkbox-group">
            <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php if ($product['is_featured'] == 1) echo 'checked'; ?>>
            <label for="is_featured">Show on Homepage (Featured Product)</label>
        </div>
        
        <button type="submit" class="btn">Update Product</button>
        <a href="?page=products" style="margin-left: 10px;">Cancel</a>
    </form>
</div>