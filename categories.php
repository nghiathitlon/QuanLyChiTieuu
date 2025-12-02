<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
$user_id = $_SESSION['user_id'];

// Lấy danh mục
$income_categories = $conn->query("SELECT * FROM categories WHERE user_id=$user_id AND type='income' ORDER BY name ASC");
$expense_categories = $conn->query("SELECT * FROM categories WHERE user_id=$user_id AND type='expense' ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý danh mục</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* Body & container */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f4f8;
    margin: 0;
    padding: 20px;
    color: #333;
}

h2 {
    text-align: center;
    color: #4e73df;
    margin-bottom: 20px;
}

/* Message */
#message {
    margin: 15px 0;
    font-weight: 600;
    text-align: center;
}

/* Form thêm danh mục */
form#addCategoryForm {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    max-width: 500px;
    margin: 0 auto 30px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

form#addCategoryForm input,
form#addCategoryForm select,
form#addCategoryForm button {
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

form#addCategoryForm input:focus,
form#addCategoryForm select:focus {
    outline: none;
    border-color: #4e73df;
    box-shadow: 0 0 6px rgba(78,115,223,0.4);
}

form#addCategoryForm button {
    background: linear-gradient(45deg, #4e73df, #2e59d9);
    color: #fff;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}

form#addCategoryForm button:hover {
    background: linear-gradient(45deg, #2e59d9, #1d4ed8);
    transform: scale(1.02);
}

/* Search box */
#categorySearch {
    width: 100%;
    max-width: 400px;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    margin: 15px auto;
    display: block;
    font-size: 14px;
}

#categorySearch:focus {
    outline: none;
    border-color: #4e73df;
    box-shadow: 0 0 6px rgba(78,115,223,0.4);
}

/* Grid danh mục */
.category-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

/* Card danh mục */
.category-card {
    background: #fff;
    padding: 15px 18px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    word-break: break-word;
}

.category-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
}

