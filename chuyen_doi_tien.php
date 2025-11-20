<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra login
$logged_in = isset($_SESSION['user_id']);
if(!$logged_in){
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chuyển đổi tiền</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #4e73df; }
        .currency-converter { display: flex; gap:5px; margin-top: 10px; }
        .currency-converter input, .currency-converter select, .currency-converter button {
            padding: 5px; border-radius: 5px; border: 1px solid #ccc;
        }
        .currency-converter button {
            background: #4e73df; color:white; border:none; cursor:pointer;
        }
        #convert_result { margin-top:10px; font-weight:500; color:#333; }
    </style>
</head>
<body>
    <h1>Chuyển đổi tiền tệ</h1>

    <div class="currency-converter">
        <input id="amount_input" type="number" placeholder="Nhập số tiền">
        <select id="direction_select">
            <option value="vnd_to_usd">VND → USD</option>
            <option value="usd_to_vnd">USD → VND</option>
        </select>
        <button onclick="convertMoney()">Chuyển đổi</button>
    </div>

    <div id="convert_result"></div>

    <script>
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
                data.from + " = <span style='color:#ff0000;'>" + data.to + "</span>";
        })
        .catch(err => {
            document.getElementById("convert_result").innerHTML = "Lỗi kết nối!";
            console.error(err);
        });
    }
    </script>
</body>
</html>
