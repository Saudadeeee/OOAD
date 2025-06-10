<?php
session_start();

if (!isset($_SESSION['customer'])) {
    header('Location: login_selection.php');
    exit();
}

$customer_data = $_SESSION['customer'];
$message_html = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_html = "<div class='message {$message['type']}'>{$message['text']}</div>";
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Hỗ trợ Khách hàng Bệnh viện</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        
        <div style="text-align:right; margin-bottom:15px;">
            Xin chào, <strong><?php echo htmlspecialchars($customer_data['ten']); ?></strong>! 
            <a href="customer_dashboard.php" style="margin-left:15px;">Bảng điều khiển</a>
            <a href="logout.php" style="margin-left:15px;">Đăng xuất</a>
        </div>

        <h1>Gửi Yêu Cầu Hỗ Trợ</h1>
        <p>Vui lòng điền đầy đủ thông tin vào biểu mẫu dưới đây. Chúng tôi sẽ liên hệ lại với bạn trong thời gian sớm nhất.</p>

        <?php echo $message_html; ?>
        
        <form action="submit_request.php" method="POST">
            <h2>Thông tin của bạn (Thông tin đã được điền tự động)</h2>
            
            <label for="ten">Họ và Tên (*)</label>
            <input type="text" id="ten" name="ten" required 
                   value="<?php echo htmlspecialchars($customer_data['ten']); ?>" readonly>

            <label for="tencongty">Tên Công ty</label>
            <input type="text" id="tencongty" name="tencongty" 
                   value="<?php echo htmlspecialchars($customer_data['tencongty'] ?? ''); ?>" readonly>

            <label for="chucvu">Chức vụ</label>
            <input type="text" id="chucvu" name="chucvu" 
                   value="<?php echo htmlspecialchars($customer_data['chucvu'] ?? ''); ?>" readonly>

            <label for="sdt">Số Điện Thoại (*)</label>
            <input type="tel" id="sdt" name="sdt" required 
                   value="<?php echo htmlspecialchars($customer_data['sdt']); ?>" readonly>

            <label for="diachi">Địa chỉ</label>
            <input type="text" id="diachi" name="diachi" 
                   value="<?php echo htmlspecialchars($customer_data['diaChi'] ?? ''); ?>" readonly>

            <input type="hidden" name="makh" value="<?php echo htmlspecialchars($customer_data['maKhachHang']); ?>">

            <h2>Nội dung yêu cầu</h2>
            <label for="ten_yeucau">Tiêu đề yêu cầu (*)</label>
            <input type="text" id="ten_yeucau" name="ten_yeucau" required>

            <label for="noidung_yeucau">Mô tả chi tiết (*)</label>
            <textarea id="noidung_yeucau" name="noidung_yeucau" required></textarea>

            <button type="submit">Gửi Yêu Cầu</button>
        </form>
    </div>
</body>
</html>