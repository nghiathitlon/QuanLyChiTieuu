<?php

session_start(); 

if (!isset($_SESSION['user_id'])) {
    // Nếu chưa đăng nhập, đá về trang login
    header("Location: ../login.php");
    exit;
}

// 2. Nhúng file kết nối DB
require '../db_connect.php';

// 3. Lấy dữ liệu an toàn từ session và form
$user_id = $_SESSION['user_id'];
$amount = $_POST['amount'];
$transaction_date = $_POST['date'];
// Chỉ cho thêm trong tháng hiện tại
$first_day = date("Y-m-01");
$last_day  = date("Y-m-t");

if ($transaction_date < $first_day || $transaction_date > $last_day) {
    die("❌ Bạn chỉ có thể thêm giao dịch trong tháng hiện tại!");
}
$transaction_date = date('Y-m-d', strtotime($transaction_date));
$category_id = $_POST['category_id'];
$description = $_POST['description']; // Có thể trống

// 4. Kiểm tra dữ liệu cơ bản (ví dụ: số tiền phải là số > 0)
if ($amount <= 0) {
    die("Số tiền không hợp lệ.");
}

// 5. Chuẩn bị câu lệnh SQL (Dùng Prepared Statements để chống SQL Injection)
$stmt = $conn->prepare("
    INSERT INTO Transactions (user_id, category_id, amount, transaction_date, description) 
    VALUES (?, ?, ?, ?, ?)
");

// "isdss" nghĩa là: Integer, Integer, Double, String, String
$stmt->bind_param("iidss", $user_id, $category_id, $amount, $transaction_date, $description);

// 6. Thực thi và chuyển hướng
if ($stmt->execute()) {
    // Thêm thành công, chuyển hướng người dùng TRỞ LẠI trang dashboard
    // Trình duyệt sẽ tự động tải lại dashboard.php,
    // và code PHP trong dashboard.php sẽ chạy lại,
    // lấy ra danh sách giao dịch MỚI NHẤT 
    header("Location: ../dashboard.php");
    exit;
} else {
    // Có lỗi xảy ra
    echo "Lỗi khi thêm giao dịch: " . $stmt->error;
}

// 7. Đóng kết nối
$stmt->close();
$conn->close();

?>