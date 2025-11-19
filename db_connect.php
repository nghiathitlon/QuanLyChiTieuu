<?php
$servername = "localhost";
$username_db = "root";
$password_db = "";
$database_name = "personal_finance_db";

$conn = new mysqli($servername, $username_db, $password_db, $database_name);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
