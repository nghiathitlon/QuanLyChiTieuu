<?php
// Chạy bằng cron hàng ngày
require 'db_connect.php';

// Cấu hình: gửi khi còn <= DAYS_LEFT ngày
define('DAYS_LEFT', 7);

// Lấy tất cả mục tiêu còn pending và có deadline trong khoảng [today, today + DAYS_LEFT]
$today = date('Y-m-d');
$limitDate = date('Y-m-d', strtotime("+".DAYS_LEFT." days"));

$sql = "SELECT g.goal_id,g.goal_name,g.target_amount,g.saved_amount,g.deadline,u.email,u.username 
        FROM goals g JOIN users u ON g.user_id = u.user_id
        WHERE g.status='pending' AND g.deadline BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss",$today,$limitDate);
$stmt->execute();
$res = $stmt->get_result();

while($row = $res->fetch_assoc()){
    $daysLeft = (new DateTime($row['deadline']))->diff(new DateTime())->format("%r%a");
    // Tạo nội dung email
    $to = $row['email'];
    $subject = "[Nhắc nhở] Mục tiêu '{$row['goal_name']}' sắp đến hạn";
    $message = "Xin chào {$row['username']},\n\nMục tiêu '{$row['goal_name']}' của bạn còn {$daysLeft} ngày tới hạn ({$row['deadline']}).\n"
             . "Mục tiêu: ".number_format($row['target_amount'],0,',','.')." ₫\n"
             . "Đã tiết kiệm: ".number_format($row['saved_amount'],0,',','.')." ₫\n\n"
             . "Hãy kiểm tra và bổ sung hoặc điều chỉnh kế hoạch để kịp hoàn thành.\n\nTrân trọng,\nỨng dụng quản lý chi tiêu";
    // Gửi email - dùng mail() (cần cấu hình server) hoặc thay bằng PHPMailer
    $headers = "From: no-reply@yourdomain.com\r\n";
    // Lưu log hoặc gửi
    @$sent = mail($to,$subject,$message,$headers);
    // Option: lưu vào bảng alerts nếu muốn
}
echo "Done\n";
