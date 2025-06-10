<?php
session_start();

// Nếu đã đăng nhập rồi thì chuyển thẳng đến dashboard
if (isset($_SESSION['user'])) {
    header('Location: support_dashboard.php');
    exit();
}

$error_message = '';
 
// Xử lý khi người dùng gửi form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // Đọc file users.json
    $users_data = file_get_contents('data/users.json');
    $users = json_decode($users_data, true);

    $login_success = false;
    foreach ($users as $user) {
        // Kiểm tra thông tin đăng nhập
        if ($user['username'] === $username_input && $user['password'] === $password_input) {
            // Đăng nhập thành công, lưu thông tin vào session
            $_SESSION['user'] = $user;
            $login_success = true;
            break;
        }
    }    if ($login_success) {
        // Chuyển hướng dựa trên role
        if ($_SESSION['user']['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: support_dashboard.php');
        }
        exit();
    } else {
        // Thông báo lỗi
        $error_message = "Tên đăng nhập hoặc mật khẩu không chính xác!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Nhân Viên</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Đăng Nhập Dành Cho Nhân Viên</h1>

        <?php if (!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="username">Tên đăng nhập</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Đăng Nhập</button>
        </form>
        <p style="text-align: center; margin-top: 20px;">
            <a href="login_selection.php">Quay lại trang chọn vai trò</a>
        </p>
    </div>
</body>
</html>