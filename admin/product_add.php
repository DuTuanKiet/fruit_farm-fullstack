<?php
// admin/product_add.php
// File này được include bởi products.php
?>
<h3>Add New Product</h3>
<div class="form-container">
    <form action="?page=products" method="POST">
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
            <label for="image_url">Image URL</label>
            <input type="text" id="image_url" name="image_url" placeholder="e.g., images/product-name.jpg" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        <button type="submit" class="btn">Save Product</button>
        <a href="?page=products" style="margin-left: 10px;">Cancel</a>
    </form>
</div>