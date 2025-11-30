<!DOCTYPE html>
<html>

<head>
    <?php require 'header.php'; ?>
    <title>Đăng ký</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<style>
input {
    display: block;
    margin-bottom: 10px;
    padding: 8px;
    width: 300px;
    ;

}
</style>

<body>
    <div class="container">
        <div classs="form-main-container">
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
        </div>
    </div>
</body>

</html>