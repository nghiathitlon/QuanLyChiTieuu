<!DOCTYPE html>
<html>

<head>
    <?php require 'header.php'; ?>
    <title>Đăng ký</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h2>Đăng ký tài khoản</h2>
    <form action="actions/action_register.php" method="POST">
        <label>Tên hiển thị:</label>
        <input type="text" name="username" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Mật khẩu:</label>
        <input type="password" name="password" required>

        <button type="submit">Đăng ký</button>
    </form>
    <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
    <?php require 'footer.php'; ?>
</body>

</html>