<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../db_connect.php';

$user_id = $_SESSION['user_id'];
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Lấy thông tin cũ của user
$query = $conn->query("SELECT avatar FROM users WHERE user_id = $user_id");
$user = $query->fetch_assoc();
$old_avatar = $user['avatar'] ?? '';

// Xử lý upload avatar
$avatar_name = '';
if (!empty($_FILES['avatar']['name'])) {
    $target_dir = "../uploads/";

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    //Cap nhat duong link anh co the them duoc
    $file_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff'];


    if (!in_array($file_ext, $allowed_types)) {
        header("Location: ../profile.php?status=error");
        exit;
    }

    if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
        header("Location: ../profile.php?status=error");
        exit;
    }

    $avatar_name = time() . '_' . preg_replace("/[^a-zA-Z0-9\.\-_]/", "", basename($_FILES['avatar']['name']));
    $target_file = $target_dir . $avatar_name;

    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
        echo "Upload thất bại!";
        echo "<pre>";
        print_r($_FILES);
        echo "</pre>";
        exit;
    }

    // Xóa avatar cũ nếu có
    if (!empty($old_avatar) && file_exists($target_dir . $old_avatar)) {
        unlink($target_dir . $old_avatar);
    }
}

// Cập nhật thông tin database
$sql = "UPDATE users SET username=?, email=?";
$params = [$username, $email];
$types = "ss";

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql .= ", password_hash=?";
    $params[] = $hashed_password;
    $types .= "s";
}


if (!empty($avatar_name)) {
    $sql .= ", avatar=?";
    $params[] = $avatar_name;
    $types .= "s";
}

$sql .= " WHERE user_id=?";
$params[] = $user_id;
$types .= "i";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ../profile.php?status=success");
        exit;
    } else {
        $stmt->close();
        header("Location: ../profile.php?status=error");
        exit;
    }
} else {
    header("Location: ../profile.php?status=error");
    exit;
}
?>
