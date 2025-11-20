<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
require 'functions.php';

$user_id = $_SESSION['user_id'];

// Lấy giá trị tháng/năm được chọn
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year  = isset($_GET['year'])  ? intval($_GET['year'])  : date('Y');

$selected_ym = $selected_year . '-' . str_pad($selected_month, 2, '0', STR_PAD_LEFT);

// ===== TỔNG THU =====
$sql_income = "SELECT SUM(t.amount) AS total
               FROM Transactions t
               JOIN Categories c ON t.category_id = c.category_id
               WHERE t.user_id = ? AND c.type = 'income' AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?";
$stmt = $conn->prepare($sql_income);
$stmt->bind_param("is", $user_id, $selected_ym);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_income = $result['total'] ?? 0;

// ===== TỔNG CHI =====
$sql_expense = "SELECT SUM(t.amount) AS total
                FROM Transactions t
                JOIN Categories c ON t.category_id = c.category_id
                WHERE t.user_id = ? AND c.type = 'expense' AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?";
$stmt = $conn->prepare($sql_expense);
$stmt->bind_param("is", $user_id, $selected_ym);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_expense = $result['total'] ?? 0;

$balance = $total_income - $total_expense;

require 'header.php';
?>

<section style="margin:20px 0;">
    <h2>Xem lại chi tiêu</h2>

    <form method="GET" style="display:flex; gap:20px; align-items:flex-end; margin-bottom:20px;">
        <div>
            <label>Chọn tháng:</label>
            <select name="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($m == $selected_month ? 'selected' : '') ?>>
                        Tháng <?= $m ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label>Chọn năm:</label>
            <select name="year">
                <?php for ($y = 2020; $y <= 2030; $y++): ?>
                    <option value="<?= $y ?>" <?= ($y == $selected_year ? 'selected' : '') ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit" style="padding:6px 12px; background:#1cc88a; color:white; border:none; border-radius:5px;">
            Xem chi tiêu
        </button>
    </form>

    <p>Tổng Thu: <?= format_vnd_with_usd($total_income); ?></p>
    <p>Tổng Chi: <?= format_vnd_with_usd($total_expense); ?></p>
    <p>Số dư: <?= format_vnd_with_usd($balance); ?></p>
</section>
