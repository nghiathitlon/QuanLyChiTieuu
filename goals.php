<?php
require 'db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = intval($_SESSION['user_id']);

// Thêm mục tiêu mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_goal') {
    $name = trim($_POST['goal_name']);
    $target = floatval($_POST['target_amount']);
    $deadline = $_POST['deadline'] ?: null;
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO goals (user_id, goal_name, target_amount, deadline, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $user_id, $name, $target, $deadline, $notes);
    $stmt->execute();
    $stmt->close();
    header("Location: goals.php");
    exit;
}

// Đánh dấu completed nếu saved_amount >= target_amount (an toàn: cho user này)
$update = $conn->prepare("UPDATE goals SET status='completed' WHERE user_id=? AND saved_amount >= target_amount AND status='pending'");
$update->bind_param("i",$user_id);
$update->execute();
$update->close();

// Lấy danh sách mục tiêu
$stmt = $conn->prepare("SELECT * FROM goals WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$goals = $stmt->get_result();
$stmt->close();

require 'header.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mục tiêu tài chính</title>
    <style>
    .container{max-width:1000px;margin:20px auto}
    .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,0.06);margin-bottom:12px}
    .progress{height:12px;background:#eee;border-radius:10px;overflow:hidden;margin-top:6px}
    .progress .fill{height:100%;background:#1cc88a}
    .btn{padding:8px 12px;border-radius:6px;border:none;cursor:pointer}
    .btn-primary{background:#4e73df;color:#fff}
    .btn-danger{background:#e74c3c;color:#fff}
    </style>
</head>
<body>
<div class="container">
    <h2 class="section-title,">Mục tiêu tài chính</h2> <style>
body {
    font-family: "Segoe UI", sans-serif;
    background: white;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}
.section-title {
    text-align: center;
    font-size: 22px;
    font-weight: 600;
    color: #2e59d9; /* xanh nhạt tinh tế */
    margin-bottom: 20px;
    margin-top: 5px;
    letter-spacing: 0.3px;
}
.container {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 15px;
}

h2 {
    text-align: center;
    color: #fff;
    margin-bottom: 25px;
    font-size: 28px;
}

.card {
    background: #ffffff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-3px);
}

input, textarea {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 15px;
    margin-top: 6px;
    transition: 0.3s;
}

input:focus, textarea:focus {
    border-color: #4e73df;
    box-shadow: 0 0 8px rgba(78,115,223,0.4);
    outline: none;
}

.btn {
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    border: none;
    transition: 0.3s;
}

.btn-primary {
    background: linear-gradient(45deg, #4e73df, #2e59d9);
    color: #fff;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #2e59d9, #1d4ed8);
    transform: scale(1.02);
}

.btn-danger {
    background: #e74c3c;
    color: #fff;
}

.progress {
    height: 14px;
    background: #eee;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 10px;
}

.progress .fill {
    height: 100%;
    background: #1cc88a;
    border-radius: 12px 0 0 12px;
    transition: width 0.4s ease;
}

small {
    color: #555;
}

h3 {
    margin: 0;
    font-size: 20px;
    color: #333;
}

span {
    font-weight: 600;
}

@media(max-width:600px) {
    .card {
        padding: 15px;
    }
    h2 { font-size: 24px; }
    h3 { font-size: 18px; }
}
</style>

    <div class="card">
        <form method="POST">
            <input type="hidden" name="action" value="create_goal">
            <div><input name="goal_name" placeholder="Tên mục tiêu" required style="width:100%;padding:8px"></div>
            <div style="margin-top:8px;"><input name="target_amount" type="number" step="0.01" placeholder="Số tiền mục tiêu (VNĐ)" required style="width:100%;padding:8px"></div>
            <div style="margin-top:8px;"><input name="deadline" type="date" style="padding:8px;width:100%"></div>
            <div style="margin-top:8px;"><textarea name="notes" placeholder="Ghi chú (tùy chọn)" style="width:100%;padding:8px"></textarea></div>
            <div style="margin-top:10px;"><button class="btn btn-primary" type="submit">Thêm mục tiêu</button></div>
        </form>
    </div>

    <?php while($g = $goals->fetch_assoc()): 
        $percent = $g['target_amount'] > 0 ? round($g['saved_amount'] / $g['target_amount'] * 100,2) : 0;
    ?>
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin:0"><?php echo htmlspecialchars($g['goal_name']); ?></h3>
                <small>Đặt: <?php echo number_format($g['target_amount'],0,',','.'); ?> ₫ — Đã tiết kiệm: <?php echo number_format($g['saved_amount'],0,',','.'); ?> ₫ (<?php echo $percent; ?>%)</small>
            </div>
            <div>
                <?php if($g['status'] === 'completed'): ?>
                    <span style="color:green;font-weight:700;">✔ Hoàn thành</span>
                <?php else: ?>
                    <a class="btn" href="goal_detail.php?id=<?php echo $g['goal_id']; ?>">Chi tiết</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="progress"><div class="fill" style="width:<?php echo min(100,$percent); ?>%"></div></div>

        <div style="margin-top:8px; display:flex; gap:8px;">
            <small>Hạn: <?php echo $g['deadline'] ?: '—'; ?></small>
            <?php
                if(!empty($g['deadline']) && $g['status'] === 'pending') {
                    $d = new DateTime($g['deadline']);
                    $today = new DateTime();
                    $diffDays = (int)$today->diff($d)->format("%r%a");
                    if($diffDays <= 7 && $diffDays >= 0) {
                        echo "<span style='color:#b80;'>⚠ Sắp đến hạn: {$diffDays} ngày</span>";
                    } elseif ($diffDays < 0) {
                        echo "<span style='color:#e74c3c;'>⚠ Đã quá hạn</span>";
                    }
                }
            ?>
        </div>
    </div>
    <?php endwhile; ?>

</div>
</body>
</html>
