<?php
require 'db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = intval($_SESSION['user_id']);
$goal_id = intval($_GET['id'] ?? 0);

// Lấy mục tiêu (bảo mật: thuộc user)
$stmt = $conn->prepare("SELECT * FROM goals WHERE goal_id=? AND user_id=?");
$stmt->bind_param("ii",$goal_id,$user_id);
$stmt->execute();
$goal = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$goal){ echo "Mục tiêu không tìm thấy"; exit; }

// Xử lý thêm khoản tiết kiệm (POST AJAX có thể gọi add_saving.php, nhưng support cả form submit)
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='add_log'){
    $amount = floatval($_POST['amount']);
    $note = trim($_POST['note']);
    $date_added = $_POST['date_added'] ?: date('Y-m-d');

    $conn->begin_transaction();
    $ins = $conn->prepare("INSERT INTO savings_logs (goal_id, amount, note, date_added) VALUES (?, ?, ?, ?)");
    $ins->bind_param("idss",$goal_id,$amount,$note,$date_added);
    $ins->execute();
    $ins->close();

    $upd = $conn->prepare("UPDATE goals SET saved_amount = saved_amount + ? WHERE goal_id=?");
    $upd->bind_param("di",$amount,$goal_id);
    $upd->execute();
    $upd->close();

    // tự động đánh dấu hoàn thành nếu đạt
    $mark = $conn->prepare("UPDATE goals SET status='completed' WHERE goal_id=? AND saved_amount >= target_amount");
    $mark->bind_param("i",$goal_id);
    $mark->execute();
    $mark->close();

    $conn->commit();
    header("Location: goal_detail.php?id=".$goal_id);
    exit;
}

// Lấy logs
$stmt = $conn->prepare("SELECT * FROM savings_logs WHERE goal_id=? ORDER BY date_added DESC");
$stmt->bind_param("i",$goal_id);
$stmt->execute();
$logs = $stmt->get_result();
$stmt->close();

