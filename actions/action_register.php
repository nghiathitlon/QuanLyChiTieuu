<?php
require '../db_connect.php';

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

// 2. Băm mật khẩu 
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// 3. Chuẩn bị câu lệnh SQL (Dùng prepared statements để chống SQL Injection)
$stmt = $conn->prepare("INSERT INTO Users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password_hash);

// 4. Thực thi và kiểm tra
if ($stmt->execute()) {
    header("Location: ../login.php?register=success");
    exit;
} else {
    echo "Lỗi: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>