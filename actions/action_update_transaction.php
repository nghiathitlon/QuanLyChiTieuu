<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../db_connect.php';

// Lấy dữ liệu an toàn từ form
$user_id = $_SESSION['user_id'];
$transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$transaction_date = isset($_POST['date']) ? $_POST['date'] : '';

// Validate cơ bản
if ($transaction_id <= 0 || $category_id <= 0 || $amount <= 0) {
    die("❌ Dữ liệu không hợp lệ.");
}

// Chuyển transaction_date về định dạng MySQL (YYYY-MM-DD)
$date_obj = DateTime::createFromFormat('Y-m-d', $transaction_date);
if (!$date_obj) {
    // Nếu người dùng gửi dd/mm/yyyy
    $date_obj = DateTime::createFromFormat('d/m/Y', $transaction_date);
}
if (!$date_obj) {
    die("❌ Ngày giao dịch không hợp lệ.");
}
$transaction_date = $date_obj->format('Y-m-d');

// Chuẩn bị statement
$stmt = $conn->prepare("
    UPDATE transactions 
    SET amount=?, transaction_date=?, category_id=?, note=?
    WHERE transaction_id = ? AND user_id = ?
");

$stmt->bind_param("dsissi", 
    $amount, 
    $transaction_date, 
    $category_id, 
    $description, 
    $transaction_id, 
    $user_id
);


// Thực thi
if ($stmt->execute()) {
    // Thành công
    header("Location: ../dashboard.php");
    exit;
} else {
    echo "❌ Lỗi khi cập nhật giao dịch: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
