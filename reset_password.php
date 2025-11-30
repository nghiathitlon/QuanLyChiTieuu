<?php
// reset_password.php

// 1. Kết nối database
$conn = new mysqli("localhost","root","","personal_finance_db");
$conn->set_charset("utf8");

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối CSDL thất bại: " . $conn->connect_error);
}

// 2. Lấy token từ URL
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Không có token trong URL!");
}

// 3. Kiểm tra token tồn tại và còn hiệu lực

$stmt = $conn->prepare("SELECT reset_token, token_expire FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows === 0) {
    die("Link không hợp lệ hoặc đã hết hạn!");
}


// 4. Xử lý khi user submit mật khẩu mới
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['pass'])) {
    $newPass = $_POST['pass'];
    
    // Hash mật khẩu
    $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);

    // Cập nhật mật khẩu, xóa token
    $stmtUpdate = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expire = NULL WHERE user_id = ?");
    $stmtUpdate->bind_param("si", $hashedPass, $user_id);

    if ($stmtUpdate->execute()) {
        $message = "Đổi mật khẩu thành công! <a href='login.php'>Đăng nhập ngay</a>";
    } else {
        $message = "Lỗi hệ thống, vui lòng thử lại.";
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu</title>
</head>
<body>
<h2>Đặt lại mật khẩu</h2>

<?php if($message): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>

<?php if(empty($message)): ?>
<form method="POST">
    <label>Mật khẩu mới:</label>
    <input type="password" name="pass" required><br><br>

    <button type="submit">Đặt lại mật khẩu</button>
</form>
<?php endif; ?>

</body>
</html>
