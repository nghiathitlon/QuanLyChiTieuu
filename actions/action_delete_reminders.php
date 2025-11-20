<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$title = $_POST['title'];
$reminder_date = $_POST['reminder_date'];
$note = $_POST['note'];

$sql = "INSERT INTO reminders (user_id, title, reminder_date, note)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $user_id, $title, $reminder_date, $note);
$stmt->execute();

header("Location: ../dashboard.php");
exit;
?>