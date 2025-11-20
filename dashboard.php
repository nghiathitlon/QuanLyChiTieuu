<?php
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
require 'currency.php';  

$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];


// 1. LẤY SỐ LIỆU THỐNG KÊ TỔNG QUAN (cho tháng hiện tại)
$current_month = date('Y-m'); // Lấy tháng hiện tại

// Tính Tổng Thu nhập
$income_result = $conn->query(
    "SELECT SUM(t.amount) AS total_income
     FROM Transactions t
     JOIN Categories c ON t.category_id = c.category_id
     WHERE t.user_id = $current_user_id 
     AND c.type = 'income'
     AND DATE_FORMAT(t.transaction_date, '%Y-%m') = '$current_month'"
);
$total_income = $income_result->fetch_assoc()['total_income'] ?? 0;

// Tính Tổng Chi tiêu
$expense_result = $conn->query(
    "SELECT SUM(t.amount) AS total_expense
     FROM Transactions t
     JOIN Categories c ON t.category_id = c.category_id
     WHERE t.user_id = $current_user_id 
     AND c.type = 'expense'
     AND DATE_FORMAT(t.transaction_date, '%Y-%m') = '$current_month'"
);
$total_expense = $expense_result->fetch_assoc()['total_expense'] ?? 0;

// Tính Số dư
$balance = $total_income - $total_expense;

/* ============================
   🔵 THÊM PHẦN NGÂN SÁCH THÁNG
   ============================ */
$current_month_num = date('n');
$current_year = date('Y');

