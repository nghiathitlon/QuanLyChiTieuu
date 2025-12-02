<?php
$logged_in = isset($_SESSION['user_id']);
$username = $logged_in ? $_SESSION['username'] : '';
?>

<style>
    header {
        background: linear-gradient(90deg, #4e73df, #1cc88a);
        color: white;
        padding: 10px 20px 20px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    header .logo img { 
        height: 70px;
    }

    .welcome-top-right {
        position: absolute;
        top: 8px;
        right: 20px;
        color: #a0ffb3;
        background: rgba(0,0,0,0.35);
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        backdrop-filter: blur(3px);
        transition: 0.25s;
    }
    .welcome-top-right:hover {
        transform: scale(1.05);
    }

    nav {
        display: flex;
        gap: 20px;
        margin-left: auto;
        margin-top: 15px;
    }

    nav a {
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: 0.3s;
    }
    nav a:hover { color: #ffe600; }

    .btn {
        background-color: white;
        color: #007bff;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: 500;
    }
    .btn:hover { background-color: #f1f1f1; }

    #converter_popup {
        display: none;
        position: absolute;
        top: 75px;
        right: 20px;
        background: #fff;
        padding: 12px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.25);
        z-index: 99;
        min-width: 230px;
    }
    #converter_popup input,
    #converter_popup select,
    #converter_popup button {
        padding: 6px;
        margin-bottom: 6px;
        width: 100%;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    #converter_popup button {
        background: #4e73df;
        color: white;
        border: none;
        cursor: pointer;
    }
    #converter_popup button:hover {
        background: #1cc88a;
    }

    @media (max-width: 650px) {
        header { flex-direction: column; padding-bottom: 40px; }
        nav { flex-direction: column; width: 100%; margin-top: 10px; }
        .welcome-top-right { top: 5px; right: 10px; font-size: 13px; }
    }
</style>

<header>
    <div class="logo">
        <img src="images/chitieu.png" alt="Logo">
    </div>

    <?php if($logged_in): ?>
        <div class="welcome-top-right">Chào mừng, <?php echo htmlspecialchars($username); ?>!</div>
    <?php endif; ?>

    <nav>
        <?php if(!$logged_in): ?>
            <a href="index.php">Trang chủ</a>
        <?php endif; ?>

        <a href="dashboard.php">Dashboard</a>
        <a href="categories.php">Quản lý Danh mục</a>
        <a href="budget.php">Ngân sách</a>
        <a href="statistics.php">Thống kê</a>
        <a href="goals.php">Mục tiêu tài chính</a>
        <a href="javascript:void(0)" onclick="toggleConverter()">Chuyển đổi tiền</a>

        <?php if($logged_in): ?>
            <a href="profile.php">Thông tin cá nhân</a>
            <a href="actions/action_logout.php">Đăng xuất</a>
        <?php else: ?>
            <a class="btn" href="login.php">Đăng nhập</a>
            <a class="btn" href="register.php">Đăng ký</a>
            
        <?php endif; ?>
    </nav>

    <div id="converter_popup">
        <input id="amount_input" type="number" placeholder="Nhập số tiền">
        <select id="direction_select">
            <option value="vnd_to_usd">VND → USD</option>
            <option value="usd_to_vnd">USD → VND</option>
        </select>
        <button onclick="convertMoney()">Chuyển đổi</button>
        <div id="convert_result"></div>
    </div>
</header>

<script>
function toggleConverter() {
    const popup = document.getElementById("converter_popup");
    popup.style.display = (popup.style.display === "none") ? "block" : "none";
}

function convertMoney() {
    let amount = document.getElementById("amount_input").value;
    let direction = document.getElementById("direction_select").value;

    if(amount.trim() === "") {
        alert("Nhập số tiền!");
        return;
    }

    fetch("convert.php?amount=" + encodeURIComponent(amount) + "&direction=" + encodeURIComponent(direction))
    .then(res => res.json())
    .then(data => {
        if(!data.ok) {
            document.getElementById("convert_result").innerHTML = "Lỗi: " + data.error;
            return;
        }
        document.getElementById("convert_result").innerHTML =
            data.from + " = <span style='color:red;'>" + data.to + "</span>";
    })
    .catch(err => {
        document.getElementById("convert_result").innerHTML = "Lỗi kết nối!";
        console.error(err);
    });
}
</script>
