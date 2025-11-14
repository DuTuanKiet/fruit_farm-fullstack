<?php
$feedbacks = [];
// Câu lệnh SQL để lấy tất cả feedback, sắp xếp cái mới nhất lên đầu
$sql = "SELECT id, name, email, subject, message, created_at, status FROM feedback ORDER BY created_at DESC";

// Thực thi câu truy vấn
$result = $conn->query($sql);

// Kiểm tra và lặp qua kết quả để đưa vào mảng $feedbacks
if ($result && $result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
  }
}
// --- KẾT THÚC LẤY DỮ LIỆU ---

function format_time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes      = round($seconds / 60);
    $hours           = round($seconds / 3600);
    $days          = round($seconds / 86400);

    if ($seconds <= 60) { return "Vừa xong"; }
    else if ($minutes <= 60) { return $minutes == 1 ? "1 phút trước" : "$minutes phút trước"; }
    else if ($hours <= 24) { return $hours == 1 ? "1 giờ trước" : "$hours giờ trước"; }
    else if ($days == 1) { return "Hôm qua lúc " . date('H:i', $time_ago); }
    else { return date('d/m/Y', $time_ago); }
}
?>
<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/feedback.css">
<div class="toast-container" id="toast-container"></div>
<div class="feedback-container">
    
    <!-- Cột Danh sách Feedback -->
    <div class="feedback-list-wrapper">
        <div class="feedback-list-header">
            <h3>Hộp thư đến</h3>
        </div>
        <ul class="feedback-list">
            <?php if (!empty($feedbacks)): ?>
                <?php foreach ($feedbacks as $feedback): ?>
                <li class="feedback-item" 
                    data-id="<?php echo $feedback['id']; ?>"
                    data-name="<?php echo htmlspecialchars($feedback['name']); ?>"
                    data-email="<?php echo htmlspecialchars($feedback['email']); ?>"
                    data-subject="<?php echo htmlspecialchars($feedback['subject']); ?>"
                    data-message="<?php echo htmlspecialchars($feedback['message']); ?>"
                    data-time="<?php echo $feedback['created_at']; ?>"
                    data-status="<?php echo $feedback['status']; ?>"
                    >
                    <?php if ($feedback['status'] === 'new'): ?>
                        <span class="unread-dot"></span>
                    <?php endif; ?>
                    <div class="item-header">
                        <span class="item-name"><?php echo htmlspecialchars($feedback['name']); ?></span>
                        <span class="item-time"><?php echo format_time_ago($feedback['created_at']); ?></span>
                    </div>
                    <p class="item-subject"><?php echo htmlspecialchars($feedback['subject']); ?></p>
                </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="no-feedback">Không có feedback nào.</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Cột Chi tiết Feedback -->
    <div class="feedback-detail-wrapper">
        <div id="feedback-placeholder">
            <i class="fas fa-envelope-open-text"></i>
            <p>Chọn một feedback để xem chi tiết</p>
        </div>
        
        <div id="feedback-content">
            <div class="detail-header">
                <h2 id="detail-subject" class="detail-subject"></h2>
                <div class="sender-info">
                    <i class="fas fa-user-circle"></i>
                    <strong id="detail-name"></strong>
                    <span>&lt;<a id="detail-email" href="#"></a>&gt;</span>
                </div>
            </div>
            <div id="detail-body" class="detail-body"></div>
            <div class="detail-actions">
                <a id="btn-reply" href="#" class="btn-action btn-reply"><i class="fas fa-reply"></i> Trả lời</a>
                <a id="btn-delete" href="#" class="btn-action btn-delete"><i class="fas fa-trash"></i> Xóa</a>
            </div>
        </div>
    </div>
</div>

