<?php
session_start();

// Bảo vệ trang: Nếu chưa đăng nhập, đá về trang login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Đọc dữ liệu từ file requests.json
$requests_json = file_get_contents('data/requests.json');
$requests = json_decode($requests_json, true);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển Hỗ Trợ</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; color: #0056b3; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['user']['fullname']); ?>!</h2>
            <a href="logout.php">Đăng xuất</a>
        </div>
        
        <h1>Danh sách Yêu cầu của Khách hàng</h1>

        <table>
            <thead>
                <tr>
                    <th>Mã Yêu Cầu</th>
                    <th>Tiêu Đề</th>
                    <th>Tên Khách Hàng</th>
                    <th>Thời Gian Gửi</th>
                    <th>Trạng Thái</th>
                    <?php if (isset($_SESSION['user']['vaiTro']) && $_SESSION['user']['vaiTro'] === 'nhanvien'): ?>
                        <th>Ghi chú</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="<?php echo (isset($_SESSION['user']['vaiTro']) && $_SESSION['user']['vaiTro'] === 'nhanvien') ? '6' : '5'; ?>" style="text-align: center;">Chưa có yêu cầu nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_reverse($requests) as $req): // array_reverse để hiển thị yêu cầu mới nhất lên đầu ?>
                        <tr>
                            <td><a href="request_detail.php?id=<?php echo $req['maYeuCau']; ?>"><?php echo htmlspecialchars($req['maYeuCau']); ?></a></td>
                            <td><?php echo htmlspecialchars($req['tenYeuCau']); ?></td>
                            <td><?php echo htmlspecialchars($req['thongTinKhachHang']['ten']); ?></td>
                            <td><?php echo htmlspecialchars($req['thoiGianGui']); ?></td>
                            <td><?php echo htmlspecialchars($req['trangThai']); ?></td>
                            <?php if (isset($_SESSION['user']['vaiTro']) && $_SESSION['user']['vaiTro'] === 'nhanvien'): ?>
                                <td><?php echo isset($req['ghiChu']) ? htmlspecialchars($req['ghiChu']) : ''; ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>