<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/../../backend/core/db_connect.php';

$feedbacks=[];
$sql="SELECT id, name, email, subject, message, created_at, status FROM feedback ORDER BY created_at DESC";
$result=$conn->query($sql);
if($result && $result->num_rows>0){
    while($row=$result->fetch_assoc()){
        $feedbacks[]=$row;
    }
}

function format_time_ago($timestamp){
    $time_ago=strtotime($timestamp);
    $diff=time()-$time_ago;
    if($diff<60) return "Vừa xong";
    $minutes=round($diff/60);
    if($minutes<60) return $minutes." phút trước";
    $hours=round($diff/3600);
    if($hours<24) return $hours." giờ trước";
    $days=round($diff/86400);
    if($days==1) return "Hôm qua lúc ".date('H:i',$time_ago);
    return date('d/m/Y',$time_ago);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Feedback</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/feedback.css">
</head>
<body>

<div class="feedback-container">
    <div class="feedback-list-wrapper">
        <h3>Hộp thư đến</h3>
        <?php if(!empty($feedbacks)): ?>
            <?php foreach($feedbacks as $fb): ?>
            <div class="feedback-item" 
                 data-id="<?= $fb['id'] ?>"
                 data-name="<?= htmlspecialchars($fb['name']) ?>"
                 data-email="<?= htmlspecialchars($fb['email']) ?>"
                 data-subject="<?= htmlspecialchars($fb['subject']) ?>"
                 data-message="<?= htmlspecialchars($fb['message']) ?>"
                 data-status="<?= $fb['status'] ?>"
            >
                <div class="sender-summary">
                    <span>
                        <?php if($fb['status']==='new'): ?><span class="unread-dot"></span><?php endif; ?>
                        <span class="sender-name"><?= htmlspecialchars($fb['name']) ?></span>
                    </span>
                    <small><?= format_time_ago($fb['created_at']) ?></small>
                </div>
                <div class="sender-email"><?= htmlspecialchars($fb['email']) ?></div>
                <p><?= htmlspecialchars($fb['subject']) ?></p>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Không có feedback nào.</p>
        <?php endif; ?>
    </div>

    <div class="feedback-detail-wrapper">
        <div id="feedback-placeholder" class="feedback-placeholder">
            <i class="fas fa-envelope-open-text fa-3x"></i>
            <p>Chọn một feedback để xem chi tiết</p>
        </div>

        <div id="feedback-content" style="display:none;">
            <div class="detail-header">
                <div>
                    <h2 id="detail-subject"></h2>
                    <div class="sender-info">
                        <strong id="detail-name"></strong> &lt;<a id="detail-email" href="mailto:"></a>&gt;
                    </div>
                </div>
            </div>

            <div id="detail-body" class="detail-body"></div>

            <div class="reply-section">
                <h4>Trả lời Feedback</h4>
                <form id="reply-form">
                    <input type="hidden" id="reply-id" name="id">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" id="name" readonly>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="email" readonly>
                    </div>
                    <div class="form-group">
                        <label>Chủ đề</label>
                        <input type="text" id="subject" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nội dung trả lời</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="submit-button">Gửi phản hồi</button>
                </form>
                <div id="reply-display" class="reply-message" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    function safeSetText($el, text){
        if($el && $el.length) $el.text(text);
    }
    function safeSetHref($el, href, text){
        if($el && $el.length){
            $el.attr('href', href).text(text);
        }
    }

    $('.feedback-item').click(function(){
        const $item = $(this);
        const id = $item.data('id');
        const name = $item.data('name');
        const email = $item.data('email');
        const subject = $item.data('subject');
        const message = $item.data('message');

        const $placeholder = $('#feedback-placeholder');
        const $content = $('#feedback-content');
        const $detailSubject = $('#detail-subject');
        const $detailName = $('#detail-name');
        const $detailEmail = $('#detail-email');
        const $detailBody = $('#detail-body');

        if($placeholder.length) $placeholder.hide();
        if($content.length) $content.show();

        safeSetText($detailSubject, subject);
        safeSetText($detailName, name);
        safeSetHref($detailEmail, 'mailto:'+email, email);
        safeSetText($detailBody, message);

        $('#reply-id').val(id);
        $('#name').val(name);
        $('#email').val(email);
        $('#subject').val(subject);
        $('#message').val('');
        $('#reply-display').hide().text('');

        // đánh dấu đã đọc
        if($item.data('status') === 'new'){
            $.ajax({
                url: '<?= BASE_URL ?>backend/admin/feedback_api.php',
                method: 'POST',
                data: { action: 'mark_read', id: id },
                dataType: 'json',
                success: function(){ /* không cần thao tác gì thêm */ },
                error: function(xhr){ console.error('Lỗi mark_read:', xhr.status); }
            });
            $item.data('status','read');
            $item.find('.unread-dot').remove();
        }
    });

    // submit reply
    $('#reply-form').submit(function(e){
        e.preventDefault();
        const id = $('#reply-id').val();
        const msg = $('#message').val().trim();
        if(!msg){ alert('Vui lòng nhập nội dung phản hồi'); return; }

        $.ajax({
            url: '<?= BASE_URL ?>backend/admin/feedback_api.php',
            method: 'POST',
            data: { action:'reply', id:id, message: msg },
            dataType: 'json',
            success: function(res){
                if(res.status === 'success'){
                    alert('Đã gửi phản hồi!');
                    $('#message').val('');
                    $('#reply-display').show().text(msg);
                } else {
                    alert('Lỗi: ' + (res.message || 'Không xác định'));
                }
            },
            error: function(xhr){
                alert('Lỗi kết nối API: '+xhr.status);
            }
        });
    });
});
</script>
</body>
</html>
