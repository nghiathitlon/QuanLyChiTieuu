<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require '../db_connect.php';
$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$type = $_POST['type']; // 'income' hoặc 'expense'
// Kiểm tra dữ liệu
if (empty($name) || ($type != 'income' && $type != 'expense')) {
    header("Location: ../categories.php?status=error");
    exit;
}

// Chèn vào CSDL
$stmt = $conn->prepare("INSERT INTO Categories (user_id, name, type) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $name, $type);

if ($stmt->execute()) {

    header("Location: ../categories.php?status=success");
} else {

    header("Location: ../categories.php?status=error");
}

$stmt->close();
$conn->close();
?>