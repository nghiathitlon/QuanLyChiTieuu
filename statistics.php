<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
require 'functions.php';

$user_id = $_SESSION['user_id'];

// Lấy khoảng ngày được chọn
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // mặc định ngày đầu tháng
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');      // mặc định ngày cuối tháng

// ===== TỔNG THU =====
$sql_income = "SELECT SUM(t.amount) AS total
               FROM transactions t
               JOIN categories c ON t.category_id = c.category_id
               WHERE t.user_id = ? AND c.type = 'income' AND t.transaction_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_income);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_income = $result['total'] ?? 0;

// ===== TỔNG CHI =====
$sql_expense = "SELECT SUM(t.amount) AS total
                FROM transactions t
                JOIN categories c ON t.category_id = c.category_id
                WHERE t.user_id = ? AND c.type = 'expense' AND t.transaction_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_expense);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_expense = $result['total'] ?? 0;

$balance = $total_income - $total_expense;

// ===== DANH SÁCH GIAO DỊCH =====
$sql_transactions = "SELECT t.transaction_id, t.transaction_date, c.name AS category_name, c.type AS type, t.amount, t.description
                     FROM transactions t
                     JOIN categories c ON t.category_id = c.category_id
                     WHERE t.user_id = ? AND t.transaction_date BETWEEN ? AND ?
                     ORDER BY t.transaction_date ASC";
$stmt = $conn->prepare($sql_transactions);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$transactions_result = $stmt->get_result();

require 'header.php';
?>

<section style="margin:20px 0;">
    <h2>Thống kê chi tiêu</h2>

    <form method="GET" style="display:flex; gap:20px; align-items:flex-end; margin-bottom:20px;">
        <div>
            <label>Chọn từ ngày:</label>
            <input type="date" name="start_date" value="<?= $start_date ?>">
        </div>

        <div>
            <label>Đến ngày:</label>
            <input type="date" name="end_date" value="<?= $end_date ?>">
        </div>

        <button type="submit" style="padding:6px 12px; background:#1cc88a; color:white; border:none; border-radius:5px;">
            Xem chi tiêu
        </button>
    </form>

    <p><strong>Tổng Thu:</strong> <?= format_vnd_with_usd($total_income); ?></p>
    <p><strong>Tổng Chi:</strong> <?= format_vnd_with_usd($total_expense); ?></p>
    <p><strong>Số dư:</strong> <?= format_vnd_with_usd($balance); ?></p>

    <h3>Chi tiết giao dịch</h3>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <thead style="background:#f2f2f2;">
            <tr>
                <th>Ngày</th>
                <th>Danh mục</th>
                <th>Loại</th>
                <th>Số tiền</th>
                <th>Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            <?php if($transactions_result->num_rows > 0): ?>
                <?php while($row = $transactions_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['transaction_date'] ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td><?= ucfirst($row['type']) ?></td>
                        <td><?= format_vnd_with_usd($row['amount']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Không có giao dịch nào trong khoảng thời gian này.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
