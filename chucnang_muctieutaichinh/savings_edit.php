<?php
require 'db_connect.php';
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) { echo json_encode(['ok'=>false,'error'=>'Chưa đăng nhập']); exit; }
$user_id = intval($_SESSION['user_id']);

$log_id = intval($_POST['log_id'] ?? 0);
$new_amount = floatval($_POST['new_amount'] ?? 0);
$new_note = trim($_POST['new_note'] ?? '');

// Lấy log + goal, check quyền
$stmt = $conn->prepare("SELECT sl.amount, g.goal_id, g.user_id FROM savings_logs sl JOIN goals g ON sl.goal_id=g.goal_id WHERE sl.log_id=?");
$stmt->bind_param("i",$log_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$row) { echo json_encode(['ok'=>false,'error'=>'Log không tồn tại']); exit; }
if(intval($row['user_id']) !== $user_id) { echo json_encode(['ok'=>false,'error'=>'Không có quyền']); exit; }

$old = floatval($row['amount']);
$diff = $new_amount - $old;

$conn->begin_transaction();
$u1 = $conn->prepare("UPDATE savings_logs SET amount=?, note=? WHERE log_id=?");
$u1->bind_param("dsi",$new_amount,$new_note,$log_id);
$u1->execute();
$u1->close();

$u2 = $conn->prepare("UPDATE goals SET saved_amount = saved_amount + ? WHERE goal_id=?");
$u2->bind_param("di",$diff,$row['goal_id']);
$u2->execute();
$u2->close();

// cập nhật trạng thái mục tiêu
$u3 = $conn->prepare("UPDATE goals SET status='completed' WHERE goal_id=? AND saved_amount >= target_amount");
$u3->bind_param("i",$row['goal_id']);
$u3->execute();
$u3->close();

$conn->commit();
echo json_encode(['ok'=>true]);
