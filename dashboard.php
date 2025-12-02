<?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    require 'header.php';
    require 'db_connect.php';
    require 'functions.php';  

    $current_user_id = $_SESSION['user_id'];
    $current_username = $_SESSION['username'];

    /* ------------------------------
    LẤY THÁNG/NĂM ĐƯỢC CHỌN
    ------------------------------ */

    // Nếu có GET → lấy GET
    $selected_month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
    $selected_year  = isset($_GET['year'])  ? intval($_GET['year'])  : intval(date('Y'));

    // Format Y-m dùng cho SQL
    $selected_ym = $selected_year . '-' . str_pad($selected_month, 2, '0', STR_PAD_LEFT);

    /* ------------------------------
    KIỂM TRA THÁNG HIỆN TẠI 
    ------------------------------ */

    $current_year_num = intval(date('Y'));
    $current_month_num = intval(date('m'));
    $is_current_month = ($selected_month === $current_month_num && $selected_year === $current_year_num);

    // Tính Tổng Thu nhập
    $income_result = $conn->query(
        "SELECT SUM(t.amount) AS total_income
        FROM Transactions t
        JOIN Categories c ON t.category_id = c.category_id
        WHERE t.user_id = $current_user_id 
        AND c.type = 'income'
        AND DATE_FORMAT(t.transaction_date, '%Y-%m') = '$selected_ym'"
    );

    $total_income = $income_result->fetch_assoc()['total_income'] ?? 0;

    // Tính Tổng Chi tiêu
    $expense_result = $conn->query(
        "SELECT SUM(t.amount) AS total_expense
        FROM Transactions t
        JOIN Categories c ON t.category_id = c.category_id
        WHERE t.user_id = $current_user_id 
        AND c.type = 'expense'
        AND DATE_FORMAT(t.transaction_date, '%Y-%m') = '$selected_ym'"
    );
    $total_expense = $expense_result->fetch_assoc()['total_expense'] ?? 0;

    // Tổng quỹ tiết kiệm trong tháng
    $savings_result = $conn->query("
        SELECT SUM(amount) AS total_savings
        FROM savings
        WHERE user_id = $current_user_id
        AND DATE_FORMAT(created_at, '%Y-%m') = '$selected_ym'
    ");
    $total_savings = $savings_result->fetch_assoc()['total_savings'] ?? 0;

    // Số dư thực tế = Tổng thu - Tổng chi - Quỹ tiết kiệm
$balance = $total_income - $total_expense - $total_savings;


    /* ============================
    🔵 THÊM PHẦN NGÂN SÁCH THÁNG
    ============================ */
    $current_month_num = $selected_month;
    $current_year = $selected_year;

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



   // Tổng chi = chi tiêu + quỹ tiết kiệm
$total_expense_with_savings = $total_expense + $total_savings;

// % ngân sách
$used_percent = $monthly_budget > 0 ? round(($total_expense_with_savings / $monthly_budget) * 100) : 0;

// Cảnh báo ngân sách
$budget_warning = "";
if ($monthly_budget > 0) {
    if ($total_expense_with_savings > $monthly_budget) {
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
        AND DATE_FORMAT(t.transaction_date, '%Y-%m') = '$selected_ym'
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
    // Lấy tất cả quỹ tiết kiệm để hiển thị
    $all_savings_result = $conn->query("SELECT * FROM savings WHERE user_id=$current_user_id ORDER BY created_at DESC");

    if($total_savings > 0){
        $chart_labels[] = "Quỹ tiết kiệm";
        $chart_values[] = $total_savings;
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

    
    $edit_reminder = null;
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        $res = $conn->query("SELECT * FROM reminders WHERE id=$edit_id AND user_id=$current_user_id LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $edit_reminder = $res->fetch_assoc();
        }
    }
    // Lấy danh sách nhắc nhở
    $today = date('Y-m-d');
    $warning_date = date('Y-m-d', strtotime('+3 days')); // 3 ngày sau

    $reminders_result = $conn->query("
        SELECT *, 
            CASE 
                WHEN remind_date <= '$today' THEN 'overdue'
                WHEN remind_date <= '$warning_date' THEN 'upcoming'
                ELSE 'normal'
            END AS status_flag
        FROM reminders 
        WHERE user_id = $current_user_id 
        ORDER BY remind_date ASC
    ");


    ?>

    <!DOCTYPE html>
    <html>

    <head>
        <title>Bảng điều khiển</title>
        <link rel="stylesheet" href="css/style.css">

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            /* CSS CHUNG */
            .summary {
                display: flex;
                justify-content: space-around;
                background: #f4f4f4;
                padding: 20px;
                border-radius: 8px;
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

            .add-transaction, .add-income {
                flex: 1;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
            }
            .add-income {
                background-color: #f0f8ff;
            }

            .chart-container {
                flex: 1;
                max-width: 400px;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
                background: #fff;
            }
            .add-transaction form, .add-income form {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
             .add-transaction label, .add-income label {
                font-weight: bold;
                margin-top: 5px;
            }
            .add-transaction input, .add-income input, .add-transaction select, .add-income select, .add-transaction textarea, .add-income textarea {
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .add-transaction button, .add-income button {
                margin-top: 10px;
            }
            
            /* CSS RIÊNG PHẦN QUỸ TIẾT KIỆM (CARD VIEW) */
            .savings-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
                gap: 20px;
                margin-top: 15px;
            }

            .saving-card {
                border: 1px solid #1cc88a; 
                border-radius: 10px;
                padding: 15px;
                background-color: #f7fff7;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                transition: transform 0.2s;
            }

            .saving-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            }

            .saving-card h3 {
                margin-top: 0;
                color: #17a673;
                font-size: 1.2rem;
                border-bottom: 2px solid #e0f0e0;
                padding-bottom: 8px;
                margin-bottom: 10px;
            }

            .saving-card p {
                margin: 5px 0;
            }

            .saving-card .amount {
                font-size: 1.5rem;
                font-weight: bold;
                color: #00796b; 
            }

            .saving-actions {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px dashed #ccc;
            }

            .saving-actions a {
                margin-right: 10px;
                text-decoration: none;
                font-weight: bold;
            }
            /* End CSS RIÊNG PHẦN QUỸ TIẾT KIỆM */

            /* CSS REMINDER & BUDGET */
            .mark-done-link {
                padding: 5px 10px;
                background-color: #1cc88a;
                color: white;
                border-radius: 5px;
                text-decoration: none;
                margin-left: 5px;
            }

            .mark-done-link:hover {
                background-color: #17a673;
            }

            .add-reminder {
                margin: 20px 0;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
                background-color: #fdfdfd;
            }
            .add-reminder h2 {
                margin-bottom: 10px;
                color: #333;
            }

            .reminder-list {
                margin-top: 30px;
            }

            .reminder-list table {
                width: 100%;
                border-collapse: collapse;
            }

            .reminder-list th, .reminder-list td {
                padding: 10px;
                border: 1px solid #ccc;
                text-align: left;
            }

            .done-btn {
                padding: 5px 10px;
                background-color: #1cc88a;
                color: white;
                border-radius: 5px;
                text-decoration: none;
            }
            .done-btn:hover {
                background-color: #17a673;
            }

            .budget-form, .currency-converter {
                margin: 20px 0;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 10px;
                background-color: #fdfdfd;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }

            .budget-form h2, .currency-converter h2 {
                margin-bottom: 15px;
                color: #333;
                font-size: 1.6rem;
            }

            .form-group {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 10px;
                margin-bottom: 15px;
            }

            .form-group label {
                min-width: 120px;
                font-weight: 500;
                color: #555;
            }

            .form-group input {
                flex: 1;
                padding: 10px;
                border-radius: 6px;
                border: 1px solid #ccc;
                font-size: 1rem;
            }

            .btn-submit {
                padding: 10px 18px;
                background-color: #1cc88a;
                color: white;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 1rem;
                transition: background 0.3s;
            }

            .btn-submit:hover {
                background-color: #17a673;
            }

            @media (max-width: 600px) {
                .form-group {
                    flex-direction: column;
                    align-items: stretch;
                }
                .form-group label {
                    min-width: auto;
                }
                .form-group input, .form-group button {
                    width: 100%;
                }
            }
            
            /* CSS TRANSACTION LIST */
            .transaction-list {
                margin-top: 30px;
            }

            .transaction-list h2 {
                font-size: 1.8rem;
                margin-bottom: 15px;
                color: #333;
            }

            .table-container {
                max-height: 400px; 
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 8px;
            }

            .transaction-list table {
                width: 100%;
                border-collapse: collapse;
                min-width: 600px;
            }

            .transaction-list thead {
                background-color: #1cc88a;
                color: white;
                position: sticky;
                top: 0;
            }

            .transaction-list th, .transaction-list td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid #eee;
            }

            .transaction-list tbody tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .transaction-list tbody tr:hover {
                background-color: #d1f0e2;
            }

            .edit-transaction-btn, .delete-btn {
                padding: 5px 10px;
                border-radius: 5px;
                text-decoration: none;
                font-size: 0.9rem;
                margin-right: 5px;
            }
             .edit-transaction-btn {
                background-color: #4e73df;
                color: white;
            }

            .edit-transaction-btn:hover {
                background-color: #2e59d9;
            }


            .delete-btn {
                background-color: #e74a3b;
                color: white;
            }

            .delete-btn:hover {
                background-color: #c82333;
            }

            .no-data {
                text-align: center;
                color: #888;
                font-style: italic;
            }

        </style>
    </head>

    <body>
    <main>
        <section class="summary">
    <div class="summary-box">
        <h3>Tổng Thu</h3>
        <p class="income"><?php echo format_vnd_with_usd($total_income); ?></p>
    </div>
    <div class="summary-box">
    <h3>Tổng Chi</h3>
    <p class="expense"><?php echo format_vnd_with_usd($total_expense_with_savings); ?></p>
</div>

    <div class="summary-box">
        <h3>Quỹ tiết kiệm</h3>
        <p style="color:green; font-weight:bold;"><?php echo format_vnd_with_usd($total_savings); ?></p>
    </div>
    <div class="summary-box">
        <h3>Số dư</h3>
        <p class="balance"><?php echo format_vnd_with_usd($balance); ?></p>
    </div>
</section>


<section class="summary" style="margin-top: 10px; background:#fff7e6; border:1px solid #ffcc80;">
    <div class="summary-box">
        <h3>Ngân sách tháng này</h3>
        <p style="color:#f57c00; font-weight:bold;" id="monthly-budget"><?php echo format_vnd_with_usd($monthly_budget); ?></p>
    </div>

    <div class="summary-box">
        <h3>Đã chi / Ngân sách</h3>
        <p style="color:#d84315; font-weight:bold;" id="expense-budget">
    <?php echo format_vnd_with_usd($total_expense_with_savings); ?> / <?php echo format_vnd_with_usd($monthly_budget); ?>
</p>

    </div>

    <div class="summary-box">
        <h3>Tiến độ</h3>
        <p style="color:#0288d1; font-weight:bold;" id="budget-progress"><?php echo $used_percent; ?>%</p>
    </div>
</section>

<div id="budget-warning" style="margin:15px; padding:12px; background:#ffe0b2; border-left:5px solid #f57c00; font-size:16px; <?php echo $budget_warning!="" ? "display:block;" : "display:none;"; ?>">
    <strong><?php echo $budget_warning; ?></strong>
</div>
<section class="savings-form" style="margin:20px 0; padding:15px; border:1px solid #ccc; border-radius:8px; background:#f0fff0;">
    <h2>Tạo Quỹ Tiết kiệm từ số dư còn lại</h2>
    <form action="actions/action_create_savings.php" method="POST">
        <p>Số dư còn lại: <strong><?php echo format_vnd_with_usd($balance); ?></strong></p>
        <label>Số tiền muốn tạo quỹ tiết kiệm (VND):</label>
        <input type="number" name="amount" min="1" value="100000" required>
        <button type="submit" class="btn-submit">Tạo Quỹ Tiết kiệm</button>

    </form>
</section>

<section class="savings-list" style="margin-top:20px;">
    <h2>Các quỹ tiết kiệm</h2>
    <div class="savings-grid">
        <?php
        if($all_savings_result->num_rows > 0){
            while($row = $all_savings_result->fetch_assoc()){
                echo "<div class='saving-card'>";
                echo "<h3>" . htmlspecialchars($row['name'])."</h3>";
                echo "<p>Số tiền: <span class='amount'>" . format_vnd_with_usd($row['amount'])."</span></p>";
                echo "<p>Ngày tạo: " . date('d/m/Y', strtotime($row['created_at']))."</p>";
                echo "<div class='saving-actions'>";
                echo "<a href='dashboard.php?edit_saving_id={$row['id']}' class='edit-saving-btn' data-id='{$row['id']}' data-name='".htmlspecialchars($row['name'], ENT_QUOTES)."' data-amount='{$row['amount']}' style='color:#4e73df;'>Sửa</a>";
                echo "<a href='actions/action_delete_saving.php?id={$row['id']}' onclick=\"return confirm('Bạn có chắc chắn muốn xóa quỹ này?')\" style='color:#e74a3b;'>Xóa</a>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>Chưa có quỹ tiết kiệm nào.</p>";
        }
        ?>
    </div>
</section>
<section class="edit-saving-form" style="display:none; margin-top:20px; padding:15px; border:1px solid #ccc; border-radius:8px; background:#f9f9f9;">
    <h2>Sửa Quỹ Tiết kiệm</h2>
    <form id="edit-saving-form" method="POST" action="actions/action_edit_saving.php">
        <input type="hidden" name="id" id="edit_saving_id">
        <div class="form-group">
            <label>Tên quỹ:</label>
            <input type="text" name="name" id="edit-saving-name" required>
        </div>
        <div class="form-group">
            <label>Số tiền:</label>
            <input type="number" name="amount" id="edit-saving-amount" required>
        </div>
        <button type="submit" class="btn-submit">Cập nhật</button>
        <button type="button" id="cancel-edit-saving" class="btn-submit" style="background:#e74a3b;">Hủy</button>
    </form>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

    // Mở form sửa khi click nút Edit
    $(document).on('click', '.edit-saving-btn', function(e){
        e.preventDefault();
        let id = $(this).data('id');
        let name = $(this).data('name');
        let amount = $(this).data('amount');

        $('#edit_saving_id').val(id);
        $('#edit-saving-name').val(name);
        $('#edit-saving-amount').val(amount);

        $('.edit-saving-form').slideDown();
        $('html, body').animate({ scrollTop: $('.edit-saving-form').offset().top }, 300);
    });

    // Hủy form
    $('#cancel-edit-saving').click(function(){
        $('.edit-saving-form').slideUp();
        $('#edit-saving-form')[0].reset();
    });

    // Submit form bằng AJAX
    $('#edit-saving-form').submit(function(e){
        e.preventDefault(); // Ngăn reload
        let formData = $(this).serialize();

        $.ajax({
            url: 'actions/action_edit_saving.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res){
                if(res.success){
                    alert('Cập nhật thành công!');
                    $('.edit-saving-form').slideUp();
                    $('#edit-saving-form')[0].reset();
                    // Reload để cập nhật danh sách quỹ tiết kiệm (card view)
                    location.reload(); 
                } else {
                    alert('Lỗi: '+res.message);
                }
            },
            error: function(){
                alert('Có lỗi xảy ra!');
            }
        });
    });

});
</script>



        <div class="content">
            <section class="add-transaction">
                <h2>Thêm Chi tiêu</h2>
                <form action="actions/action_add_transaction.php" method="POST">
                    <label>Số tiền:</label>
                    <input type="number" name="amount" required>

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

                    <button type="submit" class="btn-submit">Thêm Chi tiêu</button>
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

                    <button type="submit" class="btn-submit">Thêm Thu nhập</button>
                </form>
            </section>
            
            <section class="chart-container">
                <h2>Chi tiêu tháng này</h2>
                <canvas id="expensePieChart"></canvas>
            </section>
        </div>


        <section class="budget-form">
        <h2>Đặt ngân sách tháng</h2>
        <form action="actions/action_set_budget.php" method="POST">
            <div class="form-group">
                <label>Ngân sách (VND):</label>
                <input type="number" name="budget_amount" required placeholder="Nhập số tiền ngân sách">
            </div>
            <button type="submit" class="btn-submit">Lưu ngân sách</button>
        </form>
    </section>

    <section class="add-reminder">
        <h2><?php echo $edit_reminder ? "Sửa Ghi chú" : "Thêm Ghi chú"; ?></h2>
        <form id="reminder-form" action="actions/action_add_or_edit_reminder.php" method="POST">
            <?php if ($edit_reminder): ?>
                <input type="hidden" name="id" value="<?php echo $edit_reminder['id']; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Tiêu đề:</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($edit_reminder['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Mô tả:</label>
                <textarea name="description"><?php echo htmlspecialchars($edit_reminder['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Ngày nhắc:</label>
                <input type="date" name="remind_date" value="<?php echo $edit_reminder['remind_date'] ?? ''; ?>" required>
            </div>
            <button type="submit" class="btn-submit"><?php echo $edit_reminder ? "Cập nhật" : "Lưu ghi chú"; ?></button>
        </form>

    <script>
$(document).ready(function() {

    $("#reminder-form").submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "actions/action_add_or_edit_reminder.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(res) {

                if (!res.success) {
                    alert(res.message || "Lỗi không xác định!");
                    return;
                }

                // Tạo hàng HTML mới
                let rowHtml = `
                    <tr ${res.row_style || ''}>
                        <td>${res.title}</td>
                        <td>${res.description}</td>
                        <td>${res.remind_date_formatted}</td>
                        <td>${res.status_text}</td>
                        <td>
                            <a href="dashboard.php?edit_id=${res.id}" class="edit-reminder-form">Sửa</a>
                            <a href="actions/action_delete_reminder.php?id=${res.id}" onclick="return confirm('Bạn có muốn xóa?')">Xóa</a>
                            <a href="actions/action_complete_reminder.php?id=${res.id}" onclick="return confirm('Hoàn thành?')" class="mark-done-link">Hoàn thành</a>
                        </td>
                    </tr>
                `;
                
                // Cập nhật lại giao diện (reload) để xử lý trạng thái
                location.reload();

                $("#reminder-form")[0].reset();
            },
            error: function() {
                alert("Lỗi kết nối server!");
            }
        });
    });

});
</script>


    </section>



    <section class="reminder-list">
        <h2>Nhắc nhở & Ghi chú</h2>
        <table>
            <thead>
                <tr>
                    <th>Tiêu đề</th>
                    <th>Mô tả</th>
                    <th>Ngày nhắc</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody class="reminder-tbody">

                <?php
                if ($reminders_result->num_rows > 0) {
                    while ($row = $reminders_result->fetch_assoc()) {
                        $status_flag = $row['status_flag'];
                        
                        date_default_timezone_set('Asia/Ho_Chi_Minh'); // lấy múi giờ ở VN
                        $today = date('Y-m-d');
                        $remind_date = $row['remind_date'];

                        if ($row['is_done']) {
                            $status_text = "✅ Hoàn thành";
                            $row_style = "style='background:#e0f7fa;'";
                        } elseif ($remind_date < $today) {
                            $status_text = "❌ Trễ hạn";
                            $row_style = "style='background:#ffcdd2;'";
                        } elseif ($remind_date == $today) {
                            $status_text = "⚠️ Đến hạn";
                            $row_style = "style='background:#ffe0b2;'";
                        } elseif ($remind_date <= date('Y-m-d', strtotime('+3 days'))) {
                            $status_text = "🔔 Sắp tới";
                            $row_style = "style='background:#fff3e0;'";
                        } else {
                            $status_text = "";
                            $row_style = "";
                        }



                        echo "<tr $row_style>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td>" . date('d/m/Y', strtotime($row['remind_date'])) . "</td>";
                        echo "<td>$status_text</td>";
                        echo "<td>
                            <a href='dashboard.php?edit_id=" . $row['id'] . "' 
                                class='edit-reminder-form' 
                                data-id='" . $row['id'] . "'>Sửa</a>

                            <a href='actions/action_delete_reminder.php?id=" . $row['id'] . "' 
                                onclick='return confirm(\"Bạn có chắc chắn muốn xóa ghi chú này?\")' 
                                class='delete-btn'>Xóa</a>";
                            
                            if (!$row['is_done']) {
                                echo "<a href='actions/action_complete_reminder.php?id=" . $row['id'] . "' 
                                    class='mark-done-link' 
                                    onclick=\"return confirm('Đánh dấu nhắc nhở này là hoàn thành?')\">
                                    Hoàn thành
                                </a>";
                            }


                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Chưa có nhắc nhở nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>



        <section class="transaction-list">
        <section class="edit-transaction" style="display:none; border:1px solid #ccc; padding:15px; margin-top:20px; background:#f9f9f9;">
        <h2>Sửa Giao dịch</h2>
        <form id="edit-transaction-form">
            <input type="hidden" name="transaction_id" id="edit-transaction-id">
            
            <div class="form-group">
                <label>Số tiền:</label>
                <input type="number" name="amount" id="edit-amount" required>
            </div>

            <div class="form-group">
                <label>Ngày:</label>
                <input type="date" name="date" id="edit-date" required>
            </div>

            <div class="form-group">
                <label>Danh mục:</label>
                <select name="category_id" id="edit-category" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php
                    // Lấy lại danh sách danh mục
                    $categories_result = $conn->query("SELECT * FROM Categories WHERE user_id=$current_user_id");
                    if ($categories_result->num_rows > 0) {
                        // Reset pointer
                        $categories_result->data_seek(0);
                        while($cat = $categories_result->fetch_assoc()){
                            echo "<option value='{$cat['category_id']}'>{$cat['name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Ghi chú:</label>
                <textarea name="description" id="edit-description"></textarea>
            </div>

            <button type="submit" class="btn-submit">Cập nhật</button>
            <button type="button" id="cancel-edit" class="btn-submit" style="background:#e74a3b; margin-left:10px;">Hủy</button>
        </form>
    </section>

         <h2>Giao dịch gần đây</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Danh mục</th>
                    <th>Loại</th> 
                    <th>Số tiền</th>
                    <th>Ghi chú</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $transactions_result = $conn->query("
                    SELECT t.transaction_id, t.amount, t.transaction_date, t.description, 
                           c.name AS category_name, c.category_id, c.type AS transaction_type
                    FROM Transactions t
                    JOIN Categories c ON t.category_id = c.category_id
                    WHERE t.user_id = $current_user_id
                    ORDER BY t.transaction_date DESC, t.transaction_id DESC
                    LIMIT 20
                ");

                if ($transactions_result->num_rows > 0) {
                    while ($row = $transactions_result->fetch_assoc()) {
                        $type_label = $row['transaction_type'] === 'income' ? 'Thu' : 'Chi';
                        echo "<tr>";
                        echo "<td>" . date('d/m/Y', strtotime($row['transaction_date'])) . "</td>"; // Ngày
                        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>"; // Danh mục
                        echo "<td>$type_label</td>"; // Loại
                        echo "<td>" . format_vnd_with_usd($row['amount']) . "</td>"; // Số tiền
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>"; // Ghi chú
                        echo "<td>
                                <a href=\"#\" class=\"edit-transaction-btn\" 
                                data-id=\"{$row['transaction_id']}\" 
                                data-amount=\"{$row['amount']}\" 
                                data-date=\"{$row['transaction_date']}\" 
                                data-category=\"{$row['category_id']}\" 
                                data-description=\"".htmlspecialchars($row['description'], ENT_QUOTES)."\">
                                Sửa
                                </a>
                                <a href=\"actions/action_delete_transaction.php?id={$row['transaction_id']}\"  
                                onclick=\"return confirm('Bạn có chắc chắn muốn xóa giao dịch này?')\" 
                                class=\"delete-btn\">Xóa</a>
                            </td>";
                        echo "</tr>";

                    }
                } else {
                    echo "<tr><td colspan='6' class='no-data'>Chưa có giao dịch nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                
    <script>
    $(document).ready(function(){

        let expensePieChart = null;
    // Khởi tạo Pie chart ban đầu
    const ctx = document.getElementById('expensePieChart').getContext('2d');
    const labels = <?php echo $js_chart_labels; ?>;
    const dataValues = <?php echo $js_chart_values; ?>;

    if(labels.length > 0){
        expensePieChart = new Chart(ctx,{
            type:'pie',
            data:{
                labels: labels,
                datasets:[{
                    label:'Chi tiêu',
                    data: dataValues,
                    backgroundColor:[
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ]
                }]
            },
            options:{
                responsive:true,
                plugins:{
                    legend:{ position:'bottom' },
                    tooltip:{
                        callbacks:{
                            label:function(context){
                                // Lấy giá trị gốc (raw value) và format
                                const rawValue = context.raw;
                                const formattedValue = Number(rawValue).toLocaleString('vi-VN');
                                return context.label + ': ' + formattedValue + ' VND';
                            }
                        }
                    }
                }
            }
        });
    } else {
        // Vẽ chữ thông báo nếu không có dữ liệu
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Không có dữ liệu chi tiêu tháng này', ctx.canvas.width/2, ctx.canvas.height/2);
    }

    // Click Sửa giao dịch
    $(document).on('click', '.edit-transaction-btn', function(e){
        e.preventDefault();
        const id = $(this).data('id');
        $('#edit-transaction-id').val(id);
        $('#edit-amount').val($(this).data('amount'));
        $('#edit-date').val($(this).data('date'));
        $('#edit-category').val($(this).data('category'));
        $('#edit-description').val($(this).data('description'));
        $('.edit-transaction').show();
        $('html, body').animate({ scrollTop: $('.edit-transaction').offset().top }, 300);
    });

    // Hủy sửa
    $('#cancel-edit').click(function(){
        $('.edit-transaction').hide();
    });

    // Submit form sửa giao dịch
    $('#edit-transaction-form').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: 'actions/action_edit_transaction.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res){
                if(res.success){
                    // Khi update thành công, reload để cập nhật tất cả (summary, list, chart)
                    alert('Cập nhật giao dịch thành công!');
                    location.reload(); 
                } else {
                    alert(res.message || 'Lỗi cập nhật giao dịch!');
                }
            },
            error: function(){
                alert('Lỗi kết nối server!');
            }
        });
    });

});
</script>
        </div>
    </section>
    </main>

    </body>
    </html>
    <?php
$conn->close();
require 'footer.php';
?>