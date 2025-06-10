<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$requests = json_decode(file_get_contents('data/requests.json'), true);
$customers = json_decode(file_get_contents('data/customers.json'), true);
$users = json_decode(file_get_contents('data/users.json'), true);

$total_requests = count($requests);
$total_customers = count($customers);
$total_users = count($users);

$new_requests = 0;
$accepted_requests = 0;
foreach ($requests as $req) {
    if ($req['trangThai'] === 'Mới tạo') {
        $new_requests++;
    } elseif ($req['trangThai'] === 'Đã tiếp nhận') {
        $accepted_requests++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        }
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        .menu-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(102, 126, 234, 0.2);
        }
        .menu-card h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .menu-card p {
            margin-bottom: 20px;
            color: #666;
        }
        .menu-card a {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .menu-card a:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1>Bảng Điều Khiển Admin</h1>
            <div>
                <span>Xin chào, <strong><?php echo htmlspecialchars($_SESSION['user']['fullname']); ?></strong></span>
                <a href="logout.php" style="margin-left: 20px;">Đăng xuất</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_requests; ?></div>
                <div class="stat-label">Tổng Yêu Cầu</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $new_requests; ?></div>
                <div class="stat-label">Yêu Cầu Mới</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $accepted_requests; ?></div>
                <div class="stat-label">Đã Tiếp Nhận</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_customers; ?></div>
                <div class="stat-label">Khách Hàng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Nhân Viên</div>
            </div>
        </div>

        <div class="admin-menu">
            <div class="menu-card">
                <h3>🎫 Quản lý Yêu Cầu</h3>
                <p>Xem và quản lý tất cả các yêu cầu hỗ trợ từ khách hàng</p>
                <a href="admin_requests.php">Quản lý Yêu Cầu</a>
            </div>

            <div class="menu-card">
                <h3>👥 Quản lý Khách Hàng</h3>
                <p>Xem danh sách và thông tin chi tiết của tất cả khách hàng</p>
                <a href="admin_customers.php">Quản lý Khách Hàng</a>
            </div>

            <div class="menu-card">
                <h3>👨‍💼 Quản lý Nhân Viên</h3>
                <p>Tạo, chỉnh sửa và quản lý tài khoản nhân viên hỗ trợ</p>
                <a href="admin_users.php">Quản lý Nhân Viên</a>
            </div>            <div class="menu-card">
                <h3>📊 Báo Cáo Thống Kê</h3>
                <p>Xem báo cáo chi tiết về hoạt động của hệ thống</p>
                <a href="admin_reports.php">Xem Báo Cáo</a>
            </div>

            <div class="menu-card">
                <h3>⚙️ Cài Đặt Hệ Thống</h3>
                <p>Sao lưu dữ liệu, cấu hình và quản lý hệ thống</p>
                <a href="admin_settings.php">Cài Đặt</a>
            </div>
        </div>
    </div>
</body>
</html>