<?php
// budget.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$message = '';

// --------------------------
// XỬ LÝ CHỌN THÁNG/NĂM
// --------------------------
if (isset($_GET['month_year'])) {
    list($selected_year, $selected_month) = explode('-', $_GET['month_year']);
    $selected_month = (int)$selected_month;
    $selected_year  = (int)$selected_year;
} else {
    $selected_month = (int)date('n');
    $selected_year  = (int)date('Y');
}

// --------------------------
// CẬP NHẬT NGÂN SÁCH
// --------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'], $_POST['month_year'])) {
    $amount = floatval($_POST['amount']);
    list($month, $year) = explode('-', $_POST['month_year']);
    $month = (int)$month;
    $year  = (int)$year;

    if ($amount < 0) {
        $message = "<p style='color:red;'>Ngân sách phải >= 0</p>";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO budget (user_id, month, year, amount)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE amount = VALUES(amount), updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("iiid", $user_id, $month, $year, $amount);
        if ($stmt->execute()) {
            header("Location: budget.php?month_year={$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "&success=1");
            exit;
        } else {
            $message = "<p style='color:red;'>Lỗi: " . htmlspecialchars($stmt->error) . "</p>";
        }
        $stmt->close();
    }
}

// Nếu có thông báo thành công từ GET
if (isset($_GET['success'])) {
    $message = "<p style='color:green;'>Đã cập nhật ngân sách cho $selected_month/$selected_year</p>";
}

// --------------------------
// LẤY NGÂN SÁCH HIỆN TẠI
// --------------------------
$stmt = $conn->prepare("SELECT amount FROM budget WHERE user_id = ? AND month = ? AND year = ?");
$stmt->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt->execute();
$res = $stmt->get_result();
$current_budget = 0;
if ($row = $res->fetch_assoc()) {
    $current_budget = floatval($row['amount']);
}
$stmt->close();

// --------------------------
// TÍNH TỔNG THU/CHI
// --------------------------
$stmt2 = $conn->prepare("
    SELECT COALESCE(SUM(t.amount),0) AS total_spent
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    WHERE t.user_id = ?
      AND c.type = 'expense'
      AND MONTH(t.transaction_date) = ?
      AND YEAR(t.transaction_date) = ?
");

$stmt3 = $conn->prepare("
    SELECT COALESCE(SUM(t.amount),0) AS total_income
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    WHERE t.user_id = ?
      AND c.type = 'income'
      AND MONTH(t.transaction_date) = ?
      AND YEAR(t.transaction_date) = ?
");

// Tổng thu
$stmt3->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt3->execute();
$res3 = $stmt3->get_result();
$total_income = 0;
if ($r3 = $res3->fetch_assoc()) $total_income = floatval($r3['total_income']);
$stmt3->close();

// Tổng chi
$stmt2->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt2->execute();
$res2 = $stmt2->get_result();
$total_spent = 0;
if ($r2 = $res2->fetch_assoc()) $total_spent = floatval($r2['total_spent']);
$stmt2->close();

$conn->close();
?>

<?php require 'header.php'; ?>

<main style="padding:20px">
    <h2>Ngân sách tháng</h2>
    <?php echo $message; ?>

    <!-- FORM CHỌN THÁNG -->
    <form method="GET" action="" style="margin-bottom:20px;">
        <label for="month_year">Chọn tháng:</label>
        <input type="month" id="month_year" name="month_year"
               value="<?php echo $selected_year . '-' . str_pad($selected_month, 2, '0', STR_PAD_LEFT); ?>"
               required>
        <button type="submit">Xem</button>
    </form>

    <!-- FORM CẬP NHẬT NGÂN SÁCH -->
    <form method="POST" action="" style="max-width:500px;">
        <input type="hidden" name="month_year" value="<?php echo $selected_year . '-' . str_pad($selected_month, 2, '0', STR_PAD_LEFT); ?>">
        <label>Ngân sách (VND):</label><br>
        <input type="number" name="amount" value="<?php echo htmlspecialchars($current_budget); ?>" min="0" required style="width:200px;padding:6px;">
        <br><br>
        <button type="submit">Lưu ngân sách</button>
    </form>

    <hr>

    <h3>Tổng quan tháng <?php echo "$selected_month / $selected_year"; ?></h3>

    <div style="display:flex; gap:20px; margin-top:15px; flex-wrap:wrap;">
        <div style="padding:15px; background:#fff8e1; border-left:5px solid #ffc107; border-radius:8px; min-width:260px;">
            <h4 style="margin:0;">📊 Tổng thu được tháng này</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold; color:#d48806;">
                <?php echo number_format($total_income); ?> VND
            </p>
        </div>

        <div style="padding:15px; background:#e3f2fd; border-left:5px solid #2196f3; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">💰 Ngân sách</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold;">
                <?php echo number_format($current_budget); ?> VND
            </p>
        </div>

        <div style="padding:15px; background:#ffebee; border-left:5px solid #f44336; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">📉 Đã chi</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold;">
                <?php echo number_format($total_spent); ?> VND
            </p>
        </div>

        <div style="padding:15px; background:#e8f5e9; border-left:5px solid #4caf50; border-radius:8px; min-width:240px;">
            <h4 style="margin:0;">📦 Còn lại</h4>
            <p style="margin:5px 0 0; font-size:18px; font-weight:bold;">
                <?php echo number_format($current_budget - $total_spent); ?> VND
            </p>
        </div>
    </div>

    <?php
    // Show warning levels
    if ($current_budget > 0) {
        $percent = ($total_spent / $current_budget) * 100;
        if ($percent >= 100) {
            echo "<div style='padding:12px;background:#ffdddd;color:#b30000;border-left:5px solid red;border-radius:6px;'>⚠ Bạn đã vượt ngân sách tháng!</div>";
        } elseif ($percent >= 80) {
            echo "<div style='padding:12px;background:#fff3cd;color:#856404;border-left:5px solid #ffc107;border-radius:6px;'>⚠ Bạn đã sử dụng $percent% ngân sách tháng (>=80%)</div>";
        } elseif ($percent >= 50) {
            echo "<div style='padding:12px;background:#e7f3ff;color:#0b66c3;border-left:5px solid #66b0ff;border-radius:6px;'>ℹ Bạn đã sử dụng $percent% ngân sách tháng</div>";
        }
    } else {
        echo "<p style='color:#666;'>Chưa có ngân sách cho tháng này.</p>";
    }
    ?>
</main>

<?php require 'footer.php'; ?>
