<?php
// forgot_password.php
require_once 'db_connect.php';

// PHPMailer
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email không hợp lệ.';
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $message = 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.';
        } else {
            $user = $res->fetch_assoc();
            $user_id = $user['user_id'];

            $token = bin2hex(random_bytes(32));
            $expire = date("Y-m-d H:i:s", strtotime("+30 minutes"));

            $stmt2 = $conn->prepare("UPDATE users SET reset_token = ?, token_expire = ? WHERE user_id = ?");
            $stmt2->bind_param("ssi", $token, $expire, $user_id);
            $ok = $stmt2->execute();

            if ($ok) {
                $resetLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://"
                    . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) 
                    . "/reset_password.php?token=" . urlencode($token);

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'tho.nh.64cntt@ntu.edu.vn';
                    $mail->Password = 'fvvlzumvqcqwhljx';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('your_email@gmail.com', 'ChiTieu App');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Yêu cầu đặt lại mật khẩu';
                    $mail->Body = "
                        <p>Xin chào,</p>
                        <p>Bạn (hoặc ai đó) đã yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                        <p>Nhấp vào liên kết sau để đặt lại mật khẩu (hết hạn sau 30 phút):</p>
                        <p><a href='$resetLink'>$resetLink</a></p>
                        <p>Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</p>
                    ";

                    $mail->send();
                    $message = 'Nếu email tồn tại, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu trong vài phút.';
                } catch (Exception $e) {
                    $message = 'Không thể gửi email. Lỗi: ' . $mail->ErrorInfo;
                }
            } else {
                $message = 'Lỗi hệ thống khi ghi token vào CSDL.';
            }
        }
    }
}
require 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quên mật khẩu</title>
<style>
/* Container form */
.reset-container {
    background: #f9f9f9;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 400px;
    margin: 60px auto;
    text-align: center;
}

/* Heading */
.reset-container h2 {
    font-size: 28px;
    margin-bottom: 25px;
    font-weight: 700;
    color: #333;
}

/* Message */
.reset-container p {
    background-color: #fff3cd;
    color: #856404;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-size: 15px;
}

/* Input */
.reset-container input[type="email"] {
    width: 100%;
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 16px;
    transition: all 0.3s ease;
}

.reset-container input[type="email"]:focus {
    border-color: #007BFF;
    box-shadow: 0 0 8px rgba(0,123,255,0.3);
    outline: none;
}

/* Button */
.reset-container button {
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

.reset-container button:hover {
    background: linear-gradient(45deg, #0056b3, #003f7f);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
}

/* Label */
.reset-container label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
    font-size: 14px;
    text-align: left;
}

/* Nút quay về đăng nhập */
.reset-table .btn-login {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 20px;
    font-size: 15px;
    font-weight: 600;
    color: #1cc88a;
    background: #f0f8ff;
    border: 2px solid #1cc88a;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.reset-table .btn-login:hover {
    background: #1cc88a;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
}

/* Responsive */
@media(max-width:768px){
    .reset-table {
        padding: 30px 20px;
        margin: 30px 15px;
    }
    .reset-table h2 {
        font-size: 24px;
    }
    .reset-table input[type="password"] {
        padding: 12px 15px;
        font-size: 15px;
    }
    .reset-table button {
        padding: 14px;
        font-size: 15px;
    }
    .reset-table .btn-login {
        padding: 10px 18px;
        font-size: 14px;
    }
}
</style>
</head>
<body>

<div class="reset-container">
    <h2>Quên mật khẩu</h2>

    <?php if($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Email đã đăng ký:</label>
        <input type="email" name="email" placeholder="Nhập email của bạn" required>
        <button type="submit">Gửi liên kết</button>
    </form>

    <a href="login.php" class="btn-login">Quay về Đăng nhập</a>
</div>
</body>
</html>
