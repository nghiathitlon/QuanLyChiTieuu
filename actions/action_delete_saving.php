<?php
session_start();
require '../db_connect.php';

if(!isset($_SESSION['user_id'])) exit;
$user_id = $_SESSION['user_id'];

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM savings WHERE id=$id AND user_id=$user_id");
}

header("Location: ../dashboard.php");
?>
