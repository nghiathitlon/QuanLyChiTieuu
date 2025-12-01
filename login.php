<!DOCTYPE html>
<html>
<?php require_once 'header.php'?>

<head>
    <title>Đăng nhập</title>
</head>
<style>
/* Bảng chứa form */
.form-main-container {
    background: #fff;
    padding: 35px 30px;
    border-radius: 15px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    max-width: 400px;
    margin: 30px auto;
    text-align: center;
}

/* Heading trong bảng */
.form-main-container h2 {
    font-size: 26px;
    font-weight: 700;
    color: #333;
    margin-bottom: 25px;
}

/* Label */
.form-main-container label {
    display: block;
    text-align: left;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
    font-size: 13px;
}

/* Input */
.form-main-container input[type="email"],
.form-main-container input[type="password"] {
    width: 90%;          /* nhỏ hơn 100% */
    max-width: 300px;    /* giới hạn rộng nhất */
    padding: 10px 12px;  /* giảm padding */
    margin-bottom: 15px; /* khoảng cách nhỏ hơn */
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;     /* nhỏ hơn */
    transition: 0.3s;
}

.form-main-container input[type="email"]:focus,
.form-main-container input[type="password"]:focus {
    border-color: #007BFF;
    box-shadow: 0 0 6px rgba(0,123,255,0.3);
    outline: none;
}

/* Button */
.form-main-container button {
    width: 95%;
    max-width: 300px;
    padding: 12px;
    font-size: 15px;
    font-weight: 600;
    color: #fff;
    background: linear-gradient(45deg, #007BFF, #0056b3);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}

.form-main-container button:hover {
    background: linear-gradient(45deg, #0056b3, #003f7f);
}

/* Links */
.form-main-container p a {
    color: #007BFF;
    text-decoration: none;
}

.form-main-container p a:hover {
    text-decoration: underline;
}
</style>

<body>

    <?php
        if (isset($_GET['register']) && $_GET['register'] == 'success') {
            echo "<p style='color:green;'>Đăng ký thành công! Vui lòng đăng nhập.</p>";
        }
    ?>
  
    <div class="m-container">
        <div class="form-main-container">
          <h2>Đăng nhập</h2>
            <form action="actions/action_login.php" method="POST">
                <label>Email:</label>
                <input type="email" name="email" required>
                <label>Mật khẩu:</label>
                <input type="password" name="password" required>
                <button type="submit">Đăng nhập</button>
            </form>
            <!-- Quên mật khẩu -->
            <p><a href="forgot_password.php">Quên mật khẩu?</a></p>
            <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
            <?php require 'footer.php'; ?>
        </div>
    </div>

</body>

</html>