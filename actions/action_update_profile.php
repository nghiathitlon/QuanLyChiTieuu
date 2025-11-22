<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

if (!empty($password)) {
    // Nếu có nhập mật khẩu → hash lại
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "UPDATE users SET username = ?, email = ?, password_hash = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $username, $email, $password_hash, $user_id);

} else {
    // Nếu không nhập mật khẩu → không cập nhật cột password_hash
    $sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $username, $email, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['username'] = $username;
    header("Location: ../profile.php?status=success");
} else {
    header("Location: ../profile.php?status=error");
}

$stmt->close();
$conn->close();
?>
