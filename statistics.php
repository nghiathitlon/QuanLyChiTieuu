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

// Tháng quỹ tiết kiệm
$saving_month = $_GET['saving_month'] ?? date('Y-m');
list($saving_year, $saving_month_only) = explode('-', $saving_month);

// 1. Tổng thu/chi
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

// 2. Tổng quỹ tiết kiệm trong khoảng thời gian
$sql_savings = "SELECT SUM(amount) AS total_savings FROM savings WHERE user_id=? AND created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_savings);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_savings = floatval($res['total_savings'] ?? 0);
$stmt->close();

// 3. Tổng chi bao gồm quỹ
$total_expense_with_savings = $total_spent + $total_savings;

// 4. Số dư
$balance = $total_income - $total_expense_with_savings;

// 5. Lấy giao dịch
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

// Quỹ theo tháng
$sql_saving_by_month = "
SELECT SUM(amount) AS month_saving
FROM savings 
WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=?
";
$stmt = $conn->prepare($sql_saving_by_month);
$stmt->bind_param("iii", $user_id, $saving_month_only, $saving_year);
$stmt->execute();
$res_month_saving = $stmt->get_result()->fetch_assoc();
$month_saving = floatval($res_month_saving['month_saving'] ?? 0);
$stmt->close();

require 'header.php';
?>

<section>

    <h2 class="section-title">Thống kê chi tiêu</h2>

    <form method="GET" class="filter-form">
        <div>
            <label>Từ ngày:</label>
            <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>">
        </div>
        <div>
            <label>Đến ngày:</label>
            <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? date('Y-m-t') ?>">
        </div>
        <div>
            <label>Quỹ tiết kiệm theo tháng:</label>
            <input type="month" name="saving_month" value="<?= $_GET['saving_month'] ?? date('Y-m') ?>">
        </div>
        <button type="submit">Xem chi tiêu</button>
    </form>


    <!-- Quỹ tiết kiệm -->
    <div class="card card-saving">
        <h4>Quỹ tiết kiệm tháng <?= "$saving_month_only/$saving_year" ?></h4>
        <p><?= format_vnd_with_usd($month_saving) ?></p>
    </div>

    <!-- Tổng Thu/Chi/Số dư -->
    <div class="cards-container">
        <div class="card card-income">
            <h4>Tổng Thu</h4>
            <p><?= format_vnd_with_usd($total_income) ?></p>
        </div>
        <div class="card card-expense">
            <h4>Tổng Chi</h4>
            <p><?= format_vnd_with_usd($total_expense_with_savings) ?></p>
        </div>
        <div class="card card-balance">
            <h4>Số dư</h4>
            <p><?= format_vnd_with_usd($balance) ?></p>
        </div>
    </div>

    <!-- Bảng giao dịch -->
    <table>
        <thead>
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
                <tr class="<?= $row['category_name']==='Quỹ ngân sách' ? 'highlight-saving' : '' ?>">
                    <td><?= $row['transaction_date'] ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= ($row['type'] === 'Thu') ? 'Thu' : 'Chi' ?></td>
                    <td><?= format_vnd_with_usd($row['amount']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">Không có giao dịch nào.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- CSS pastel nhạt hơn -->
    <style>
        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f4f8;
    margin: 0;
    color: #333;
    }
        /* Form lọc */
        form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
            margin-bottom: 30px;
        }
        form div {
            display: flex;
            flex-direction: column;
        }
        form label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        form input[type="date"],
        form input[type="month"] {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        form button {
            padding: 8px 16px;
            background: #81c784; /* xanh nhạt pastel */
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        form button:hover {
            background: #66bb6a;
        }
        .section-title {
    text-align: center;
    color: #1a73e8;
    margin-bottom: 25px;
    font-size: 24px;
    font-weight: 600;
}

.filter-form {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 25px;
    align-items: flex-end;
    margin-bottom: 30px;
}

.filter-form div {
    display: flex;
    flex-direction: column;
    text-align: center;
}
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            flex: 1;
            min-width: 220px;
            padding: 20px;
            border-radius: 10px;
            color: #333;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .card p {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Màu nhạt pastel cho từng card */
        .card-saving {
            background: #e3f2fd; /* xanh nhạt */
            border-left: 5px solid #2196f3;
        }
        .card-income {
            background: #fff8e1; /* vàng nhạt */
            border-left: 5px solid #ffc107;
        }
        .card-expense {
            background: #ffebee; /* đỏ nhạt */
            border-left: 5px solid #f44336;
        }
        .card-balance {
            background: #e8f5e9; /* xanh lá nhạt */
            border-left: 5px solid #4caf50;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        table thead {
            background: #f2f2f2;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            font-size: 14px;
        }
        table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        table tbody tr:hover {
            background: #e3f2fd;
        }
        .highlight-saving {
            background: #e8f5e9 !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            form {
                flex-direction: column;
                align-items: flex-start;
            }
            .cards-container {
                flex-direction: column;
            }
            table th, table td {
                font-size: 13px;
                padding: 10px;
            }
        }
    </style>
</section>

<?php require 'footer.php'; ?>