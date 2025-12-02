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

// Tính tổng thu/chi
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

// Tổng quỹ tiết kiệm
$stmt = $conn->prepare("SELECT SUM(amount) AS total_savings FROM savings WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?");
$stmt->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt->execute();
$res2 = $stmt->get_result()->fetch_assoc();
$total_savings = floatval($res2['total_savings'] ?? 0);
$stmt->close();

$conn->close();

// Tính toán
$total_expense_with_savings = $total_spent + $total_savings;
$real_balance = $total_income - $total_expense_with_savings;
$budget_balance = ($current_budget !== null) ? $current_budget - $total_expense_with_savings : null;
$expense_percent = ($current_budget > 0) ? round(($total_expense_with_savings / $current_budget) * 100, 2) : null;
$over_budget = ($current_budget !== null && $total_expense_with_savings > $current_budget);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Ngân sách tháng</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php require 'header.php'; ?>

<main style="padding:20px">

    <div class="header-section">
        <h2 style="color:#1a73e8;">Ngân sách tháng</h2>

        <form method="GET" action="" class="month-form">
            <label for="month_year">Chọn tháng:</label>
            <input type="month" id="month_year" name="month_year"
                   value="<?= $selected_year . '-' . str_pad($selected_month,2,'0',STR_PAD_LEFT) ?>"
                   required>
            <button type="submit">Xem</button>
        </form>
    </div>

    <hr style="margin:20px 0; border-color:#ccc;">

    <h3>Tổng quan tháng <?= "$selected_month / $selected_year" ?></h3>

    <div class="cards-container">

        <!-- Tổng thu -->
        <div class="card card-income">
            <h4>📊 Tổng thu</h4>
            <p><?= number_format($total_income) ?> VND</p>
        </div>

        <!-- Ngân sách -->
        <div class="card card-budget">
            <h4>💰 Ngân sách</h4>
            <p><?= $current_budget !== null ? number_format($current_budget) . ' VND' : 'Chưa thiết lập' ?></p>
        </div>

        <!-- Tổng chi -->
        <div class="card card-expense">
            <h4>Tổng chi</h4>
            <p><?= number_format($total_expense_with_savings) ?> VND</p>
            <?php if ($current_budget !== null): ?>
                <p class="expense-percent" style="color:<?= $over_budget ? '#d32f2f' : '#2e7d32' ?>;">
                    Chi tiêu <?= $expense_percent ?>% <?= $over_budget ? '(Vượt ngân sách!)' : '' ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Số dư thực tế -->
        <div class="card card-real-balance">
            <h4>Số dư thực tế</h4>
            <p style="color:<?= $real_balance >= 0 ? '#00796b' : '#d32f2f' ?>;">
                <?= number_format($real_balance) ?> VND
            </p>
        </div>

        <!-- Số dư ngân sách -->
        <div class="card card-budget-balance">
            <h4>Số dư ngân sách</h4>
            <p style="color:<?= ($budget_balance !== null && $budget_balance >= 0) ? '#6a1b9a' : '#d32f2f' ?>;">
                <?= $budget_balance !== null ? number_format($budget_balance) . ' VND' : '-' ?>
            </p>
        </div>

    </div>

    <!-- CSS màu nhạt -->
    <style>
        /* Form chọn tháng */
        .month-form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .month-form label {
            font-weight: 600;
            color: #333;
        }
        .month-form input[type="month"] {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        .month-form button {
            padding: 6px 14px;
            background: #81c784; /* màu xanh pastel nhạt */
            color: #fff;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .month-form button:hover {
            background: #66bb6a;
        }

        /* Cards container */
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 15px;
        }

        .card {
            flex: 1;
            min-width: 220px;
            padding: 20px;
            border-radius: 10px;
            color: #333;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card h4 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .card p {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        /* Màu pastel nhạt cho từng loại card */
        .card-income { background: linear-gradient(135deg, #fff9c4, #fff59d); }
        .card-budget { background: linear-gradient(135deg, #bbdefb, #90caf9); }
        .card-expense { background: linear-gradient(135deg, #ffcdd2, #ef9a9a); }
        .card-real-balance { background: linear-gradient(135deg, #b2ebf2, #80deea); }
        .card-budget-balance { background: linear-gradient(135deg, #e1bee7, #ce93d8); }

        /* Phần trăm chi tiêu */
        .expense-percent {
            margin-top: 5px;
            font-size: 14px;
            font-weight: normal;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .cards-container {
                flex-direction: column;
            }
            .card {
                min-width: 100%;
            }
            .month-form {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        .header-section {
    text-align: center;
    margin-bottom: 20px;
}

.month-form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center; /* canh giữa form input và button */
    gap: 10px;
    margin-top: 10px;
}

.month-form label {
    font-weight: 600;
    color: #333;
}

.month-form input[type="month"] {
    padding: 6px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

.month-form button {
    padding: 6px 14px;
    background: #81c784;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.month-form button:hover {
    background: #66bb6a;
}

    </style>
</main>

<?php require 'footer.php'; ?>
