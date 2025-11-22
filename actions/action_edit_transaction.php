<?php
session_start();
require '../db_connect.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Chưa đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];
$transaction_id = intval($_POST['transaction_id']);
$amount = floatval($_POST['amount']);
$date = $_POST['date'];
$category_id = intval($_POST['category_id']);
$description = $_POST['description'];

// Cập nhật giao dịch
$stmt = $conn->prepare("UPDATE Transactions SET amount=?, transaction_date=?, category_id=?, description=? WHERE transaction_id=? AND user_id=?");
$stmt->bind_param("dsiiii",$amount,$date,$category_id,$description,$transaction_id,$user_id);
$success = $stmt->execute();

if($success){
    // Lấy tên danh mục
    $cat_res = $conn->query("SELECT name FROM Categories WHERE category_id=$category_id");
    $category_name = $cat_res->fetch_assoc()['name'];

    // Tính lại tổng thu, tổng chi, số dư
    $income_res = $conn->query("SELECT SUM(t.amount) AS total_income
        FROM Transactions t
        JOIN Categories c ON t.category_id=c.category_id
        WHERE t.user_id=$user_id AND c.type='income'");
    $total_income = $income_res->fetch_assoc()['total_income'] ?? 0;

    $expense_res = $conn->query("SELECT SUM(t.amount) AS total_expense
        FROM Transactions t
        JOIN Categories c ON t.category_id=c.category_id
        WHERE t.user_id=$user_id AND c.type='expense'");
    $total_expense = $expense_res->fetch_assoc()['total_expense'] ?? 0;

    $balance = $total_income - $total_expense;

    // Dữ liệu chart
    $chart_res = $conn->query("
        SELECT c.name, SUM(t.amount) AS total_amount
        FROM Transactions t
        JOIN Categories c ON t.category_id=c.category_id
        WHERE t.user_id=$user_id AND c.type='expense'
        GROUP BY c.name
    ");
    $chart_labels = [];
    $chart_values = [];
    while($row = $chart_res->fetch_assoc()){
        $chart_labels[] = $row['name'];
        $chart_values[] = floatval($row['total_amount']);
    }

    echo json_encode([
        'success'=>true,
        'id'=>$transaction_id,
        'amount'=>$amount,
        'date'=>$date,
        'category_id'=>$category_id,
        'category_name'=>$category_name,
        'description'=>$description,
        'total_income'=>$total_income,
        'total_expense'=>$total_expense,
        'balance'=>$balance,
        'chart_labels'=>$chart_labels,
        'chart_values'=>$chart_values
    ]);
} else {
    echo json_encode(['success'=>false,'message'=>'Lỗi cập nhật giao dịch!']);
}
