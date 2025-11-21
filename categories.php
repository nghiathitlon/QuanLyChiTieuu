<?php
session_start(); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Kết nối DB
require 'db_connect.php'; 
$current_user_id = $_SESSION['user_id'];

$message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $message = "<p style='color:green;'>Thao tác thành công!</p>";
    } else if ($_GET['status'] == 'error') {
        $message = "<p style='color:red;'>Có lỗi xảy ra.</p>";
    }
}

// Lấy danh mục
$income_categories_result = $conn->query(
    "SELECT * FROM Categories WHERE user_id = $current_user_id AND type = 'income'"
);
$expense_categories_result = $conn->query(
    "SELECT * FROM Categories WHERE user_id = $current_user_id AND type = 'expense'"
);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        header h1 { margin:0; }
        nav a { text-decoration:none; color:#007bff; margin-left:10px; }
        .message { margin:10px 0; font-weight:bold; }
        form label { display:block; margin:5px 0 3px; font-weight:500; }
        form input, form select { padding:8px; width:100%; max-width:300px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc; }
        form button { padding:8px 16px; background:#1cc88a; color:white; border:none; border-radius:5px; cursor:pointer; }
        form button:hover { background:#17a673; }

        .search-bar { margin: 15px 0; }
        .search-bar input { padding:8px 12px; width:100%; max-width:400px; border-radius:8px; border:1px solid #ccc; }

        .category-container { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; margin-top:20px; }

        .category-card { background:#fff; padding:15px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); position:relative; transition:0.2s; }
        .category-card:hover { transform:translateY(-3px); box-shadow:0 6px 15px rgba(0,0,0,0.2); }
        .category-card h4 { margin:0 0 10px; font-size:1.1rem; display:flex; align-items:center; gap:5px; }
        .category-card .actions { position:absolute; top:10px; right:10px; }
        .category-card .actions a { margin-left:5px; text-decoration:none; font-size:0.9rem; color:#007bff; }
        .category-card .actions a:hover { text-decoration:underline; }

        .category-card.expense { border-left:5px solid #e74a3b; }
        .category-card.income { border-left:5px solid #4e73df; }

        /* Modal */
        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background:rgba(0,0,0,0.5); }
        .modal-content { background:#fff; margin:10% auto; padding:20px; border-radius:10px; max-width:400px; position:relative; }
        .modal-content .close { position:absolute; top:10px; right:15px; font-size:1.5rem; cursor:pointer; }
        .modal-content form input, .modal-content form select { width:100%; margin-bottom:10px; }
        .modal-content form button { width:100%; }
    </style>
</head>
<body>

<header>
    <h1>Quản lý Danh mục</h1>
    <div>
        <span>Chào, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
        <nav>
            <a href="dashboard.php">Bảng điều khiển</a>
            <a href="actions/action_logout.php">Đăng xuất</a>
        </nav>
    </div>
</header>

<?php echo $message; ?>

<!-- Form thêm danh mục -->
<section class="add-category">
    <h2>Thêm Danh mục mới</h2>
    <form action="actions/action_add_category.php" method="POST">
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
<div class="search-bar">
    <input type="text" id="categorySearch" placeholder="Tìm danh mục...">
</div>

<!-- Danh mục -->
<div class="category-container">
    <?php while($row = $expense_categories_result->fetch_assoc()): ?>
        <div class="category-card expense" data-name="<?php echo htmlspecialchars($row['name']); ?>">
            <h4><i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($row['name']); ?></h4>
            <div class="actions">
                <a href="#" class="edit-btn" data-id="<?php echo $row['category_id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">Sửa</a>
                <a href="actions/action_delete_category.php?id=<?php echo $row['category_id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
            </div>
        </div>
    <?php endwhile; ?>

    <?php while($row = $income_categories_result->fetch_assoc()): ?>
        <div class="category-card income" data-name="<?php echo htmlspecialchars($row['name']); ?>">
            <h4><i class="fas fa-hand-holding-dollar"></i> <?php echo htmlspecialchars($row['name']); ?></h4>
            <div class="actions">
                <a href="#" class="edit-btn" data-id="<?php echo $row['category_id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">Sửa</a>
                <a href="actions/action_delete_category.php?id=<?php echo $row['category_id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- Modal chỉnh sửa -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Chỉnh sửa danh mục</h3>
        <form id="editCategoryForm" method="POST" action="actions/action_edit_category.php">
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
    // Tìm kiếm danh mục
    document.getElementById('categorySearch').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        document.querySelectorAll('.category-card').forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            card.style.display = name.includes(searchValue) ? 'block' : 'none';
        });
    });

    // Hiển thị modal chỉnh sửa
    const modal = document.getElementById('editModal');
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('editCategoryId').value = this.dataset.id;
            document.getElementById('editCategoryName').value = this.dataset.name;
            // Lấy loại từ class parent
            const type = this.closest('.category-card').classList.contains('expense') ? 'expense' : 'income';
            document.getElementById('editCategoryType').value = type;
            modal.style.display = 'block';
        });
    });

    // Đóng modal
    document.querySelector('.modal .close').addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Click ngoài modal đóng
    window.addEventListener('click', (e) => {
        if(e.target === modal) modal.style.display = 'none';
    });
</script>

</body>
</html>

<?php $conn->close(); ?>
