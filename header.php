<?php
// header.php
session_start();

// Kiểm tra user đã đăng nhập chưa
$logged_in = isset($_SESSION['user_id']);
$username = $logged_in ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản lý Chi tiêu</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Header tổng thể */
        header {
            background: linear-gradient(90deg, #4e73df, #1cc88a);
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Logo */
        header .logo {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        header .logo img {
            height: 40px;
            margin-right: 10px;
        }

        /* Menu điều hướng */
        nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #ffe600;
        }

        /* Phần đăng nhập/username */
        .user-info {
            font-weight: 500;
        }
        .btn {
            background-color: white;
            color: #007bff;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }
        .btn:hover {
            background-color: #f1f1f1;
        }

        /* Responsive nhỏ */
        @media (max-width: 600px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }
            nav {
                flex-direction: column;
                width: 100%;
                gap: 10px;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <img src="images/hinhanh.png" alt="Logo">
        <span>Chi tiêu thông minh</span>
    </div>  
    <nav>
        <a href="index.php">Trang chủ</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="reports.php">Xem lại chi tiêu</a>
        <a href="budget.php">Ngân sách</a>
        <?php if($logged_in): ?>
            <span class="user-info">Xin chào, <?= htmlspecialchars($username) ?></span>
            <a class="btn" href="logout.php">Đăng xuất</a>
        <?php else: ?>
            <a class="btn" href="login.php">Đăng nhập</a>
            <a class="btn" href="register.php">Đăng ký</a>
        <?php endif; ?>
    </nav>
</header>
