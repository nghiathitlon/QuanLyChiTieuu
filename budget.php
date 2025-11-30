<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
require 'functions.php';

$user_id = $_SESSION['user_id'];

// Lấy tháng/năm được chọn
if (isset($_GET['month_year'])) {
    list($selected_year, $selected_month) = explode('-', $_GET['month_year']);
    $selected_month = (int)$selected_month;
    $selected_year  = (int)$selected_year;
} else {
    $selected_month = (int)date('n');
    $selected_year  = (int)date('Y');
}

// Lấy ngân sách hiện tại
$stmt = $conn->prepare("SELECT amount FROM budget WHERE user_id=? AND month=? AND year=?");
$stmt->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt->execute();
$res = $stmt->get_result();
$current_budget = null;
if ($row = $res->fetch_assoc()) {
    $current_budget = floatval($row['amount']);
}
$stmt->close();

// Tính tổng thu/chi (bao gồm quỹ ngân sách)
$stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN c.type='income' THEN t.amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN c.type='expense' THEN t.amount ELSE 0 END) AS total_spent
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.category_id
    WHERE t.user_id=? AND MONTH(t.transaction_date)=? AND YEAR(t.transaction_date)=?
");
$stmt->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_income = floatval($res['total_income']);
$total_spent  = floatval($res['total_spent']);
$stmt->close();

// Lấy tổng quỹ tiết kiệm
$stmt = $conn->prepare("SELECT SUM(amount) AS total_savings FROM savings WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?");
$stmt->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt->execute();
$res2 = $stmt->get_result()->fetch_assoc();
$total_savings = floatval($res2['total_savings'] ?? 0);
$stmt->close();

// Tổng chi bao gồm quỹ tiết kiệm
$total_expense_with_savings = $total_spent + $total_savings;

// 1️⃣ Số dư thực tế = thu - chi
$real_balance = $total_income - $total_expense_with_savings;

// 2️⃣ Số dư ngân sách = ngân sách - chi (so với ngân sách đặt ra)
$budget_balance = ($current_budget !== null) ? $current_budget - $total_expense_with_savings : null;

// % chi tiêu so với ngân sách
$expense_percent = ($current_budget > 0) ? round(($total_expense_with_savings / $current_budget) * 100, 2) : null;

// Kiểm tra vượt ngân sách
$over_budget = ($current_budget !== null && $total_expense_with_savings > $current_budget);

$conn->close();
require 'header.php';
?>

<main style="padding:20px">
    <h2>Ngân sách tháng</h2>

    <!-- FORM CHỌN THÁNG -->
    <form method="GET" action="" style="margin-bottom:20px;">
        <label for="month_year">Chọn tháng:</label>
        <input type="month" id="month_year" name="month_year"
               value="<?= $selected_year . '-' . str_pad($selected_month,2,'0',STR_PAD_LEFT) ?>"
               required>
        <button type="submit">Xem</button>
    </form>

    <hr>

    <h3>Tổng quan tháng <?= "$selected_month / $selected_year" ?></h3>

    <div style="display:flex; gap:20px; margin-top:15px; flex-wrap:wrap;">

        <!-- Tổng thu -->
        <div style="padding:15px; background:#fff8e1; border-left:5px solid #ffc107; border-radius:8px; min-width:260px;">
            <h4 style="margin:0;">📊 Tổng thu</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold; color:#d48806;">
                <?= number_format($total_income) ?> VND
            </p>
        </div>

        <!-- Ngân sách -->
        <div style="padding:15px; background:#e3f2fd; border-left:5px solid #2196f3; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">💰 Ngân sách</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold;">
                <?= $current_budget !== null ? number_format($current_budget) . ' VND' : 'Chưa thiết lập' ?>
            </p>
        </div>

        <!-- Tổng chi -->
        <div style="padding:15px; background:#ffebee; border-left:5px solid #f44336; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">Tổng chi</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold; color:#c62828;">
                <?= number_format($total_expense_with_savings) ?> VND
            </p>
            <?php if ($current_budget !== null): ?>
                <p style="margin:2px 0 0; font-size:14px; color:<?= $over_budget ? '#d32f2f' : '#2e7d32' ?>;">
                    Chi tiêu <?= $expense_percent ?>% <?= $over_budget ? '(Vượt ngân sách!)' : '' ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Số dư thực tế -->
        <div style="padding:15px; background:#e0f7fa; border-left:5px solid #0097a7; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">Số dư thực tế</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold; color:<?= $real_balance >= 0 ? '#00796b' : '#d32f2f' ?>">
                <?= number_format($real_balance) ?> VND
            </p>
        </div>

        <!-- Số dư ngân sách -->
        <div style="padding:15px; background:#f3e5f5; border-left:5px solid #8e24aa; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">Số dư ngân sách</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold; color:<?= ($budget_balance !== null && $budget_balance >= 0) ? '#6a1b9a' : '#d32f2f' ?>">
                <?= $budget_balance !== null ? number_format($budget_balance) . ' VND' : '-' ?>
            </p>
        </div>

    </div>
</main>

<?php require 'footer.php'; ?>
