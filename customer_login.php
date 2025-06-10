<?php
session_start();
if (isset($_SESSION['customer'])) {
    header('Location: index.php');
    exit();
}

$error_message = '';
$success_message = '';

if (isset($_SESSION['register_success'])) {
    $success_message = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    $customers = json_decode(file_get_contents('data/customers.json'), true);
    
    $login_success = false;
    foreach ($customers as $customer) {
        // So sánh username và dùng password_verify để kiểm tra mật khẩu đã băm
        if ($customer['username'] === $username_input && password_verify($password_input, $customer['password_hash'])) {
            // Lưu toàn bộ thông tin khách hàng vào session
            $_SESSION['customer'] = $customer;
            $login_success = true;
            break;
        }
    }

    if ($login_success) {
        header('Location: customer_dashboard.php');
        exit();
    } else {
        $error_message = "Tên đăng nhập hoặc mật khẩu không chính xác!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập Khách Hàng</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>Đăng Nhập Khách Hàng</h1>

    <?php if (!empty($success_message)): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="customer_login.php">
        <label for="username">Tên đăng nhập</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Đăng Nhập</button>
    </form>
    <p style="text-align: center; margin-top: 20px;">
        Chưa có tài khoản? <a href="customer_register.php">Tạo tài khoản mới</a>.
    </p>
     <p style="text-align: center; margin-top: 20px;">
        <a href="login_selection.php">Quay lại trang chọn vai trò</a>
    </p>
</div>
</body>
</html>