$budget_result = $conn->query("
    SELECT amount 
    FROM budget 
    WHERE user_id = $current_user_id
      AND month = $current_month_num
      AND year = $current_year
");

$monthly_budget = 0;

if ($budget_result && $budget_result->num_rows > 0) {
    $row = $budget_result->fetch_assoc();
    if ($row && isset($row['amount'])) {
        $monthly_budget = $row['amount'];
    }
}



// TÍNH % CHI TIÊU
$used_percent = $monthly_budget > 0 ? round(($total_expense / $monthly_budget) * 100) : 0;

// TẠO NHẮC NHỞ
$budget_warning = "";
if ($monthly_budget > 0) {
    if ($total_expense > $monthly_budget) {
        $budget_warning = "⚠️ Bạn đã vượt ngân sách tháng!";
    } elseif ($used_percent >= 90) {
        $budget_warning = "🔴 Cảnh báo! Bạn đã dùng $used_percent% ngân sách.";
    } elseif ($used_percent >= 70) {
        $budget_warning = "🟡 Bạn đã dùng $used_percent% ngân sách, hãy cẩn thận!";
    }
}

/* ============================
   HẾT PHẦN NGÂN SÁCH - CẢNH BÁO
   ============================ */

// 2. LẤY SỐ LIỆU CHO BIỂU ĐỒ
$chart_data_result = $conn->query(
    "SELECT c.name, SUM(t.amount) AS total_amount
     FROM Transactions t
     JOIN Categories c ON t.category_id = c.category_id
     WHERE t.user_id = $current_user_id 
     AND c.type = 'expense'
     AND DATE_FORMAT(t.transaction_date, '%Y-%m') = '$current_month'
     GROUP BY c.name
     ORDER BY total_amount DESC"
);

// Chuyển dữ liệu sang JS
$chart_labels = [];
$chart_values = [];
if ($chart_data_result->num_rows > 0) {
    while ($row = $chart_data_result->fetch_assoc()) {
        $chart_labels[] = $row['name'];
        $chart_values[] = $row['total_amount'];
    }
}
$js_chart_labels = json_encode($chart_labels);
$js_chart_values = json_encode($chart_values);

// Dữ liệu form thêm giao dịch
$categories_result = $conn->query("SELECT * FROM Categories WHERE user_id = $current_user_id AND type = 'expense'");
$expense_categories_result = $conn->query(
    "SELECT * FROM Categories WHERE user_id = $current_user_id AND type = 'expense'"
);
$income_categories_result = $conn->query(
    "SELECT * FROM Categories WHERE user_id = $current_user_id AND type = 'income'"
);

// Giao dịch gần đây
$transactions_result = $conn->query("
    SELECT t.transaction_id,  t.amount, t.transaction_date, t.description, c.name AS category_name
    FROM Transactions t
    JOIN Categories c ON t.category_id = c.category_id
    WHERE t.user_id = $current_user_id
    ORDER BY t.transaction_date DESC
    LIMIT 20
");

?>

<!DOCTYPE html>
<html>

<head>
    <title>Bảng điều khiển</title>
    <link rel="stylesheet" href="css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .summary {
            display: flex;
            justify-content: space-around;
            background: #f4f4f4;
            padding: 20px;
        }

        .summary-box {
            text-align: center;
        }

        .summary-box h3 {
            margin: 0;
        }

        .income {
            color: green;
        }

        .expense {
            color: red;
        }

        .balance {
            color: blue;
        }

        .content {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .add-transaction {
            flex: 1;
        }

        .chart-container {
            flex: 1;
            max-width: 400px;
        }
    </style>
</head>

<body>
    <header>
        <h1>Chào mừng, <?php echo htmlspecialchars($current_username); ?>!</h1>
        <nav>
            <a href="categories.php">Quản lý Danh mục</a> |
            <a href="actions/action_logout.php">Đăng xuất</a>
        </nav>
    </header>

    <!-- ===== PHẦN TỔNG QUAN ===== -->
    <section class="summary">
        <div class="summary-box">
            <h3>Tổng Thu (Tháng này)</h3>
            <p class="income"><?php echo format_vnd_with_usd($total_income); ?></p>

        </div>
        <div class="summary-box">
            <h3>Tổng Chi (Tháng này)</h3>
            <p class="expense"><?php echo format_vnd_with_usd($total_expense); ?></p>

        </div>
        <div class="summary-box">
            <h3>Số dư</h3>
            <p class="balance"><?php echo format_vnd_with_usd($balance); ?></p>

        </div>
    </section>


    <!-- ⭐ THÊM PHẦN NGÂN SÁCH THÁNG -->
    <section class="summary" style="margin-top: 10px; background:#fff7e6; border:1px solid #ffcc80;">
        <div class="summary-box">
            <h3>Ngân sách tháng này</h3>
            <p style="color:#f57c00; font-weight:bold;">
                <?php echo format_vnd_with_usd($monthly_budget); ?>

            </p>
        </div>

        <div class="summary-box">
            <h3>Đã chi / Ngân sách</h3>
            <p style="color:#d84315; font-weight:bold;">
                <?php echo format_vnd_with_usd($total_expense); ?> / <?php echo format_vnd_with_usd($monthly_budget); ?>

            </p>
        </div>

        <div class="summary-box">
            <h3>Tiến độ</h3>
            <p style="color:#0288d1; font-weight:bold;">
                <?php echo $used_percent; ?>%
            </p>
        </div>
    </section>

    <?php if ($budget_warning != ""): ?>
        <div style="margin: 15px; padding: 12px; background:#ffe0b2; border-left: 5px solid #f57c00; font-size: 16px;">
            <strong><?php echo $budget_warning; ?></strong>
        </div>
    <?php endif; ?>


    <main class="content">
        <section class="add-transaction">
            <h2>Thêm Chi tiêu</h2>
            <form action="actions/action_add_transaction.php" method="POST">
                <label>Số tiền:</label>
                <input type="number" name="amount" require>

                <label>Ngày:</label>
                <input type="date" name="date" required>

                <label>Danh mục:</label>
                <select name="category_id" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php
                    if ($expense_categories_result->num_rows > 0) {
                        while ($row = $expense_categories_result->fetch_assoc()) {
                            echo "<option value='{$row['category_id']}'>{$row['name']}</option>";
                        }
                    }
                    ?>
                </select>

                <label>Ghi chú:</label>
                <textarea name="description"></textarea>

                <button type="submit">Thêm Chi tiêu</button>
            </form>
        </section>

        <section class="add-income" style="background-color: #f0f8ff;">
            <h2>Thêm Thu nhập</h2>
            <form action="actions/action_add_transaction.php" method="POST">
                <label>Số tiền:</label>
                <input type="number" name="amount" required>

                <label>Ngày:</label>
                <input type="date" name="date" required>

                <label>Danh mục:</label>
                <select name="category_id" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php
                    if ($income_categories_result->num_rows > 0) {
                        while ($row = $income_categories_result->fetch_assoc()) {
                            echo "<option value='{$row['category_id']}'>{$row['name']}</option>";
                        }
                    }
                    ?>
                </select>

                <label>Ghi chú:</label>
                <textarea name="description"></textarea>

                <button type="submit">Thêm Thu nhập</button>
            </form>
        </section>

<section style="margin:20px; padding:15px; border:1px solid #ccc;">
    <h2>Chuyển đổi VND → USD</h2>

    <input id="vnd_input" type="number" placeholder="Nhập số tiền VND" 
           style="padding:8px; width:200px">

    <button onclick="convertVND()" 
            style="padding:8px 12px; margin-left:10px; cursor:pointer;">
        Chuyển đổi
    </button>

    <p id="convert_result" style="margin-top:10px; font-size:18px; font-weight:bold;"></p>
</section>

<script>
function convertVND() {
    let vnd = document.getElementById("vnd_input").value;

    if (vnd.trim() === "") {
        alert("Vui lòng nhập số tiền!");
        return;
    }

    fetch("convert.php?amount=" + encodeURIComponent(vnd))
        .then(res => res.json())
        .then(data => {
            if (!data.ok) {
                document.getElementById("convert_result").innerHTML =
                    "Lỗi chuyển đổi: " + data.error;
                return;
            }

            document.getElementById("convert_result").innerHTML =
                Number(data.vnd).toLocaleString() + " VND = " +
                "<span style='color:red'>" +
                Number(data.usd).toLocaleString() + " USD</span>";
        })
        .catch(err => {
            document.getElementById("convert_result").innerHTML = 
                "Lỗi chuyển đổi!";
        });
}
</script>
        <section class="chart-container">
            <h2>Chi tiêu tháng này</h2>
            <canvas id="expensePieChart"></canvas>
        </section>
    </main>

    <!-- ⭐ FORM ĐẶT NGÂN SÁCH THÁNG -->
    <section style="margin: 20px; padding: 15px; border:1px solid #ccc;">
        <h2>Đặt ngân sách tháng</h2>
        <form action="actions/action_set_budget.php" method="POST">
            <label>Ngân sách (VND):</label>
            <input type="number" name="budget_amount" required>

            <button type="submit">Lưu ngân sách</button>
        </form>
    </section>


    <section class="transaction-list">
        <h2>Giao dịch gần đây</h2>
        <table>
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Danh mục</th>
                    <th>Số tiền</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($transactions_result->num_rows > 0) {
                    while ($row = $transactions_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['transaction_date'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                        echo "<td>" . format_vnd_with_usd($row['amount']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";

                        echo "<td>
                            <a href='edit_transaction.php?id={$row['transaction_id']}'>Sửa</a> | 
                            <a href='actions/action_delete_transaction.php?id={$row['transaction_id']}'
                               onclick='return confirm(\"Bạn có chắc chắn muốn xóa giao dịch này?\")'
                               style='color:red;'>Xóa</a>
                        </td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Chưa có giao dịch nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>



    <script>
        const labels = <?php echo $js_chart_labels; ?>;
        const dataValues = <?php echo $js_chart_values; ?>;

        if (labels.length > 0) {
            const ctx = document.getElementById('expensePieChart').getContext('2d');
            const expensePieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Chi tiêu',
                        data: dataValues,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)'
                        ],
                        hoverOffset: 4
                    }]
                }
            });
        } else {
            const ctx = document.getElementById('expensePieChart').getContext('2d');
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Không có dữ liệu chi tiêu tháng này', 150, 100);
        }
    </script>

    
</body>

</html>

<?php
$conn->close();
?>
<?php require 'footer.php'; ?>
