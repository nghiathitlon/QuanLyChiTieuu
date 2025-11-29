<?php
session_start();
require '../db_connect.php';
require '../functions.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy POST
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

if($amount <= 0){
    $_SESSION['error'] = "Số tiền không hợp lệ!";
    header("Location: ../dashboard.php");
    exit;
}

// Lấy số dư khả dụng
$selected_month = intval(date('m'));
$selected_year = intval(date('Y'));
$selected_ym = $selected_year . '-' . str_pad($selected_month, 2, '0', STR_PAD_LEFT);

// Tổng thu
$income_result = $conn->query("
    SELECT SUM(t.amount) AS total_income
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    WHERE t.user_id = $user_id AND c.type='income' AND DATE_FORMAT(t.transaction_date, '%Y-%m')='$selected_ym'
");
$total_income = $income_result->fetch_assoc()['total_income'] ?? 0;

// Tổng chi
$expense_result = $conn->query("
    SELECT SUM(t.amount) AS total_expense
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    WHERE t.user_id = $user_id AND c.type='expense' AND DATE_FORMAT(t.transaction_date, '%Y-%m')='$selected_ym'
");
$total_expense = $expense_result->fetch_assoc()['total_expense'] ?? 0;

// Tổng quỹ tiết kiệm đã tạo trong tháng
$savings_result = $conn->query("
    SELECT SUM(amount) AS total_savings
    FROM savings
    WHERE user_id = $user_id AND DATE_FORMAT(created_at, '%Y-%m')='$selected_ym'
");
$total_savings = $savings_result->fetch_assoc()['total_savings'] ?? 0;

// Số dư khả dụng
$balance = $total_income - $total_expense - $total_savings;

if($amount > $balance){
    $_SESSION['error'] = "Số dư không đủ để tạo quỹ tiết kiệm!";
    header("Location: ../dashboard.php");
    exit;
}

// Tạo quỹ tiết kiệm
$name = "Tiết kiệm tháng $selected_month/$selected_year";
$conn->query("INSERT INTO savings(user_id, name, amount) VALUES($user_id, '$name', $amount)");

$_SESSION['success'] = "Đã tạo quỹ tiết kiệm: ".number_format($amount)." VND";
header("Location: ../dashboard.php");
exit;
?>
