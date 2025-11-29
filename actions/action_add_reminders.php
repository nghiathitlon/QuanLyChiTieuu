<?php
session_start();
require __DIR__ . '/../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $remind_date = $_POST['remind_date'] ?? '';

    // Kiểm tra dữ liệu bắt buộc
    if ($title === '' || $remind_date === '') {
        $_SESSION['error'] = 'Tiêu đề và ngày nhắc không được để trống!';
        header("Location: ../dashboard.php");
        exit;
    }

    // Thêm ghi chú
    $stmt = $conn->prepare("INSERT INTO reminders (user_id, title, description, remind_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $remind_date);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Reload trang dashboard
    header("Location: ../dashboard.php");
    exit;
}
?>
