<?php
session_start();
require 'db_connect.php';

$id = $_GET['id'];
$user = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE alerts SET is_done = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user);
$stmt->execute();

header("Location: dashboard.php?done=1");
exit;
?>
