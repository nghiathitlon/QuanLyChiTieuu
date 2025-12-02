<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Lấy thông tin người dùng
$query = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $query->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Thông tin cá nhân nâng cao</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    body {
        background: #f5f7fb;
        font-family: "Segoe UI", sans-serif;
    }

    .profile-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 25px 30px;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 5px 20px rgba(0,0,0,0.07);
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-container h2 {
        text-align: center;
        color: #1a73e8;
        margin-bottom: 20px;
    }

    /* Avatar */
    .profile-container img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e3f2fd;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }

    /* Label */
    .profile-container label {
        display: block;
        margin: 10px 0 4px;
        font-weight: 600;
        font-size: 14px;
        color: #444;
    }

    /* INPUT nhỏ gọn */
    .profile-container input[type="text"],
    .profile-container input[type="email"],
    .profile-container input[type="password"],
    .profile-container input[type="file"] {
        width: 100%;
        padding: 6px 10px;        /* nhỏ hơn */
        border: 1px solid #c9d3e0;
        border-radius: 5px;
        font-size: 13px;          /* chữ nhỏ hơn */
        background: #fafcff;
        transition: 0.2s;
    }

    .profile-container input:hover,
    .profile-container input:focus {
        border-color: #90caf9;
        background: #ffffff;
        box-shadow: 0 0 3px rgba(33,150,243,0.25);
        outline: none;
    }

    /* Button */
    .profile-container button {
        width: 100%;
        padding: 10px;
        background: #42a5f5;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        margin-top: 15px;
        transition: 0.2s;
        font-size: 14px;
    }

    .profile-container button:hover {
        background: #1e88e5;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(25,118,210,0.25);
    }

    .status-message {
        padding: 8px 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
    }
    .status-message.success {
        background: #e8f5e9;
        color: #2e7d32;
    }
    .status-message.error {
        background: #ffebee;
        color: #c62828;
    }
</style>

</head>
<body>
    <?php require 'header.php'; ?>
    <div class="profile-container">
        <h2>Thông tin cá nhân</h2>

        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo "<p class='status-message' style='color:green;'>Cập nhật thành công!</p>";
            } elseif ($_GET['status'] == 'error') {
                echo "<p class='status-message' style='color:red;'>Có lỗi xảy ra, vui lòng thử lại.</p>";
            }
        }
        ?>

        <form action="actions/action_update_profile.php" method="POST" enctype="multipart/form-data">
            <div style="text-align:center; margin-bottom:15px;">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                <?php else: ?>
                    <img src="uploads/default.png" alt="Avatar">
                <?php endif; ?>

            </div>

            <label>Thay đổi ảnh đại diện:</label>
            <input type="file" name="avatar" accept="image/*">

            <label>Họ và tên:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label>Mật khẩu mới (bỏ trống nếu không đổi):</label>
            <input type="password" name="password">

            <label>Vai trò:</label>
            <input type="text" value="<?php echo isset($user['role']) ? htmlspecialchars($user['role']) : ''; ?>" disabled>


            <button type="submit">Cập nhật</button>
        </form>
    </div>
</body>
</html>

<?php require 'footer.php'; ?>