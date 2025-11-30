<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/core/db_connect.php';

// Lấy danh sách danh mục
$sql = "SELECT id, name, slug, created_at FROM categories ORDER BY created_at ASC";
$result = mysqli_query($conn, $sql);

$categories = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý Danh mục</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/categories.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="page-container">
  <h2 class="page-title"><i class="fa-solid fa-list"></i> Quản lý Danh mục</h2>

  <table class="styled-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Tên danh mục</th>
        <th>Slug</th>
        <th>Ngày tạo</th>
        <th>Hành động</th>
      </tr>
      <!-- Hàng thêm mới -->
      <tr class="add-row">
        <td>#</td>
        <td><input type="text" id="newName" placeholder="Tên danh mục"></td>
        <td><input type="text" id="newSlug" placeholder="Slug"></td>
        <td>-</td>
        <td><button id="addCategoryBtn" class="btn btn-add"><i class="fa-solid fa-plus"></i> Thêm</button></td>
      </tr>
    </thead>
    <tbody id="categoriesBody">
      <?php foreach ($categories as $cat): ?>
        <tr data-id="<?= $cat['id'] ?>">
          <td><?= $cat['id'] ?></td>
          <td><span class="display"><?= htmlspecialchars($cat['name']) ?></span>
              <input class="edit-input" type="text" value="<?= htmlspecialchars($cat['name']) ?>" style="display:none;"></td>
          <td><span class="display"><?= htmlspecialchars($cat['slug']) ?></span>
              <input class="edit-input" type="text" value="<?= htmlspecialchars($cat['slug']) ?>" style="display:none;"></td>
          <td><?= $cat['created_at'] ?></td>
          <td>
            <button class="btn btn-edit editBtn"><i class="fa-solid fa-pen"></i></button>
            <button class="btn btn-save saveBtn" style="display:none;"><i class="fa-solid fa-floppy-disk"></i></button>
            <button class="btn btn-delete deleteBtn"><i class="fa-solid fa-trash"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
$(document).ready(function(){
  const apiPath = '../../backend/admin/categories_api.php';

  // Thêm danh mục mới
  $('#addCategoryBtn').click(function(){
    const name = $('#newName').val().trim();
    const slug = $('#newSlug').val().trim();
    if(name === "" || slug === "") { alert("Vui lòng nhập đầy đủ"); return; }

    $.post(apiPath, {action: 'add', name: name, slug: slug}, function(res){
        let data = typeof res === 'string' ? JSON.parse(res) : res;
        if(data.status === 'success'){
          const cat = data.category;
          $('#categoriesBody').append(`
            <tr data-id="${cat.id}">
              <td>${cat.id}</td>
              <td><span class="display">${cat.name}</span>
                  <input class="edit-input" type="text" value="${cat.name}" style="display:none;"></td>
              <td><span class="display">${cat.slug}</span>
                  <input class="edit-input" type="text" value="${cat.slug}" style="display:none;"></td>
              <td>${cat.created_at}</td>
              <td>
                <button class="btn btn-edit editBtn"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-save saveBtn" style="display:none;"><i class="fa-solid fa-floppy-disk"></i></button>
                <button class="btn btn-delete deleteBtn"><i class="fa-solid fa-trash"></i></button>
              </td>
            </tr>
          `);
          $('#newName').val(''); $('#newSlug').val('');
        } else { alert(data.message); }
    }, 'json').fail(function(xhr){ alert("Lỗi kết nối API: "+xhr.status); });
  });

  // Sửa inline
  $(document).on('click', '.editBtn', function(){
    const tr = $(this).closest('tr');
    tr.find('.display').hide();
    tr.find('.edit-input').show();
    tr.find('.editBtn').hide();
    tr.find('.saveBtn').show();
  });

  // Lưu sửa
  $(document).on('click', '.saveBtn', function(){
    const tr = $(this).closest('tr');
    const id = tr.data('id');
    const name = tr.find('input').eq(0).val().trim();
    const slug = tr.find('input').eq(1).val().trim();
    if(name === "" || slug === "") { alert("Vui lòng nhập đầy đủ"); return; }

    function sendEdit(confirmUpdate=0){
        $.post(apiPath, {action:'edit', id:id, name:name, slug:slug, confirm:confirmUpdate}, function(res){
            let data = typeof res === 'string' ? JSON.parse(res) : res;
            if(data.status === 'success'){
                tr.find('.display').eq(0).text(name);
                tr.find('.display').eq(1).text(slug);
                tr.find('.display').show();
                tr.find('.edit-input').hide();
                tr.find('.editBtn').show();
                tr.find('.saveBtn').hide();
            } else if(data.status === 'warning'){
                if(confirm(data.message)){
                    sendEdit(1);
                }
            } else {
                alert(data.message);
            }
        }, 'json').fail(function(xhr){ alert("Lỗi kết nối API: "+xhr.status); });
    }

    sendEdit();
  });

  // Xóa
  $(document).on('click', '.deleteBtn', function(){
    if(!confirm('Bạn có chắc muốn xóa danh mục này?')) return;
    const tr = $(this).closest('tr');
    const id = tr.data('id');
    $.post(apiPath, {action:'delete', id:id}, function(res){
      let data = typeof res === 'string' ? JSON.parse(res) : res;
      if(data.status === 'success'){ tr.remove(); }
      else{ alert(data.message); }
    }, 'json').fail(function(xhr){ alert("Lỗi kết nối API: "+xhr.status); });
  });

});
</script>
</body>
</html>
