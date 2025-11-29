<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
require 'functions.php';

$user_id = $_SESSION['user_id'];

// Khoảng ngày
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-t');

// 1️⃣ Tổng thu/chi (bao gồm quỹ ngân sách)
$sql_totals = "
SELECT 
    SUM(CASE WHEN c.type='income' OR t.category_id=0 THEN t.amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN c.type='expense' THEN t.amount ELSE 0 END) AS total_spent
FROM transactions t
LEFT JOIN categories c ON t.category_id = c.category_id
WHERE t.user_id=? AND t.transaction_date BETWEEN ? AND ?
";
$stmt = $conn->prepare($sql_totals);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_income = floatval($res['total_income'] ?? 0);
$total_spent  = floatval($res['total_spent'] ?? 0);
$stmt->close();

// 2️⃣ Tổng quỹ tiết kiệm (nếu bạn có bảng savings)
$sql_savings = "SELECT SUM(amount) AS total_savings FROM savings WHERE user_id=? AND created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_savings);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_savings = floatval($res['total_savings'] ?? 0);
$stmt->close();

// 3️⃣ Tổng chi bao gồm quỹ tiết kiệm
$total_expense_with_savings = $total_spent + $total_savings;

// 4️⃣ Số dư = tổng thu - tổng chi (bao gồm quỹ)
$balance = $total_income - $total_expense_with_savings;

// 5️⃣ Lấy danh sách giao dịch bao gồm quỹ ngân sách
$sql_transactions = "
SELECT 
    t.transaction_id,
    t.transaction_date,
    CASE WHEN t.category_id=0 THEN 'Quỹ ngân sách' ELSE c.name END AS category_name,
    CASE 
        WHEN t.category_id=0 THEN 'Thu'
        WHEN c.type='income' THEN 'Thu'
        WHEN c.type='expense' THEN 'Chi'
    END AS type,
    t.amount,
    t.description
FROM transactions t
LEFT JOIN categories c ON t.category_id = c.category_id
WHERE t.user_id=? AND t.transaction_date BETWEEN ? AND ?
ORDER BY t.transaction_date ASC
";

$stmt = $conn->prepare($sql_transactions);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$transactions_result = $stmt->get_result();

require 'header.php';
?>

<section style="margin:20px 0;">
    <h2>Thống kê chi tiêu</h2>

    <!-- FORM CHỌN KHOẢNG THỜI GIAN -->
    <form method="GET" style="display:flex; gap:20px; align-items:flex-end; margin-bottom:20px;">
        <div>
            <label>Từ ngày:</label>
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

    <!-- THỐNG KÊ TỔNG QUÁT -->
    <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom:20px;">
        <div style="padding:15px; background:#fff8e1; border-left:5px solid #ffc107; border-radius:8px; min-width:220px;">
            <h4>Tổng Thu</h4>
            <p style="font-size:18px; font-weight:bold; color:#d48806;"><?= format_vnd_with_usd($total_income) ?></p>
        </div>

        <div style="padding:15px; background:#ffebee; border-left:5px solid #f44336; border-radius:8px; min-width:240px;">
            <h4>Tổng Chi</h4>
            <p style="font-size:18px; font-weight:bold; color:#c62828;">
                <?= format_vnd_with_usd($total_expense_with_savings) ?>
            </p>
        </div>

        <div style="padding:15px; background:#e8f5e9; border-left:5px solid #4caf50; border-radius:8px; min-width:220px;">
            <h4>Số dư</h4>
            <p style="font-size:18px; font-weight:bold; color:#2e7d32;"><?= format_vnd_with_usd($balance) ?></p>
        </div>
    </div>

    <!-- BẢNG CHI TIẾT GIAO DỊCH -->
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
                    <tr style="<?= $row['category_name']==='Quỹ ngân sách' ? 'background:#e8f5e9;' : '' ?>">
                        <td><?= $row['transaction_date'] ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td>
                            <?= ($row['type'] === 'income') || ($row['type'] === 'Thu') ? 'Thu' : 'Chi' ?>
                        </td>

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

<?php 
$stmt->close();
$conn->close();
require 'footer.php'; 
?>
