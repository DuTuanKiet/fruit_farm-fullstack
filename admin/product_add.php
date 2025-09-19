<?php
// File này được include bởi products.php
?>
<h3>Add New Product</h3>
<div class="form-container">
<!-- Bắt buộc! Thuộc tính này cho phép form gửi được dữ liệu file lên máy chủ. Nếu thiếu, chức năng upload sẽ không hoạt động -->
    <form action="?page=products" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_new">

    <div class="form-group">
        <label for="name">Product Name</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div class="form-group">
        <label for="price">Price (₫)</label>
        <input type="number" id="price" name="price" required>
    </div>
    
    <div class="form-group">
        <label for="product_image">Product Image</label>
        <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg, image/gif" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"></textarea>
    </div>
    
    <div class="form-group checkbox-group">
        <input type="checkbox" id="is_featured" name="is_featured" value="1">
        <label for="is_featured">Show on Homepage (Featured Product)</label>
    </div>

    <button type="submit" class="btn">Save Product</button>
    <a href="?page=products" style="margin-left: 10px;">Cancel</a>
</form>
</div>