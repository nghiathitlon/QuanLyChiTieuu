<?php
$conn = new mysqli("localhost","root","","quanlychitieu");
$conn->set_charset("utf8");

if(isset($_POST["email"])) {
    $email = $_POST["email"];
    $token = bin2hex(random_bytes(50));
    $expire = date("Y-m-d H:i:s", strtotime("+30 minutes"));

    $sql = "UPDATE users SET reset_token='$token', token_expire='$expire' WHERE email='$email'";
    
    if($conn->query($sql)) {
        $baseURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://"
         . $_SERVER['HTTP_HOST']
         . rtrim(dirname($_SERVER['PHP_SELF']), "/");

        $resetLink = $baseURL . "/reset_password.php?token=" . urlencode($token);


        // Gửi email bằng PHPMailer
        require __DIR__ . '/PHPMailer/src/Exception.php';
        require __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require __DIR__ . '/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = 'smtp.gmail.com';
            $mail->Username = 'your_email@gmail.com'; 
            $mail->Password = 'your_generated_app_password'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your_email@gmail.com', 'Chi Tieu App');
            $mail->addAddress($email);
            $mail->Subject = "Khôi phục mật khẩu";
            $mail->Body = "Nhấn vào link để đặt lại mật khẩu: $resetLink";

            $mail->send();
            echo "Vui lòng kiểm tra email để đặt lại mật khẩu!";
        } catch (Exception $e) {
            echo "Lỗi gửi email: " . $mail->ErrorInfo;
        }
    }
}
?>

<form method="POST">
    <label>Nhập email đăng ký:</label>
    <input type="email" name="email" required>
    <button type="submit">Gửi liên kết</button>
</form>
