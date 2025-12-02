<?php
require '../db_connect.php';

$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];

if (strlen($password) < 6) {
    header("Location: ../register.php?error=short_password");
    exit;
}
if (preg_match('/[^\x20-\x7E]/', $password)) {
    // \x20-\x7E là các ký tự ASCII từ khoảng trắng đến ~
    header("Location: ../register.php?error=password_invalid_char");
    exit;
}

// 1. Kiểm tra email đã tồn tại chưa
$check = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Email đã tồn tại
    header("Location: ../register.php?error=email_exists");
    exit;
}

$check->close();

// 2. Hash mật khẩu
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// 3. Lưu tài khoản mới
$stmt = $conn->prepare("INSERT INTO Users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password_hash);

if ($stmt->execute()) {
    header("Location: ../login.php?register=success");
    exit;
} else {
    echo "Lỗi hệ thống: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
