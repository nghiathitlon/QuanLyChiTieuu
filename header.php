    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Kiểm tra user đã đăng nhập chưa
    $logged_in = isset($_SESSION['user_id']);
    $username = $logged_in ? $_SESSION['username'] : '';
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Quản lý Chi tiêu</title>
        <link rel="stylesheet" href="css/style.css">
        <style>
            header {
                background: linear-gradient(90deg, #4e73df, #1cc88a);
                color: white;
                padding: 10px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                position: relative;
            }
            header .logo img { height: 70px; margin-right: 10px; }
            nav { display: flex; align-items: center; gap: 20px; }
            nav a { color: white; text-decoration: none; font-weight: 500; transition: 0.3s; }
            nav a:hover { color: #ffe600; }
            .btn { background-color: white; color: #007bff; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-weight: 500; }
            .btn:hover { background-color: #f1f1f1; }

            /* Popup chuyển đổi tiền */
            #converter_popup {
                display: none;
                position: absolute;
                top: 80px;
                right: 20px;
                background: #fff;
                padding: 10px;
                border-radius: 5px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.2);
                z-index: 100;
                min-width: 220px;
            }
            #converter_popup input, #converter_popup select, #converter_popup button {
                padding: 5px;
                border-radius: 5px;
                border: 1px solid #ccc;
                margin-bottom: 5px;
            }
            #converter_popup button { background: #4e73df; color: white; border: none; cursor: pointer; width: 100%; }
            #converter_popup button:hover { background: #1cc88a; }

            #convert_result { font-weight: 500; margin-top:5px; color:#333; }

            @media (max-width: 600px) {
                header { flex-direction: column; align-items: flex-start; }
                nav { flex-direction: column; width: 100%; gap: 10px; margin-top: 10px; }
            }
        </style>
    </head>
    <body>
    <header>
        <div class="logo">
            <img src="images/chitieu.png" alt="Logo">
        </div>  

        <nav>
            <?php if(!$logged_in): ?><a href="index.php">Trang chủ</a><?php endif; ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="categories.php">Quản lý Danh mục</a>
            <a href="xem_lai_chi_tieu.php">Xem lại chi tiêu</a>
            <a href="budget.php">Ngân sách</a>
            <!-- Click để mở popup -->
            <a href="javascript:void(0)" onclick="toggleConverter()">Chuyển đổi tiền</a>

            <?php if($logged_in): ?>
                <a href="actions/action_logout.php">Đăng xuất</a>
            <?php else: ?>
                <a class="btn" href="login.php">Đăng nhập</a>
                <a class="btn" href="register.php">Đăng ký</a>
            <?php endif; ?>
        </nav>

        <!-- Popup chuyển đổi tiền -->
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
