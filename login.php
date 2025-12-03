<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", sans-serif;
        }

        body {
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            width: 400px;
            background: #ffffff;
            padding: 30px 35px;
            border-radius: 18px;
            box-shadow: 0px 10px 35px rgba(0,0,0,0.20);
            animation: fadeIn 0.5s ease;
            text-align: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container h2 {
            font-size: 26px;
            font-weight: bold;
            color: #333;
            margin-bottom: 25px;
        }

        .form-container label {
            text-align: left;
            display: block;
            font-weight: 600;
            color: #444;
            margin: 10px 0 5px;
        }

        .form-container input {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
            transition: 0.3s;
        }

        .form-container input:focus {
            border-color: #4e73df;
            box-shadow: 0 0 8px rgba(78,115,223,0.4);
        }

        .form-container button {
            width: 100%;
            margin-top: 20px;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(45deg, #4e73df, #2e59d9);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .form-container button:hover {
            background: linear-gradient(45deg, #2e59d9, #1d4ed8);
            transform: scale(1.02);
        }

        .extra-links p {
            margin-top: 15px;
            font-size: 14px;
        }

        .extra-links a {
            color: #2e59d9;
            text-decoration: none;
            font-weight: 600;
        }

        .extra-links a:hover {
            text-decoration: underline;
        }

        .alert {
            color: #28a745;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2>Đăng nhập</h2>
        <?php


// Sai mật khẩu
if (isset($_GET['error']) && $_GET['error'] == 'wrong_password') {
    echo '<p class="alert" style="
        background:#ffe6e6;
        color:#cc0000;
        padding:10px;
        border-radius:8px;
        border:1px solid #ffb3b3;
        font-weight:bold;
        margin-bottom:12px;">
        Sai mật khẩu! Vui lòng thử lại.
    </p>';
}

// Email không tồn tại
if (isset($_GET['error']) && $_GET['error'] == 'email_not_found') {
    echo '<p class="alert" style="
        background:#fff3cd;
        color:#856404;
        padding:10px;
        border-radius:8px;
        border:1px solid #ffeeba;
        font-weight:bold;
        margin-bottom:12px;">
        Email không tồn tại trong hệ thống.
    </p>';
}
?>

        <?php
        if (isset($_GET['register']) && $_GET['register'] == 'success') {
            echo '<p class="alert"> Đăng ký thành công! Vui lòng đăng nhập.</p>';
        }
        ?>

        <form action="actions/action_login.php" method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Mật khẩu:</label>
            <input type="password" name="password" required>

            <button type="submit">Đăng nhập</button>
        </form>

        <div class="extra-links">
            <p><a href="forgot_password.php">Quên mật khẩu?</a></p>
            <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
        </div>
    </div>

</body>
</html>
