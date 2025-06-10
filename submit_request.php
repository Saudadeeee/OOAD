<?php
session_start();
require_once 'includes/classes.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten = htmlspecialchars($_POST['ten']);

    $sdt = htmlspecialchars($_POST['sdt']);
    $diaChi = htmlspecialchars($_POST['diachi']);
    $tencongty = htmlspecialchars($_POST['tencongty'] ?? '');
    $chucvu = htmlspecialchars($_POST['chucvu'] ?? '');
   
    $tenYeuCau = htmlspecialchars($_POST['ten_yeucau']);
    $noiDungYeuCau = htmlspecialchars($_POST['noidung_yeucau']);

    // Lấy mã khách hàng từ session (nếu đã đăng nhập) hoặc từ form (nếu là khách vãng lai)
    $maKhachHang = $_POST['makh'] ?? null;
    
    // Kiểm tra tính hợp lệ
    if (empty($ten)|| empty($sdt) || empty($tenYeuCau) || empty($noiDungYeuCau)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Lỗi: Vui lòng điền đầy đủ các trường có dấu (*).'];
        header('Location: index.php');
        exit();
    }

    $khachHang = new KhachHang($maKhachHang, $tencongty, $ten, $chucvu,  $sdt, $diaChi);
    $yeuCau = new YeuCau($tenYeuCau, $noiDungYeuCau, $khachHang);
    
    // Nếu khách hàng đã đăng nhập, chúng ta chỉ cần lưu mã của họ trong yêu cầu
    // Điều này giúp chuẩn hóa dữ liệu
    if (isset($_SESSION['customer'])) {
        $yeuCau->maKhachHang = $_SESSION['customer']['maKhachHang'];
    }

    $filePath = 'data/requests.json';
    $current_data = file_get_contents($filePath);
    $array_data = json_decode($current_data, true);
    $array_data[] = $yeuCau;
    $final_data = json_encode($array_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if (file_put_contents($filePath, $final_data)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Yêu cầu của bạn đã được gửi thành công! Mã yêu cầu của bạn là: ' . $yeuCau->maYeuCau];
    } else {
         $_SESSION['message'] = ['type' => 'error', 'text' => 'Đã có lỗi xảy ra khi gửi yêu cầu. Vui lòng thử lại.'];
    }

    header('Location: customer_dashboard.php');
    exit();
}