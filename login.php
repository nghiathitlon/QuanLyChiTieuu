<!DOCTYPE html>
<html>
<?php require_once 'header.php'?>

<head>
    <title>Đăng nhập</title>
</head>
<style>
input {
    display: block;
    margin-bottom: 10px;
    padding: 8px;
    width: 300px;
    box-sizing: border-box;
}
</style>


<body>

    <h2>Đăng nhập</h2>
    <?php
        if (isset($_GET['register']) && $_GET['register'] == 'success') {
            echo "<p style='color:green;'>Đăng ký thành công! Vui lòng đăng nhập.</p>";
        }
    ?>
    <div class="m-container">
        <div class="form-main-container">
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