<?php

session_start();
require '../db_connect.php';

// Lấy dữ liệu POST
$email = trim($_POST['email']);
$password = $_POST['password'];

// 1. Tìm user bằng email
$stmt = $conn->prepare("SELECT user_id, username, password FROM Users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // 2. So sánh mật khẩu nhập với hash trong DB
    if (password_verify($password, $user['password'])) {
        // 3. Lưu thông tin vào SESSION
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        // 4. Chuyển hướng đến dashboard
        header("Location: ../dashboard.php");
        exit;
    } else {
        // Sai mật khẩu → chuyển hướng kèm lỗi
        header("Location: ../login.php?error=wrong_password");
        exit;
    }
} else {
    // Email không tồn tại → chuyển hướng kèm lỗi
    header("Location: ../login.php?error=email_not_found");
    exit;
}

$stmt->close();
$conn->close();
?>
