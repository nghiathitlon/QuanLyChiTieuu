<?php
// --------------------
// FUNCTIONS.PHP CHO DASHBOARD MỚI
// --------------------

// KẾT NỐI CSDL
function getDB() {
    $conn = new mysqli("localhost", "root", "", "quanlychitieu");
    if ($conn->connect_error) {
        die("Kết nối CSDL thất bại: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// KIỂM TRA LOGIN
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// LẤY TỶ GIÁ USD (TỰ ĐỘNG)
function get_usd_rate() {
    $url = "https://open.er-api.com/v6/latest/VND";
    $response = @file_get_contents($url);
    if ($response === FALSE) return 25000; // fallback
    $data = json_decode($response, true);
    if (isset($data["result"]) && $data["result"] === "success") {
        return 1 / $data["rates"]["USD"];
    }
    return 25000;
}

// FORMAT TIỀN VND + USD
function format_vnd_with_usd($amount) {
    $usd_rate = get_usd_rate();
    $usd_amount = round($amount / $usd_rate, 2);
    return number_format($amount, 0, ',', '.') . "₫ / " . number_format($usd_amount, 2, '.', ',') . " USD";
}

// LẤY DANH SÁCH GIAO DỊCH THEO USER
function get_transactions($user_id, $month = null, $year = null, $type = null) {
    $conn = getDB();

    $sql = "SELECT t.transaction_id, t.amount, t.transaction_date, t.description, c.name AS category_name, c.type
            FROM Transactions t
            JOIN Categories c ON t.category_id = c.category_id
            WHERE t.user_id = ?";

    if ($month && $year) {
        $sql .= " AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?";
    }

    if ($type) {
        $sql .= " AND c.type = ?";
    }

    $sql .= " ORDER BY t.transaction_date DESC, t.transaction_id DESC";

    $stmt = $conn->prepare($sql);
    if ($month && $year && $type) {
        $ym = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT);
        $stmt->bind_param("iss", $user_id, $ym, $type);
    } elseif ($month && $year) {
        $ym = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT);
        $stmt->bind_param("is", $user_id, $ym);
    } elseif ($type) {
        $stmt->bind_param("is", $user_id, $type);
    } else {
        $stmt->bind_param("i", $user_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    return $transactions;
}

// THÊM GIAO DỊCH
function add_transaction($user_id, $amount, $category_id, $description, $date) {
    $conn = getDB();
    $sql = "INSERT INTO Transactions(user_id, amount, category_id, description, transaction_date) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $user_id, $amount, $category_id, $description, $date);
    return $stmt->execute();
}

// XÓA GIAO DỊCH
function delete_transaction($transaction_id, $user_id) {
    $conn = getDB();
    $sql = "DELETE FROM Transactions WHERE transaction_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $transaction_id, $user_id);
    return $stmt->execute();
}

// CẬP NHẬT GIAO DỊCH
function update_transaction($transaction_id, $user_id, $amount, $category_id, $description, $date) {
    $conn = getDB();
    $sql = "UPDATE Transactions 
            SET amount = ?, category_id = ?, description = ?, transaction_date = ?
            WHERE transaction_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissii", $amount, $category_id, $description, $date, $transaction_id, $user_id);
    return $stmt->execute();
}

// LẤY TỔNG THEO THÁNG (INCOME / EXPENSE)
function get_total_by_type($user_id, $type, $month, $year) {
    $conn = getDB();
    $ym = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT);

    $sql = "SELECT SUM(t.amount) AS total
            FROM Transactions t
            JOIN Categories c ON t.category_id = c.category_id
            WHERE t.user_id = ? AND c.type = ? AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $type, $ym);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['total'] ?? 0;
}

// LẤY DỮ LIỆU BIỂU ĐỒ CHI TIÊU THEO DANH MỤC
function get_chart_data($user_id, $month, $year) {
    $conn = getDB();
    $ym = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT);

    $sql = "SELECT c.name, SUM(t.amount) AS total_amount
            FROM Transactions t
            JOIN Categories c ON t.category_id = c.category_id
            WHERE t.user_id = ? AND c.type = 'expense' AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?
            GROUP BY c.name
            ORDER BY total_amount DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $ym);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}
?>
