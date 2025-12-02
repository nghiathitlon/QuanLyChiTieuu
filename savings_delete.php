<?php
require 'db_connect.php';
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) { echo json_encode(['ok'=>false,'error'=>'Chưa đăng nhập']); exit; }
$user_id = intval($_SESSION['user_id']);

$log_id = intval($_POST['log_id'] ?? 0);

// Lấy log + goal
$stmt = $conn->prepare("SELECT sl.amount, g.goal_id, g.user_id FROM savings_logs sl JOIN goals g ON sl.goal_id=g.goal_id WHERE sl.log_id=?");
$stmt->bind_param("i",$log_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$row) { echo json_encode(['ok'=>false,'error'=>'Log không tồn tại']); exit; }
if(intval($row['user_id']) !== $user_id) { echo json_encode(['ok'=>false,'error'=>'Không có quyền']); exit; }

$amount = floatval($row['amount']);
$goal_id = intval($row['goal_id']);

$conn->begin_transaction();
$u = $conn->prepare("UPDATE goals SET saved_amount = saved_amount - ? WHERE goal_id=?");
$u->bind_param("di",$amount,$goal_id);
$u->execute();
$u->close();

$d = $conn->prepare("DELETE FROM savings_logs WHERE log_id=?");
$d->bind_param("i",$log_id);
$d->execute();
$d->close();

// nếu sau khi trừ mà chưa đủ, set status = pending
$r = $conn->prepare("UPDATE goals SET status='pending' WHERE goal_id=? AND saved_amount < target_amount");
$r->bind_param("i",$goal_id);
$r->execute();
$r->close();

$conn->commit();
echo json_encode(['ok'=>true]);