// Gợi ý cần tiết kiệm mỗi tháng
$monthly_needed_text = '—';
if(!empty($goal['deadline']) && $goal['status'] !== 'completed') {
    $today = new DateTime();
    $deadline = new DateTime($goal['deadline']);
    $interval = $today->diff($deadline);
    $months_left = max(1, $interval->y * 12 + $interval->m + ($interval->d>0 ? 1 : 0));
    $remaining_amount = max(0, $goal['target_amount'] - $goal['saved_amount']);
    $monthly_needed = $remaining_amount / $months_left;
    $monthly_needed_text = number_format($monthly_needed,0,',','.').' ₫ / tháng (còn '.$months_left.' tháng)';
}
require 'header.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chi tiết mục tiêu</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    .container{max-width:1000px;margin:20px auto}
    .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,0.06);margin-bottom:12px}
    table{width:100%;border-collapse:collapse}
    th, td{padding:8px;border-bottom:1px solid #eee}
    .btn{padding:6px 10px;border:none;border-radius:6px;cursor:pointer}
    </style>
</head>
<body>
<div class="container">
    <a href="goals.php">← Quay lại</a>
    <div class="card">
        <h2><?php echo htmlspecialchars($goal['goal_name']); ?></h2>
        <p>Đặt: <?php echo number_format($goal['target_amount'],0,',','.'); ?> ₫ — Đã tiết kiệm: <?php echo number_format($goal['saved_amount'],0,',','.'); ?> ₫</p>
        <p>Hạn: <?php echo $goal['deadline'] ?: '—'; ?> — Trạng thái: <?php echo $goal['status']; ?></p>
        <p>Gợi ý: <b><?php echo $monthly_needed_text; ?></b></p>

        <?php
        // Nhắc nhở popup hiển thị nếu còn ≤7 ngày và chưa hoàn thành
        if(!empty($goal['deadline']) && $goal['status']=='pending') {
            $d = new DateTime($goal['deadline']);
            $today = new DateTime();
            $diffDays = (int)$today->diff($d)->format("%r%a");
            if($diffDays <= 7 && $diffDays >= 0) {
                echo "<div style='background:#fff3cd;padding:10px;border-radius:8px;border:1px solid #ffeeba'>⚠ Mục tiêu sắp đến hạn trong <b>{$diffDays}</b> ngày. Hãy bổ sung tiền hoặc điều chỉnh deadline.</div>";
            } elseif($diffDays < 0) {
                echo "<div style='background:#f8d7da;padding:10px;border-radius:8px;border:1px solid #f5c6cb'>⚠ Mục tiêu đã quá hạn. Hãy cân nhắc điều chỉnh deadline hoặc số tiền tiết kiệm mỗi tháng.</div>";
            }
        }
        ?>
    </div>

    <div class="card">
        <h3>Biểu đồ tiến độ</h3>
        <canvas id="progressChart" width="400" height="200"></canvas>
    </div>

    <div class="card">
        <h3>Thêm khoản tiết kiệm</h3>
        <?php if($goal['status']=='completed') echo "<div style='color:green;font-weight:700'>Mục tiêu đã hoàn thành — không thể thêm nữa.</div>"; ?>
        <?php if($goal['status']!='completed'): ?>
        <form method="POST">
            <input type="hidden" name="action" value="add_log">
            <input name="amount" type="number" step="0.01" required placeholder="Số tiền (VNĐ)" style="padding:8px;width:100%">
            <input name="date_added" type="date" value="<?php echo date('Y-m-d'); ?>" style="padding:8px;width:100%;margin-top:8px">
            <input name="note" placeholder="Ghi chú" style="padding:8px;width:100%;margin-top:8px">
            <button class="btn" style="background:#1cc88a;color:#fff;margin-top:8px">Thêm</button>
        </form>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Lịch sử khoản tiết kiệm</h3>
        <table>
            <thead><tr><th>Ngày</th><th>Số tiền</th><th>Ghi chú</th><th>Hành động</th></tr></thead>
            <tbody>
            <?php while($l = $logs->fetch_assoc()): ?>
                <tr id="log-row-<?php echo $l['log_id']; ?>">
                    <td><?php echo $l['date_added']; ?></td>
                    <td><?php echo number_format($l['amount'],0,',','.'); ?> ₫</td>
                    <td><?php echo htmlspecialchars($l['note']); ?></td>
                    <td>
                        <button class="btn" onclick="openEdit(<?php echo $l['log_id']; ?>, <?php echo $l['amount']; ?>, '<?php echo addslashes($l['note']); ?>')">Sửa</button>
                        <button class="btn" onclick="deleteLog(<?php echo $l['log_id']; ?>)">Xóa</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal sửa -->
    <div id="editModal" style="display:none; position:fixed; left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.4);">
        <div style="background:#fff; margin:80px auto; padding:20px; width:400px; border-radius:8px;">
            <h3>Sửa khoản tiết kiệm</h3>
            <form id="editForm">
                <input type="hidden" name="log_id" id="edit_log_id">
                <input type="number" name="new_amount" id="edit_amount" step="0.01" required style="width:100%;padding:8px">
                <input type="text" name="new_note" id="edit_note" style="width:100%;padding:8px;margin-top:8px">
                <div style="margin-top:8px;">
                    <button type="button" onclick="submitEdit()" class="btn btn-primary">Lưu</button>
                    <button type="button" onclick="closeEdit()" class="btn btn-danger">Hủy</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
// Dữ liệu cho chart
const saved = <?php echo (float)$goal['saved_amount']; ?>;
const target = <?php echo (float)$goal['target_amount']; ?>;
const ctx = document.getElementById('progressChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Đã tiết kiệm', 'Còn thiếu'],
        datasets: [{
            data: [saved, Math.max(0, target - saved)]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Sửa / xóa log via AJAX
function openEdit(id, amount, note){
    document.getElementById('edit_log_id').value = id;
    document.getElementById('edit_amount').value = amount;
    document.getElementById('edit_note').value = note;
    document.getElementById('editModal').style.display = 'block';
}
function closeEdit(){ document.getElementById('editModal').style.display = 'none'; }

function submitEdit(){
    const id = document.getElementById('edit_log_id').value;
    const new_amount = document.getElementById('edit_amount').value;
    const new_note = document.getElementById('edit_note').value;
    fetch('savings_edit.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'log_id='+encodeURIComponent(id)+'&new_amount='+encodeURIComponent(new_amount)+'&new_note='+encodeURIComponent(new_note)
    }).then(r=>r.json()).then(j=>{
        if(j.ok){ location.reload(); } else { alert(j.error||'Lỗi'); }
    }).catch(e=>{ alert('Lỗi kết nối'); });
}

function deleteLog(id){
    if(!confirm('Xác nhận xóa khoản tiết kiệm này?')) return;
    fetch('savings_delete.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'log_id='+encodeURIComponent(id)
    }).then(r=>r.json()).then(j=>{
        if(j.ok){ document.getElementById('log-row-'+id).remove(); location.reload(); } else { alert(j.error||'Lỗi'); }
    }).catch(e=>{ alert('Lỗi kết nối'); });
}
</script>
</body>
</html>
