<?php
require_once 'admin_auth.php'; // Yêu cầu đăng nhập quyền admin


$page = $_GET['page'] ?? 'dashboard'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fruit Farm</title>
    <link rel="stylesheet" href="/fruitfarm/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
    <div class="admin-wrapper">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>🥑 Admin</h2>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="?page=accounts" class="<?= $page == 'accounts' ? 'active' : '' ?>">
                        <i class="fa-solid fa-users"></i> Account Management
                    </a>
                </li>
                <li>
                    <a href="?page=products" class="<?= $page == 'products' ? 'active' : '' ?>">
                        <i class="fa-solid fa-box-archive"></i> Product Management
                    </a>
                </li>
                <li>
                    <a href="?page=reports" class="<?= $page == 'reports' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-line"></i> Report & Statistics
                    </a>
                </li>
                 <li>
                    <a href="?page=feedback" class="<?= $page == 'feedback' ? 'active' : '' ?>">
                        <i class="fa-solid fa-message"></i> Feedback
                    </a>
                </li>
            </ul>
            
        </div>

        <main class="main-content">
            <header class="main-header">
                <h1>
                    <?php 
                        // Hiển thị tiêu đề trang tương ứng
                        switch ($page) {
                            case 'accounts': echo "Account Management"; break;
                            case 'products': echo "Product Management"; break;
                            case 'reports': echo "Reports & Statistics"; break;
                            case 'feedback': echo "Feedback Management"; break;
                            default: echo "Dashboard"; break;
                        }
                    ?>
                </h1>
                <div class="user-info">
    <span>Xin chào, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
    <a href="../php/logout.php" class="logout-link-simple">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>
            </header>
            
            <div class="content-wrapper">
                <?php
                    // Nạp nội dung trang tương ứng
                    switch ($page) {
                        case 'accounts':
                            include 'accounts.php';
                            break;
                        case 'products':
                            include 'products.php';
                            break;
                        case 'reports':
                            include 'reports.php';
                            break;
                        case 'feedback':
                            include 'feedback.php';
                            break;
                        default:
                            include 'dashboard_content.php';
                            break;
                    }
                ?>
            </div>
        </main>
    </div>
</body>
</html>