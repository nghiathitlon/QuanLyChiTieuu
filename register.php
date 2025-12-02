<!DOCTYPE html>
<html>

<head>
    <title>Đăng ký</title>
</head>

<style>
body {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Container canh form giữa */
.container {
    width: 80%;
    display: flex;
    justify-content: center;
}

/* Hiệu ứng xuất hiện từ dưới lên */
.form-main-container {
    background: #fff;
    padding: 35px 30px;
    border-radius: 20px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    max-width: 420px;
    width: 100%;
    text-align: center;

    /* Animation */
    opacity: 0;
    transform: translateY(40px);
    animation: formFadeUp 0.7s ease-out forwards;
}

/* Keyframes tạo hiệu ứng */
@keyframes formFadeUp {
    0% {
        opacity: 0;
        transform: translateY(40px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Tiêu đề */
.form-main-container h2 {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin-bottom: 25px;
}

/* Label */
.form-main-container label {
    display: block;
    text-align: left;
    margin: 6px 0 5px;
    font-weight: 600;
    font-size: 14px;
    color: #333;
}

/* Input */
.form-main-container input {
    width: 80%;
    max-width: 350px;
    padding: 11px 13px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 15px;
    margin-bottom: 18px;
    transition: 0.3s;
}

.form-main-container input:focus {
    border-color: #007BFF;
    box-shadow: 0 0 6px rgba(0,123,255,0.3);
    outline: none;
}

/* Button */
.form-main-container button {
    width: 100%;
    max-width: 350px;
    padding: 12px;
    background: linear-gradient(90deg, #337be8, #1a5bd8);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 15.5px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.form-main-container button:hover {
    background: linear-gradient(90deg, #1a5bd8, #0d3ea5);
}

/* Link */
.form-main-container p a {
    font-size: 14px;
    color: #007BFF;
    text-decoration: none;
}

.form-main-container p a:hover {
    text-decoration: underline;
}

</style>

<body>
    <div class="container">
        <div class="form-main-container">
            <h2>Đăng ký</h2>
            <?php
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'short_password') {
        echo '<p class="alert" style="
            background:#fff3cd;
            color:#856404;
            padding:10px;
            border-radius:8px;
            border:1px solid #ffeeba;
            margin-bottom:12px;
            font-weight:bold;">
            Mật khẩu phải ít nhất 6 ký tự!
        </p>';
    }
    if ($_GET['error'] == 'email_exists') {
        echo '<p class="alert" style="
            background:#ffe6e6;
            color:#cc0000;
            padding:10px 15px;
            border-radius:10px;
            font-weight:bold;
            width:80%;
            margin: -10px auto 20px auto;
            border:1px solid #ffb3b3;">
            Email đã tồn tại! Vui lòng đăng nhập hoặc sử dụng email khác.
        </p>';
    }
    if ($_GET['error'] == 'password_invalid_char') {
        echo '<p class="alert" style="
            background:#fff3cd;
            color:#856404;
            padding:10px;
            border-radius:8px;
            border:1px solid #ffeeba;
            margin-bottom:12px;
            font-weight:bold;">
            Mật khẩu không được chứa dấu hoặc ký tự đặc biệt không hợp lệ!
        </p>';
    }
}
?>

            <form action="actions/action_register.php" method="POST">
                <label for="username">Tên hiển thị:</label>
                <input type="text" name="username" required>

                <label for="email">Email:</label>
                <input type="email" name="email" required>

                <label for="password">Mật khẩu:</label>
                <input type="password" name="password" required>

                <button type="submit">Đăng ký</button>
            </form>
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>

            <?php require 'footer.php'; ?>
        </div>
    </div>
</body>

</html>
