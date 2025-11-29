<?php
session_start();
require '../db_connect.php';

$current_user_id = $_SESSION['user_id'] ?? 0;
$id = $_POST['id'] ?? null;
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$remind_date = $_POST['remind_date'];

$response = ['success'=>false, 'message'=>''];

if(!$current_user_id){
    $response['message'] = 'Chưa đăng nhập!';
    echo json_encode($response);
    exit;
}

if(!$title || !$remind_date){
    $response['message'] = 'Tiêu đề và ngày nhắc là bắt buộc!';
    echo json_encode($response);
    exit;
}

if($id){ 
    // Cập nhật
    $stmt = $conn->prepare("UPDATE reminders SET title=?, description=?, remind_date=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sssii", $title, $description, $remind_date, $id, $current_user_id);
    $stmt->execute();
} else {
    // Thêm mới
    $stmt = $conn->prepare("INSERT INTO reminders (user_id, title, description, remind_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $current_user_id, $title, $description, $remind_date);
    $stmt->execute();
    $id = $stmt->insert_id;
}

// Xác định trạng thái
$status_flag = ($remind_date <= date('Y-m-d')) ? 'overdue' : (strtotime($remind_date) <= strtotime('+3 days') ? 'upcoming' : 'normal');
$status_text = '';
$row_style = '';

if ($status_flag == 'overdue') {
    $status_text = '❌ Trễ hạn';
    $row_style = "style='background:#ffcdd2;'";
} elseif ($status_flag == 'upcoming') {
    $status_text = '🔔 Sắp tới';
    $row_style = "style='background:#fff3e0;'";
} elseif ($remind_date == date('Y-m-d')) {
    $status_text = '⚠️ Đến hạn';
    $row_style = "style='background:#ffe0b2;'";
} else {
    $status_text = '';
    $row_style = '';
}

// Trả về dữ liệu JSON cho JS
$response = [
    "success" => true,
    "id" => $id,
    "title" => $title,
    "description" => $description,
    "remind_date_formatted" => date('d/m/Y', strtotime($remind_date)),
    "status_text" => $status_text,
    "row_style" => $row_style
];

echo json_encode($response);
exit;
?>
