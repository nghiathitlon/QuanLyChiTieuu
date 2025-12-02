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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quên mật khẩu</title>

<style>
body {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Hiệu ứng xuất hiện từ dưới lên */
@keyframes fadeSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.reset-container {
    background: #fff;
    padding: 35px 30px;
    border-radius: 20px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    max-width: 420px;
    width: 100%;
    text-align: center;

    /* Áp dụng animation */
    animation: fadeSlideUp 0.6s ease-in-out;
}

.reset-container h2 {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin-bottom: 25px;
}

.reset-container p {
    background-color: #fff3cd;
    color: #856404;
    padding: 10px 15px;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 18px;
}

.reset-container label {
    display: block;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 6px;
    color: #333;
}

.reset-container input[type="email"] {
    width: 90%;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-bottom: 18px;
    font-size: 15px;
}

.reset-container input[type="email"]:focus {
    border-color: #007BFF;
    box-shadow: 0 0 6px rgba(0,123,255,0.3);
    outline: none;
}

.reset-container button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(90deg, #337be8, #1a5bd8);
    border-radius: 10px;
    border: none;
    color: #fff;
    font-size: 15.5px;
    font-weight: bold;
    cursor: pointer;
    margin-bottom: 15px;
    transition: 0.3s;
}

.reset-container button:hover {
    background: linear-gradient(90deg, #1a5bd8, #0d3ea5);
    transform: scale(1.02);
}

.reset-container a {
    font-size: 14px;
    color: #007BFF;
    text-decoration: none;
}

.reset-container a:hover {
    text-decoration: underline;
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

        <button type="submit">Gửi liên kết đặt lại</button>
    </form>

    <a href="login.php">Quay về đăng nhập</a>
</div>

</body>
</html>

