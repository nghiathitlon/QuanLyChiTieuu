<!DOCTYPE html>
<html>

<head>
    <title>Đăng nhập</title>
</head>

<body>
    <?php require 'header.php'; ?>
    <h2>Đăng nhập</h2>
    <?php
        if (isset($_GET['register']) && $_GET['register'] == 'success') {
            echo "<p style='color:green;'>Đăng ký thành công! Vui lòng đăng nhập.</p>";
        }
    ?>
    <form action="actions/action_login.php" method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Mật khẩu:</label>
        <input type="password" name="password" required>
        <button type="submit">Đăng nhập</button>
    </form>
    <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
    <?php require 'footer.php'; ?>
</body>

</html>
