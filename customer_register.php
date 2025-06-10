<?php
session_start();
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $ten = trim($_POST['ten']);
    $tencongty = trim($_POST['tencongty']);
    $chucvu = trim($_POST['chucvu']);


    $sdt = trim($_POST['sdt']);
    $diaChi = trim($_POST['diachi']);
    if (empty($username) || empty($password) || empty($ten)  || empty($sdt)) {
        $errors[] = "Vui lòng điền đầy đủ các trường bắt buộc.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Mật khẩu xác nhận không khớp.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự.";
    }
    $file_path = 'data/customers.json';
    if (file_exists($file_path)) {
        $customers = json_decode(file_get_contents($file_path), true);
        foreach ($customers as $customer) {
            if ($customer['username'] === $username) {
                $errors[] = "Tên đăng nhập này đã tồn tại. Vui lòng chọn tên khác.";
                break;
            }
        }
    } else {
        $customers = []; 
    }

    if (empty($errors)) {
        $new_customer = [
            'maKhachHang' => 'KH' . time(),
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'ten' => $ten,
            'tencongty' => $tencongty,
            'chucvu' => $chucvu,
            'sdt' => $sdt,
            'diaChi' => $diaChi,
        ];

        $customers[] = $new_customer;
        file_put_contents($file_path, json_encode($customers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $_SESSION['register_success'] = 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.';
        header('Location: customer_login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký Tài Khoản Khách Hàng</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>Tạo Tài Khoản Khách Hàng Mới</h1>
    <p>Sử dụng tài khoản để quản lý các yêu cầu của bạn dễ dàng hơn.</p>

    <?php if (!empty($errors)): ?>
        <div class="message error">
            <?php foreach ($errors as $error): ?>
                <p style="margin: 0;"><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="customer_register.php" method="POST">
        <h2>Thông tin đăng nhập</h2>
        <label for="username">Tên đăng nhập (*)</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Mật khẩu (*, ít nhất 6 ký tự)</label>
        <input type="password" id="password" name="password" required>

        <label for="password_confirm">Xác nhận mật khẩu (*)</label>
        <input type="password" id="password_confirm" name="password_confirm" required>

        <h2>Thông tin cá nhân</h2>
        <label for="ten">Họ và Tên (*)</label>
        <input type="text" id="ten" name="ten" required>

        <label for="tencongty">Tên Công ty</label>
        <input type="text" id="tencongty" name="tencongty">

        <label for="chucvu">Chức vụ</label>
        <input type="text" id="chucvu" name="chucvu">

        <label for="sdt">Số Điện Thoại (*)</label>
        <input type="tel" id="sdt" name="sdt" required>

        <label for="diachi">Địa chỉ</label>
        <input type="text" id="diachi" name="diachi">

        <button type="submit">Đăng Ký</button>
    </form>
    <p style="text-align: center; margin-top: 20px;">
        Đã có tài khoản? <a href="customer_login.php">Đăng nhập ngay</a>.
    </p>
</div>
</body>
</html>