<?php
?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'exists'): ?>
    <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <strong>Error:</strong> Username or Email already exists. Please choose a different one.
    </div>
<?php endif; ?>
<div class="toast-container" id="toast-container"></div>
<h3><i class="fa-solid fa-user-plus"></i> Thêm tài khoản admin mới</h3>
<div class="form-container">
    <form action="?page=accounts" method="POST">
        <input type="hidden" name="action" value="save_admin">

        <div class="form-group">
            <label for="username">Họ tên</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-save"><i class="fa-solid fa-check"></i> Lưu tài khoản</button>
            <a href="?page=accounts" class="btn-cancel">Hủy bỏ</a>
        </div>
    </form>
</div>