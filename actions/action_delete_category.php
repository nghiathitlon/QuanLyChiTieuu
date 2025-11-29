<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['ok'=>false,'msg'=>'Chưa đăng nhập']); exit;}
require '../db_connect.php';

$user_id = $_SESSION['user_id'];
$category_id = intval($_POST['category_id'] ?? 0);
if($category_id<=0){ echo json_encode(['ok'=>false,'msg'=>'ID không hợp lệ']); exit; }

$stmt = $conn->prepare("DELETE FROM categories WHERE category_id=? AND user_id=?");
$stmt->bind_param("ii",$category_id,$user_id);
if($stmt->execute()) echo json_encode(['ok'=>true]);
else echo json_encode(['ok'=>false,'msg'=>'Lỗi xóa danh mục']);
$stmt->close(); $conn->close();
?>
