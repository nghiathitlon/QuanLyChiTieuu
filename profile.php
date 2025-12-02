<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
require 'header.php';

$user_id = $_SESSION['user_id'];

// Lấy thông tin người dùng
$query = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $query->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Thông tin cá nhân nâng cao</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .profile-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-container label {
            display: block;
            margin: 10px 0 5px;
        }
        .profile-container input[type="text"],
        .profile-container input[type="email"],
        .profile-container input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        .profile-container button {
            padding: 10px 20px;
            background-color: #2d89ef;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .profile-container button:hover {
            background-color: #1b5dab;
        }
        .status-message {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Thông tin cá nhân</h2>

        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo "<p class='status-message' style='color:green;'>Cập nhật thành công!</p>";
            } elseif ($_GET['status'] == 'error') {
                echo "<p class='status-message' style='color:red;'>Có lỗi xảy ra, vui lòng thử lại.</p>";
            }
        }
        ?>

        <form action="actions/action_update_profile.php" method="POST" enctype="multipart/form-data">
            <div style="text-align:center; margin-bottom:15px;">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                <?php else: ?>
                    <img src="uploads/default.png" alt="Avatar">
                <?php endif; ?>

            </div>

            <label>Thay đổi ảnh đại diện:</label>
            <input type="file" name="avatar" accept="image/*">

            <label>Họ và tên:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label>Mật khẩu mới (bỏ trống nếu không đổi):</label>
            <input type="password" name="password">

            <label>Vai trò:</label>
            <input type="text" value="<?php echo isset($user['role']) ? htmlspecialchars($user['role']) : ''; ?>" disabled>


            <button type="submit">Cập nhật</button>
        </form>
    </div>
</body>
</html>

<?php require 'footer.php'; ?>
<?php require 'header.php'; ?>