.category-card.expense { border-left: 6px solid #e74a3b; }
.category-card.income  { border-left: 6px solid #4e73df; }

/* Card header */
.category-card h4 {
    margin: 0;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #333;
}

/* Actions (Sửa/Xóa) */
.category-card .actions {
    display: flex;
    gap: 8px;
    margin-top: 8px;
}

.category-card .actions a {
    font-size: 0.9rem;
    color: #4e73df;
    text-decoration: none;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 6px;
    transition: 0.2s;
}

.category-card .actions a:hover {
    background: rgba(78,115,223,0.1);
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: #fff;
    margin: 8% auto;
    padding: 20px;
    border-radius: 12px;
    max-width: 400px;
    position: relative;
    box-shadow: 0 6px 25px rgba(0,0,0,0.12);
}

.modal-content .close {
    position: absolute;
    top: 10px; right: 15px;
    font-size: 1.5rem;
    cursor: pointer;
    color: #888;
    transition: 0.2s;
}

.modal-content .close:hover { color: #333; }

.modal-content form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.modal-content form input,
.modal-content form select,
.modal-content form button {
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.modal-content form input:focus,
.modal-content form select:focus {
    outline: none;
    border-color: #4e73df;
    box-shadow: 0 0 6px rgba(78,115,223,0.4);
}

.modal-content form button {
    background: linear-gradient(45deg, #4e73df, #2e59d9);
    color: #fff;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

.modal-content form button:hover {
    background: linear-gradient(45deg, #2e59d9, #1d4ed8);
    transform: scale(1.02);
}

/* Responsive */
@media(max-width:600px){
    .category-container { grid-template-columns: 1fr; }
    form#addCategoryForm, .modal-content { width: 90%; }
}
</style>

</head>
<body>

<?php require 'header.php'; ?>

<div id="message"></div>

<!-- Thêm danh mục -->
<section>
<h2>Thêm danh mục mới</h2>
<form id="addCategoryForm">
<label>Tên danh mục:</label>
<input type="text" name="name" placeholder="Ví dụ: Ăn trưa" required>
<label>Loại danh mục:</label>
<select name="type" required>
<option value="expense">Chi tiêu</option>
<option value="income">Thu nhập</option>
</select>
<button type="submit">Thêm mới</button>
</form>
</section>

<hr>

<!-- Search -->
<div>
<input type="text" id="categorySearch" placeholder="Tìm danh mục...">
</div>

<!-- Danh mục -->
<div class="category-container" id="categoryList">

<?php foreach($expense_categories as $row): ?>
<div class="category-card expense" data-id="<?= $row['category_id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>">
    <h4><i class="fas fa-money-bill-wave"></i> <?= htmlspecialchars($row['name']) ?></h4>
    <div class="actions">
        <a href="#" class="edit-btn">Sửa</a>
        <a href="#" class="delete-btn">Xóa</a>
    </div>
</div>
<?php endforeach; ?>

<?php foreach($income_categories as $row): ?>
<div class="category-card income" data-id="<?= $row['category_id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>">
    <h4><i class="fas fa-hand-holding-dollar"></i> <?= htmlspecialchars($row['name']) ?></h4>
    <div class="actions">
        <a href="#" class="edit-btn">Sửa</a>
        <a href="#" class="delete-btn">Xóa</a>
    </div>
</div>
<?php endforeach; ?>

</div>

<!-- Modal chỉnh sửa -->
<div id="editModal" class="modal">
<div class="modal-content">
<span class="close">&times;</span>
<h3>Chỉnh sửa danh mục</h3>

<form id="editCategoryForm">
<input type="hidden" name="category_id" id="editCategoryId">

<label>Tên danh mục:</label>
<input type="text" name="name" id="editCategoryName" required>

<label>Loại danh mục:</label>
<select name="type" id="editCategoryType" required>
<option value="expense">Chi tiêu</option>
<option value="income">Thu nhập</option>
</select>

<button type="submit">Lưu thay đổi</button>
</form>
</div>
</div>

<script>
// Search
document.getElementById('categorySearch').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.category-card').forEach(card=>{
        card.style.display = card.dataset.name.toLowerCase().includes(val) ? 'block':'none';
    });
});

// Modal
const modal = document.getElementById('editModal');
const editForm = document.getElementById('editCategoryForm');

document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.addEventListener('click', e=>{
        e.preventDefault();
        const card = btn.closest('.category-card');
        document.getElementById('editCategoryId').value = card.dataset.id;
        document.getElementById('editCategoryName').value = card.dataset.name;
        document.getElementById('editCategoryType').value =
            card.classList.contains('expense') ? 'expense' : 'income';

        modal.style.display='block';
    });
});

document.querySelector('.modal .close').addEventListener('click',()=>modal.style.display='none');
window.addEventListener('click', e=>{ if(e.target===modal) modal.style.display='none'; });

// AJAX thêm danh mục
document.getElementById('addCategoryForm').addEventListener('submit', function(e){
    e.preventDefault();
    const data = new FormData(this);

    fetch('actions/action_add_category.php', {method:'POST', body:data})
    .then(res=>res.json())
    .then(res=>{
        if(res.ok){ location.reload(); }
        else document.getElementById('message').innerHTML = '<span style="color:red">'+res.msg+'</span>';
    });
});

// AJAX sửa danh mục
editForm.addEventListener('submit', function(e){
    e.preventDefault();
    const data = new FormData(this);

    fetch('actions/action_edit_category.php', {method:'POST', body:data})
    .then(res=>res.json())
    .then(res=>{
        if(res.ok) location.reload();
        else document.getElementById('message').innerHTML = '<span style="color:red">'+res.msg+'</span>';
    });
});

// AJAX xóa danh mục
document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.addEventListener('click', e=>{
        e.preventDefault();
        if(!confirm('Bạn có chắc chắn muốn xóa?')) return;

        const card = btn.closest('.category-card');
        const data = new FormData();
        data.append('category_id', card.dataset.id);

        fetch('actions/action_delete_category.php', {method:'POST', body:data})
        .then(res=>res.json())
        .then(res=>{
            if(res.ok) location.reload();
            else document.getElementById('message').innerHTML = '<span style="color:red">'+res.msg+'</span>';
        });
    });
});
</script>

</body>
</html>

<?php $conn->close(); ?>
