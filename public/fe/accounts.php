<?php
// === START SESSION & DB ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../backend/core/db_connect.php';

// === XỬ LÝ ENABLE / DISABLE ===
if (isset($_GET['action'], $_GET['id']) && $_SESSION['role'] === 'admin') {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($id > 0) {
        if ($action === 'disable') {
            $stmt = $conn->prepare("UPDATE users SET status = 'disabled' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['toast_message'] = "Tài khoản đã được vô hiệu hóa.";
        } elseif ($action === 'enable') {
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['toast_message'] = "Tài khoản đã được kích hoạt.";
        }
    }
    header("Location: ?page=accounts");
    exit;
}

// === LẤY DỮ LIỆU TỪ DATABASE ===
$all_users = [];
$result = $conn->query("SELECT id, username, email, role, status FROM users ORDER BY role DESC, username ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_users[] = $row;
    }
}

$admins = array_filter($all_users, fn($user) => $user['role'] === 'admin');
$users = array_filter($all_users, fn($user) => $user['role'] === 'user');
?>
<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/accounts.css">
<div class="toast-container" id="toast-container"></div>

<div class="account-management-grid">
    <!-- ==== ADMIN LIST ==== -->
    <div class="account-column">
        <div class="column-header">
            <h3><i class="fa-solid fa-user-shield"></i> Tài khoản Admin</h3>
            <a href="?page=accounts&action=add" class="btn btn-primary btn-add">
                <i class="fa-solid fa-user-plus"></i> Thêm Admin
            </a>
        </div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Trạng thái</th>
                    <th>Chỉnh sửa</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($admins)): ?>
                    <tr><td colspan="5" style="text-align:center;">Không có tài khoản admin nào.</td></tr>
                <?php else: $stt=1; foreach($admins as $admin): ?>
                <tr>
                    <td class="text-center"><?= $stt++ ?></td>
                    <td><strong><?= htmlspecialchars($admin['username']) ?></strong></td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td>
                        <?php
                        $status_class = $admin['status'] === 'active' ? 'status-active' : 'status-disabled';
                        $status_text = ucfirst($admin['status']);
                        ?>
                        <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                    </td>
                    <td class="actions text-center">
                        <a href="?page=accounts&action=edit&id=<?= $admin['id'] ?>" title="Chỉnh sửa"><i class="fa-solid fa-pencil"></i></a>
                        <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                            <?php if ($admin['status'] === 'active'): ?>
                                <a href="?page=accounts&action=disable&id=<?= $admin['id'] ?>" class="action-disable" title="Vô hiệu hóa"><i class="fa-solid fa-user-slash"></i></a>
                            <?php else: ?>
                                <a href="?page=accounts&action=enable&id=<?= $admin['id'] ?>" class="action-enable" title="Kích hoạt"><i class="fa-solid fa-user-check"></i></a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ==== USER LIST ==== -->
    <div class="account-column">
        <div class="column-header">
            <h3><i class="fa-solid fa-user-group"></i> Tài khoản Người dùng</h3>
        </div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Trạng thái</th>
                    <th>Chỉnh sửa</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" style="text-align:center;">Không có tài khoản người dùng nào.</td></tr>
                <?php else: $stt=1; foreach($users as $user): ?>
                <tr>
                    <td class="text-center"><?= $stt++ ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <?php
                        $status_class = $user['status'] === 'active' ? 'status-active' : 'status-disabled';
                        $status_text = ucfirst($user['status']);
                        ?>
                        <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                    </td>
                    <td class="actions text-center">
                        <a href="?page=accounts&action=edit&id=<?= $user['id'] ?>" title="Chỉnh sửa"><i class="fa-solid fa-pencil"></i></a>
                        <?php if ($user['status'] === 'active'): ?>
                            <a href="?page=accounts&action=disable&id=<?= $user['id'] ?>" class="action-disable" title="Vô hiệu hóa"><i class="fa-solid fa-user-slash"></i></a>
                        <?php else: ?>
                            <a href="?page=accounts&action=enable&id=<?= $user['id'] ?>" class="action-enable" title="Kích hoạt"><i class="fa-solid fa-user-check"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ==== TOAST NOTIFICATION ==== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    <?php if (isset($_SESSION['toast_message'])): ?>
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = <?= json_encode($_SESSION['toast_message']) ?>;
        document.getElementById('toast-container').appendChild(toast);

        setTimeout(() => toast.remove(), 3000); // 3s
        <?php unset($_SESSION['toast_message']); ?>
    <?php endif; ?>
});
</script>

<style>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}
.toast {
    background: #333;
    color: #fff;
    padding: 10px 20px;
    margin-bottom: 10px;
    border-radius: 5px;
    opacity: 0.9;
}
</style>
