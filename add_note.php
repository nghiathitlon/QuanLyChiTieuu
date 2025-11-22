<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = $_POST['note'];
    $due = $_POST['due_date'];
    $user = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO alerts (user_id, type, message, due_date) VALUES (?,?,?,?)");
    $type = "reminder";
    $stmt->bind_param("isss", $user, $type, $note, $due);
    $stmt->execute();

    header("Location: dashboard.php?added=1");
    exit;
}
?>

<h2>Thêm ghi chú nhắc nhở</h2>
<form method="POST">
    <textarea name="note" placeholder="Nội dung ghi chú..." required></textarea><br>
    <label>Ngày đến hạn:</label>
    <input type="date" name="due_date" required><br><br>
    <button type="submit">Lưu</button>
</form>
