<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
require 'functions.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Lấy tháng/năm được chọn
if (isset($_GET['month_year'])) {
    list($selected_year, $selected_month) = explode('-', $_GET['month_year']);
    $selected_month = (int)$selected_month;
    $selected_year  = (int)$selected_year;
} else {
    $selected_month = (int)date('n');
    $selected_year  = (int)date('Y');
}

// Cập nhật ngân sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'], $_POST['month_year'])) {
    $amount = floatval($_POST['amount']);
    list($month, $year) = explode('-', $_POST['month_year']);
    $month = (int)$month;
    $year  = (int)$year;

    if ($amount < 0) {
        $message = "<p style='color:red;'>Ngân sách phải >= 0</p>";
    } else {
        // Cập nhật bảng budget
        $stmt = $conn->prepare("
            INSERT INTO budget (user_id, month, year, amount)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE amount = VALUES(amount), updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("iiid", $user_id, $month, $year, $amount);
        $stmt->execute();
        $stmt->close();

        // Cập nhật giao dịch "Quỹ ngân sách" (category_id = 0)
        $transaction_date = "$year-" . str_pad($month,2,'0',STR_PAD_LEFT) . "-01";
        $stmt2 = $conn->prepare("
            INSERT INTO transactions (user_id, category_id, transaction_date, amount, description)
            VALUES (?, 0, ?, ?, 'Quỹ ngân sách')
            ON DUPLICATE KEY UPDATE amount = VALUES(amount)
        ");
        $stmt2->bind_param("isd", $user_id, $transaction_date, $amount);
        $stmt2->execute();
        $stmt2->close();

        header("Location: budget.php?month_year={$year}-" . str_pad($month,2,'0',STR_PAD_LEFT) . "&success=1");
        exit;
    }
}

// Thông báo thành công
if (isset($_GET['success'])) {
    $message = "<p style='color:green;'>Đã cập nhật ngân sách cho $selected_month/$selected_year</p>";
}

// Lấy ngân sách hiện tại
$stmt = $conn->prepare("SELECT amount FROM budget WHERE user_id=? AND month=? AND year=?");
$stmt->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt->execute();
$res = $stmt->get_result();
$current_budget = 0;
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

// Lấy tổng quỹ tiết kiệm (nếu có bảng savings)
$stmt = $conn->prepare("SELECT SUM(amount) AS total_savings FROM savings WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?");
$stmt->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt->execute();
$res2 = $stmt->get_result()->fetch_assoc();
$total_savings = floatval($res2['total_savings'] ?? 0);
$stmt->close();

// Tổng chi bao gồm quỹ tiết kiệm
$total_expense_with_savings = $total_spent + $total_savings;

// Số dư = tổng thu - tổng chi
$balance = $total_income - $total_expense_with_savings;

$conn->close();

require 'header.php';
?>

<main style="padding:20px">
    <h2>Ngân sách tháng</h2>
    <?= $message ?>

    <!-- FORM CHỌN THÁNG -->
    <form method="GET" action="" style="margin-bottom:20px;">
        <label for="month_year">Chọn tháng:</label>
        <input type="month" id="month_year" name="month_year"
               value="<?= $selected_year . '-' . str_pad($selected_month,2,'0',STR_PAD_LEFT) ?>"
               required>
        <button type="submit">Xem</button>
    </form>

    <!-- FORM CẬP NHẬT NGÂN SÁCH -->
    <form method="POST" action="" style="max-width:500px;">
        <input type="hidden" name="month_year" value="<?= $selected_year . '-' . str_pad($selected_month,2,'0',STR_PAD_LEFT) ?>">
        <label>Ngân sách (VND):</label><br>
        <input type="number" name="amount" value="<?= htmlspecialchars($current_budget) ?>" min="0" required style="width:200px;padding:6px;">
        <br><br>
        <button type="submit">Lưu ngân sách</button>
    </form>

    <hr>

    <h3>Tổng quan tháng <?= "$selected_month / $selected_year" ?></h3>

    <div style="display:flex; gap:20px; margin-top:15px; flex-wrap:wrap;">
        <div style="padding:15px; background:#fff8e1; border-left:5px solid #ffc107; border-radius:8px; min-width:260px;">
            <h4 style="margin:0;">📊 Tổng thu được tháng này</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold; color:#d48806;">
                <?= number_format($total_income) ?> VND
            </p>
        </div>

        <div style="padding:15px; background:#e3f2fd; border-left:5px solid #2196f3; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">💰 Ngân sách</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold;">
                <?= number_format($current_budget) ?> VND
            </p>
        </div>

        <div style="padding:15px; background:#ffebee; border-left:5px solid #f44336; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">Tổng Chi</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold; color:#c62828;">
                <?= number_format($total_expense_with_savings) ?> VND
            </p>
        </div>

        <div style="padding:15px; background:#e8f5e9; border-left:5px solid #4caf50; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">Số dư</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold; color:#2e7d32;">
                <?= number_format($balance) ?> VND
            </p>
        </div>
    </div>
</main>

<?php require 'footer.php'; ?>
