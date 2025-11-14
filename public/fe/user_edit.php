<?php
// File: admin/fe/user_edit.php

// Lấy thông tin user cần sửa
$user_to_edit = null;
if (isset($_GET['id'])) {
    $id_to_edit = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_to_edit = $result->fetch_assoc();
}

// Nếu không tìm thấy user, quay về trang danh sách
if (!$user_to_edit) {
    echo "<p>User not found. <a href='?page=accounts'>Go back</a>.</p>";
    return; // Dừng không hiển thị form
}
?>
<div class="toast-container" id="toast-container"></div>
<h3><i class="fa-solid fa-user-pen"></i> Chỉnh sửa người dùng: <?php echo htmlspecialchars($user_to_edit['username']); ?></h3>
<div class="form-container">
    <form action="?page=accounts" method="POST">
        <input type="hidden" name="action" value="update_user">
        <input type="hidden" name="id" value="<?php echo $user_to_edit['id']; ?>">

        <div class="form-group">
            <label for="username">Họ tên</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_to_edit['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_to_edit['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Mật khẩu mới (Rời nếu không có thay đổi)</label>
            <input type="password" id="password" name="password">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-save"><i class="fa-solid fa-check"></i> Cập nhật người dùng mới</button>
            <a href="?page=accounts" class="btn-cancel">Hủy bỏ</a>
        </div>
    </form>
</div>
