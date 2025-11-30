<?php
// forgot_password.php

// 1) Require DB connect (chỉnh đường dẫn nếu cần)
require_once 'db_connect.php'; // file nên tạo trả về $conn (mysqli)

// 2) Include PHPMailer
// ======= Nếu dùng Composer =======
// require __DIR__ . '/vendor/autoload.php';

// ======= Nếu dùng thủ công =======
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3) Xử lý form khi submit
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);

    // Kiểm tra email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email không hợp lệ.';
    } else {
        // 3.1 Tìm user theo email
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            // Không hiện rõ là email không tồn tại — để tránh lộ thông tin
            $message = 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được email hướng dẫn.';
        } else {
            $user = $res->fetch_assoc();
            $user_id = $user['user_id'];

            // 3.2 Tạo token + expire
            $token = bin2hex(random_bytes(32)); // token đủ dài
            $expire = date("Y-m-d H:i:s", strtotime("+30 minutes"));

            // 3.3 Lưu token vào DB (prepared statement)
            $stmt2 = $conn->prepare("UPDATE users SET reset_token = ?, token_expire = ? WHERE user_id = ?");
            $stmt2->bind_param("ssi", $token, $expire, $user_id);
            $ok = $stmt2->execute();

            if ($ok) {
                // 3.4 Tạo link reset (chỉnh base URL cho đúng)
                // Nếu chạy trên local: ví dụ http://localhost/QuanLyChiTieuu/reset_password.php
                $resetLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://"
                    . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) 
                    . "/reset_password.php?token=" . urlencode($token);

                // 3.5 Gửi mail bằng PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Cấu hình SMTP (ví dụ Gmail)
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'tho.nh.64cntt@ntu.edu.vn'; // Email của bạn
                    $mail->Password = 'fvvlzumvqcqwhljx'; // Dùng App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('your_email@gmail.com', 'ChiTieu App'); // tên hiển thị
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Yêu cầu đặt lại mật khẩu';
                    $mail->Body = "
                        <p>Xin chào,</p>
                        <p>Bạn (hoặc ai đó) đã yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                        <p>Vui lòng nhấp vào liên kết sau để đặt lại mật khẩu (hết hạn sau 30 phút):</p>
                        <p><a href='$resetLink'>$resetLink</a></p>
                        <p>Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</p>
                    ";

                    $mail->send();
                    $message = 'Nếu email tồn tại, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu trong vài phút.';
                } catch (Exception $e) {
                    // Lỗi gửi mail
                    $message = 'Không thể gửi email. Lỗi: ' . $mail->ErrorInfo;
                }
            } else {
                $message = 'Lỗi hệ thống khi ghi token vào CSDL.';
            }
        }
    }
}
?>

<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Quên mật khẩu</title>
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
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background: url('images/hero-bg.png') no-repeat center center/cover;
    position: relative;
}

/* Overlay để làm mờ tối background */
body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background-color: rgba(0,0,0,0.5); /* mờ tối 50% */
    z-index: 1;
}

/* Form container */
.container {
    position: relative;
    z-index: 2; /* để nổi trên overlay */
    background-color: rgba(255, 255, 255, 0.95); /* nền form */
    padding: 40px 30px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 400px;
}

/* Heading */
.container h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
    font-size: 28px;
    font-weight: bold;
}

/* Message */
.container p {
    background-color: #ffefc2;
    padding: 10px;
    border-radius: 6px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
}

/* Input fields */
.container input[type="email"],
.container input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
    transition: 0.3s;
}

.container input[type="email"]:focus,
.container input[type="password"]:focus {
    border-color: #007BFF;
    outline: none;
}

/* Button */
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

/* Label styling */
.container label {
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
    <h2>Quên mật khẩu</h2>

    <?php if($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Email đã đăng ký:</label>
        <input type="email" name="email" placeholder="Nhập email của bạn" required>
        <button type="submit">Gửi liên kết đặt lại</button>
    </form>
</div>

</body>
</html>