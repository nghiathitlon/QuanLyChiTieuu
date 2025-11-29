<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

session_start();
require '../db_connect.php'; 

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Chưa đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];

$id = intval($_POST['id']);
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$amount = floatval($_POST['amount']);
$user_id = intval($_SESSION['user_id']);

if(empty($id) || empty($name) || empty($amount)){
    echo json_encode(['success'=>false,'message'=>'Thiếu dữ liệu']);
    exit;
}


$amount = floatval(str_replace([',','₫'],'',$amount));

$stmt = $conn->prepare("UPDATE savings SET name=?, amount=? WHERE id=? AND user_id=?");
$stmt->bind_param("sdii", $name, $amount, $id, $user_id);

if($stmt->execute()){
    echo json_encode([
        'success'=>true,
        'id'=>$id,
        'name'=>$name,
        'amount'=>$amount,
        'amount_formatted'=>number_format($amount,0,',','.') . '₫'
    ]);
} else {
    echo json_encode(['success'=>false,'message'=>'Cập nhật thất bại']);
}
