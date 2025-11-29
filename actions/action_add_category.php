<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok'=>false,'msg'=>'Chưa đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$type = $_POST['type'];

if(empty($name) || ($type != 'income' && $type != 'expense')){
    echo json_encode(['ok'=>false,'msg'=>'Dữ liệu không hợp lệ']);
    exit;
}

// Kiểm tra trùng
$stmt = $conn->prepare("SELECT * FROM categories WHERE user_id=? AND name=? AND type=?");
$stmt->bind_param("iss", $user_id, $name, $type);
$stmt->execute();
if($stmt->get_result()->num_rows > 0){
    echo json_encode(['ok'=>false,'msg'=>'Danh mục đã tồn tại']);
    exit;
}

// Thêm mới
$stmt = $conn->prepare("INSERT INTO categories (user_id,name,type) VALUES (?,?,?)");
$stmt->bind_param("iss", $user_id, $name, $type);
if($stmt->execute()){
    echo json_encode(['ok'=>true,'id'=>$stmt->insert_id,'name'=>$name,'type'=>$type]);
}else{
    echo json_encode(['ok'=>false,'msg'=>'Thêm thất bại']);
}
?>
