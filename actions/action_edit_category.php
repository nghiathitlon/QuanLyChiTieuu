<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['ok'=>false,'msg'=>'Chưa đăng nhập']); exit;}
require '../db_connect.php';

$user_id = $_SESSION['user_id'];
$category_id = intval($_POST['category_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$type = $_POST['type'] ?? '';

if($category_id<=0 || empty($name) || !in_array($type,['income','expense'])){
    echo json_encode(['ok'=>false,'msg'=>'Dữ liệu không hợp lệ']); exit;
}

// Kiểm tra trùng
$stmt = $conn->prepare("SELECT category_id FROM categories WHERE user_id=? AND name=? AND type=? AND category_id!=?");
$stmt->bind_param("issi",$user_id,$name,$type,$category_id);
$stmt->execute(); $stmt->store_result();
if($stmt->num_rows>0){ echo json_encode(['ok'=>false,'msg'=>'Danh mục đã tồn tại']); exit;}
$stmt->close();

// Update
$stmt = $conn->prepare("UPDATE categories SET name=?, type=? WHERE category_id=? AND user_id=?");
$stmt->bind_param("ssii",$name,$type,$category_id,$user_id);
if($stmt->execute()) echo json_encode(['ok'=>true]);
else echo json_encode(['ok'=>false,'msg'=>'Lỗi cập nhật']);
$stmt->close(); $conn->close();
?>
