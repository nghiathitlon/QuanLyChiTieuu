<?php
// reset_password.php

require_once 'db_connect.php'; // kết nối DB trả về $conn

$message = '';
$showForm = false;
$user_id = null;

// 1) Lấy token từ URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Debug tạm thời: hiển thị token để kiểm tra
    // var_dump($token);

    // 2) Kiểm tra token tồn tại và chưa hết hạn
    $stmt = $conn->prepare("SELECT user_id, token_expire FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $message = "Token không hợp lệ hoặc đã được sử dụng.";
    } else {
        $user = $res->fetch_assoc();
        $expire = strtotime($user['token_expire']);
        $now = time();

        // Debug: kiểm tra thời gian token
        // var_dump($expire, $now);

        if ($now > $expire) {
            $message = "Token đã hết hạn. Vui lòng yêu cầu tạo token mới.";
        } else {
            $showForm = true;
            $user_id = $user['user_id'];
        }
    }
} else {
    $message = "Token không tồn tại trong URL.";
}

// 3) Xử lý khi submit form đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'], $_POST['password_confirm'])) {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        $message = "Mật khẩu xác nhận không trùng khớp.";
        $showForm = true;
    } elseif (strlen($password) < 6) {
        $message = "Mật khẩu phải có ít nhất 6 ký tự.";
        $showForm = true;
    } else {
        // Hash mật khẩu
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Cập nhật password và xóa token
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expire = NULL WHERE user_id = ?");
        $stmt->bind_param("si", $passwordHash, $user_id);

        if ($stmt->execute()) {
            $message = "Mật khẩu đã được đặt lại thành công. Bạn có thể đăng nhập ngay bây giờ.";
            $showForm = false;
        } else {
            $message = "Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại.";
            $showForm = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đặt lại mật khẩu</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: url('images/hero-bg.png') no-repeat center center/cover;
    position: relative;
}
body::before {
    content: "";
    position: absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 1;
}
.container {
    position: relative;
    z-index: 2;
    background-color: rgba(255,255,255,0.95);
    padding: 40px 30px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 400px;
}
.container h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
    font-size: 28px;
    font-weight: bold;
}
.container p {
    background-color: #ffefc2;
    padding: 10px;
    border-radius: 6px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
}
.container input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
    transition: 0.3s;
}
.container input[type="password"]:focus {
    border-color: #007BFF;
    outline: none;
}
.container button {
    width: 100%;
    padding: 12px;
    background-color: #007BFF;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
}
.container button:hover {
    background-color: #0056b3;
}
label {
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
    display: block;
    font-size: 14px;
}
</style>
</head>
<body>

<div class="container">
    <h2>Đặt lại mật khẩu</h2>
    <?php if($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if($showForm): ?>
    <form method="post" action="">
        <label>Mật khẩu mới:</label>
        <input type="password" name="password" placeholder="Nhập mật khẩu mới" required>
        <label>Xác nhận mật khẩu:</label>
        <input type="password" name="password_confirm" placeholder="Nhập lại mật khẩu" required>
        <button type="submit">Đặt lại mật khẩu</button>
    </form>
    <?php endif; ?>
</div>

</body>
</html>
