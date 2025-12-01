<?php
require_once 'db_connect.php';

$message = '';
$showForm = false;
$user_id = null;

// 1) Lấy token từ URL
if (!isset($_GET['token'])) {
    $message = "Token không tồn tại trong URL.";
} else {
    $token = $_GET['token'];

    // 2) Kiểm tra token trong DB
    $stmt = $conn->prepare("SELECT user_id, token_expire FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $message = "Token không hợp lệ hoặc đã được sử dụng.";
    } else {
        $user = $res->fetch_assoc();
        $user_id = $user["user_id"];
        $expire = strtotime($user["token_expire"]);

        if (time() > $expire) {
            $message = "Link đặt lại mật khẩu đã hết hạn.";
        } else {
            $showForm = true;
        }
    }
}

// 3) Xử lý submit form
if ($_SERVER["REQUEST_METHOD"] === "POST" && $user_id !== null) {
    $password = $_POST["password"];
    $confirm = $_POST["password_confirm"];

    if ($password !== $confirm) {
        $message = "Mật khẩu nhập lại không khớp!";
        $showForm = true;
    } elseif (strlen($password) < 6) {
        $message = "Mật khẩu phải có ít nhất 6 ký tự.";
        $showForm = true;
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, token_expire = NULL 
            WHERE user_id = ?
        ");
        $stmt->bind_param("si", $hash, $user_id);

        if ($stmt->execute()) {
            $message = "Đặt lại mật khẩu thành công! Hãy đăng nhập ngay.";
            $showForm = false;
        } else {
            $message = "Lỗi hệ thống, vui lòng thử lại.";
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
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f0f0f0;
    position: relative;
}

/* Lớp mờ phủ toàn màn hình */
body::before {
    content: "";
    width: 100%;
    height: 100%;
    position: absolute;
    background: rgba(0, 0, 0, 0.5);
    left: 0;
    top: 0;
    z-index: 1;
}

/* Container chính */
.reset-box {
    position: relative;
    z-index: 2;
    width: 380px;
    background: #fff;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 0 25px rgba(0,0,0,0.3);
    animation: fadeIn 0.5s ease-in-out;
}

.reset-box h2 {
    text-align: center;
    margin-bottom: 22px;
}

/* Thông báo */
.message {
    background: #fff3cd;
    color: #856404;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ffeeba;
    margin-bottom: 20px;
    text-align: center;
}

/* Label + Input */
label {
    font-weight: bold;
    margin-top: 12px;
    display: block;
}

input[type="password"] {
    width: 100%;
    padding: 12px;
    margin-top: 5px;
    border: 1px solid #bbb;
    border-radius: 8px;
}

/* Button */
button {
    width: 100%;
    background: #1cc88a;
    color: #fff;
    padding: 14px;
    margin-top: 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}

button:hover {
    background: #159a6b;
}

/* Link dưới nút */
a.btn-login {
    display: block;
    text-align: center;
    margin-top: 18px;
    color: #007bff;
    text-decoration: none;
}

a.btn-login:hover {
    text-decoration: underline;
}

/* Fade hiệu ứng nhẹ */
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-10px);}
    to {opacity: 1; transform: translateY(0);}
}
</style>
</head>

<body>

<div class="reset-box">

    <h2>Đặt lại mật khẩu</h2>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($showForm): ?>
    <form method="post" action="">
        <label>Mật khẩu mới</label>
        <input type="password" name="password" required>

        <label>Xác nhận mật khẩu</label>
        <input type="password" name="password_confirm" required>

        <button type="submit">Cập nhật mật khẩu</button>
    </form>
    <?php endif; ?>

    <a class="btn-login" href="login.php">Quay về đăng nhập</a>

</div>

</body>
</html>
