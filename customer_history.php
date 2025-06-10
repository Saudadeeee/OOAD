<?php
session_start();

if (!isset($_SESSION['customer'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer']['maKhachHang'];
$my_requests = [];

$all_requests = json_decode(file_get_contents('data/requests.json'), true);

foreach ($all_requests as $request) {
   
    if ((isset($request['maKhachHang']) && $request['maKhachHang'] === $customer_id) ||
        (isset($request['thongTinKhachHang']['maKhachHang']) && $request['thongTinKhachHang']['maKhachHang'] === $customer_id))
    {
        $my_requests[] = $request;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử Yêu cầu</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
<div class="container">
    <a href="customer_dashboard.php">&larr; Quay lại Bảng điều khiển</a>
    <h1>Lịch sử Yêu cầu của bạn</h1>

    <table>
        <thead>
            <tr>
                <th>Mã Yêu Cầu</th>
                <th>Tiêu Đề</th>
                <th>Thời Gian Gửi</th>
                <th>Trạng Thái</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($my_requests)): ?>
                <tr><td colspan="4" style="text-align: center;">Bạn chưa có yêu cầu nào.</td></tr>
            <?php else: ?>
                <?php foreach (array_reverse($my_requests) as $req): ?>
                    <tr>
                        <td><a href="request_detail.php?id=<?php echo $req['maYeuCau']; ?>"><?php echo htmlspecialchars($req['maYeuCau']); ?></a></td>
                        <td><?php echo htmlspecialchars($req['tenYeuCau']); ?></td>
                        <td><?php echo htmlspecialchars($req['thoiGianGui']); ?></td>
                        <td><?php echo htmlspecialchars($req['trangThai']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>