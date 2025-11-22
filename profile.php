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
    <title>Thông tin cá nhân</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h2>Thay đổi thông tin cá nhân</h2>

    <?php
        if (isset($_GET['status']) && $_GET['status'] == 'success') {
            echo "<p style='color:green;'>Cập nhật thành công!</p>";
        }
        if (isset($_GET['status']) && $_GET['status'] == 'error') {
            echo "<p style='color:red;'>Có lỗi xảy ra, vui lòng thử lại.</p>";
        }
    ?>

    <form action="actions/action_update_profile.php" method="POST">
        <label>Họ tên:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>Mật khẩu mới (bỏ trống nếu không đổi):</label>
        <input type="password" name="password">

        <button type="submit">Cập nhật</button>
    </form>

<?php require 'footer.php'; ?>
</body>
</html>
