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
/* Reset cơ bản */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body với background */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: url('images/hero-bg.png') no-repeat center center/cover;
    position: relative;
}

/* Overlay mờ nền */
body::before {
    content: "";
    position: absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.6);
    z-index: 1;
}

/* Form container */
.container {
    position: relative;
    z-index: 2;
    background: linear-gradient(to bottom right, #ffffff, #f2f7ff);
    padding: 50px 35px;
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 420px;
    text-align: center;
    transition: all 0.3s ease;
}

/* Heading */
.container h2 {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 30px;
    letter-spacing: 1px;
}

/* Message */
.container p {
    background-color: #fff3cd;
    color: #856404;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-size: 15px;
    text-align: center;
    border: 1px solid #ffeeba;
}

/* Input fields */
.container input[type="password"] {
    width: 100%;
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 16px;
    transition: all 0.3s ease;
}

.container input[type="password"]:focus {
    border-color: #007BFF;
    box-shadow: 0 0 8px rgba(0,123,255,0.3);
    outline: none;
}

/* Button */
.container button {
    width: 100%;
    padding: 14px;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    background: linear-gradient(45deg, #007BFF, #0056b3);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.container button:hover {
    background: linear-gradient(45deg, #0056b3, #003f7f);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
}

/* Label */
label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
    font-size: 14px;
    text-align: left;
}

/* Responsive */
@media (max-width: 480px) {
    .container {
        padding: 35px 25px;
    }

    .container h2 {
        font-size: 26px;
    }

    .container input[type="password"],
    .container button {
        font-size: 15px;
    }
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
