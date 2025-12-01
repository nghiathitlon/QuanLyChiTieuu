<?php
require 'db_connect.php';

$showResetForm = false;
$message = '';
$token = '';

// Kiểm tra token từ link email
if(isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT user_id, token_expire FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        $expire = strtotime($user['token_expire']);
        if(time() > $expire) {
            $message = "Link đã hết hạn!";
        } else {
            $showResetForm = true; // Hiển thị form reset
            $user_id = $user['user_id'];
        }
    } else {
        $message = "Token không hợp lệ!";
    }
}

// Xử lý submit form reset password
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'], $_POST['confirm_password'], $user_id)) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if($new_pass !== $confirm_pass) {
        $message = "Mật khẩu nhập lại không khớp!";
        $showResetForm = true;
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, token_expire = NULL WHERE user_id = ?");
        $stmt2->bind_param("si", $hash, $user_id);
        if($stmt2->execute()) {
            $message = "Đổi mật khẩu thành công! Bạn có thể đăng nhập ngay.";
            $showResetForm = false;
        } else {
            $message = "Lỗi hệ thống!";
            $showResetForm = true;
        }
    }
}

require 'header.php';
?>

<!-- Reset Password Form -->
<div class="reset-table">
    <h2>Đặt lại mật khẩu</h2>

    <?php if($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if($showResetForm): ?>
    <form method="POST" action="">
        <label>Mật khẩu mới</label>
        <input type="password" name="new_password" placeholder="Nhập mật khẩu mới" required>

        <label>Xác nhận mật khẩu</label>
        <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>

        <button type="submit">Cập nhật mật khẩu</button>
    </form>
    <?php endif; ?>

    <!-- Nút quay về đăng nhập -->
    <a href="login.php" class="btn-login">Quay về Đăng nhập</a>
</div>

<style>
/* ===== Reset Password Table / Form ===== */
.reset-table {
    max-width: 400px;
    margin: 60px auto;
    padding: 40px 30px;
    background: linear-gradient(135deg, #ffffff, #f0f8ff);
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    text-align: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.reset-table h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #1a1a1a;
    font-weight: 700;
}

.reset-table .message {
    background-color: #fff3cd;
    color: #856404;
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-size: 15px;
}

.reset-table label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
    text-align: left;
    font-size: 14px;
}

.reset-table input[type="password"] {
    width: 100%;
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: 12px;
    border: 1px solid #ccc;
    font-size: 16px;
    transition: all 0.3s ease;
}

.reset-table input[type="password"]:focus {
    border-color: #1cc88a;
    box-shadow: 0 0 12px rgba(28,200,138,0.3);
    outline: none;
}

.reset-table button {
    width: 100%;
    padding: 16px;
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(45deg, #1cc88a, #17a673);
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.reset-table button:hover {
    background: linear-gradient(45deg, #17a673, #138155);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}

</style>

<?php require 'footer.php'; ?>
