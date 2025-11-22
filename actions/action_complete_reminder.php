<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $reminder_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    $update = $conn->query("UPDATE reminders SET is_done = 1 WHERE id = $reminder_id AND user_id = $user_id");

    if ($update) {
        header("Location: ../dashboard.php?msg=completed");
        exit;
    } else {
        echo "Lỗi khi cập nhật nhắc nhở.";
    }
} else {
    echo "ID nhắc nhở không hợp lệ.";
}
?>
