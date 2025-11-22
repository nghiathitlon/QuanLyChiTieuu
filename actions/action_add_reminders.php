<?php
session_start();
require __DIR__ . '/../db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $remind_date = $_POST['remind_date'];

    $stmt = $conn->prepare("INSERT INTO reminders (user_id, title, description, remind_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $remind_date);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: ../dashboard.php?status=reminder_added");
    exit;
}
?>
