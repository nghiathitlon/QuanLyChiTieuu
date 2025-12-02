<!DOCTYPE html>
<html>
<?php require_once 'header.php'?>

<head>
    <title>Đăng ký</title>
</head>

<style>
/* Bảng chứa form – giống login.php */
.form-main-container {
    background: #fff;
    padding: 35px 30px;
    border-radius: 15px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    max-width: 400px;
    margin: 30px auto;
    text-align: center;
}

.form-main-container h2 {
    font-size: 26px;
    font-weight: 700;
    color: #333;
    margin-bottom: 25px;
}

.form-main-container label {
    display: block;
    text-align: left;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
    font-size: 13px;
}

.form-main-container input[type="text"],
.form-main-container input[type="email"],
.form-main-container input[type="password"] {
    width: 90%;
    max-width: 300px;
    padding: 10px 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.form-main-container input:focus {
    border-color: #007BFF;
    box-shadow: 0 0 6px rgba(0,123,255,0.3);
    outline: none;
}

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
}

.form-main-container button:hover {
    background: linear-gradient(45deg, #0056b3, #003f7f);
}

.form-main-container p a {
    color: #007BFF;
    text-decoration: none;
}

.form-main-container p a:hover {
    text-decoration: underline;
}
</style>

<body>

    <div class="m-container">
        <div class="form-main-container">

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
