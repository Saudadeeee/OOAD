<?php
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'backup_data':
            $backup_dir = 'backups';
            if (!file_exists($backup_dir)) {
                mkdir($backup_dir, 0755, true);
            }
            
            $backup_filename = 'backup_' . date('Y-m-d_H-i-s') . '.zip';
            $backup_path = $backup_dir . '/' . $backup_filename;
            
            $zip = new ZipArchive();
            if ($zip->open($backup_path, ZipArchive::CREATE) === TRUE) {
                $zip->addFile('data/users.json', 'users.json');
                $zip->addFile('data/customers.json', 'customers.json');
                $zip->addFile('data/requests.json', 'requests.json');
                $zip->close();
                
                $message = "Sao lưu dữ liệu thành công: $backup_filename";
                $message_type = "success";
            } else {
                $message = "Không thể tạo file sao lưu!";
                $message_type = "error";
            }
            break;
            
        case 'reset_demo_data':
            // Tạo dữ liệu demo
            $demo_requests = [
                [
                    "maYeuCau" => "YC" . time() . "001",
                    "tenYeuCau" => "Hỗ trợ cài đặt phần mềm",
                    "noiDungYeuCau" => "Cần hỗ trợ cài đặt phần mềm quản lý bệnh viện",
                    "thoiGianGui" => date('Y-m-d H:i:s'),
                    "trangThai" => "Mới tạo",
                    "thongTinKhachHang" => [
                        "ten" => "Nguyễn Văn Demo",
                        "sdt" => "0123456789",
                        "tencongty" => "Bệnh viện Demo",
                        "chucvu" => "IT Manager",
                        "diaChi" => "Hà Nội"
                    ],
                    "ghiChu" => []
                ]
            ];
            
            $demo_customers = [
                [
                    "maKhachHang" => "KH" . time(),
                    "username" => "demo_user",
                    "password_hash" => password_hash("demo123", PASSWORD_DEFAULT),
                    "ten" => "Khách Hàng Demo",
                    "tencongty" => "Công ty Demo",
                    "chucvu" => "Nhân viên",
                    "sdt" => "0987654321",
                    "diaChi" => "Hà Nội"
                ]
            ];
            
            file_put_contents('data/requests.json', json_encode($demo_requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            file_put_contents('data/customers.json', json_encode($demo_customers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $message = "Đã tạo lại dữ liệu demo thành công!";
            $message_type = "success";
            break;
            
        case 'clear_all_data':
            if ($_POST['confirm'] === 'DELETE_ALL') {
                file_put_contents('data/requests.json', json_encode([], JSON_PRETTY_PRINT));
                file_put_contents('data/customers.json', json_encode([], JSON_PRETTY_PRINT));
                
                $message = "Đã xóa toàn bộ dữ liệu khách hàng và yêu cầu!";
                $message_type = "success";
            } else {
                $message = "Xác nhận không chính xác. Không thực hiện xóa dữ liệu.";
                $message_type = "error";
            }
            break;
    }
}

// Đọc thông tin hệ thống
$requests_count = count(json_decode(file_get_contents('data/requests.json'), true));
$customers_count = count(json_decode(file_get_contents('data/customers.json'), true));
$users_count = count(json_decode(file_get_contents('data/users.json'), true));

// Lấy danh sách backup
$backup_files = [];
if (file_exists('backups')) {
    $backup_files = array_diff(scandir('backups'), ['.', '..']);
    rsort($backup_files); // Sắp xếp mới nhất lên đầu
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài Đặt Hệ Thống</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .settings-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .settings-card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .danger-zone {
            border-left: 5px solid #dc3545;
            background: rgba(220, 53, 69, 0.05);
        }
        .danger-zone h3 {
            color: #dc3545;
            border-bottom-color: #dc3545;
        }
        .system-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .info-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
        }
        .info-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .backup-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin: 15px 0;
        }
        .backup-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .backup-item:last-child {
            border-bottom: none;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .btn-warning {
            background: #ffc107;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        .confirm-input {
            margin: 15px 0;
        }
        .confirm-input input {
            border: 2px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1>⚙️ Cài Đặt Hệ Thống</h1>
            <a href="admin_dashboard.php">&larr; Quay lại Dashboard</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="settings-grid">
            <!-- Thông tin hệ thống -->
            <div class="settings-card">
                <h3>📊 Thông Tin Hệ Thống</h3>
                <div class="system-info">
                    <div class="info-item">
                        <div class="info-value"><?php echo $requests_count; ?></div>
                        <div class="info-label">Tổng yêu cầu</div>
                    </div>
                    <div class="info-item">
                        <div class="info-value"><?php echo $customers_count; ?></div>
                        <div class="info-label">Khách hàng</div>
                    </div>
                    <div class="info-item">
                        <div class="info-value"><?php echo $users_count; ?></div>
                        <div class="info-label">Nhân viên</div>
                    </div>
                    <div class="info-item">
                        <div class="info-value"><?php echo date('d/m/Y H:i'); ?></div>
                        <div class="info-label">Thời gian hiện tại</div>
                    </div>
                </div>
                <p><strong>Phiên bản:</strong> 1.0.0</p>
                <p><strong>Admin:</strong> <?php echo htmlspecialchars($_SESSION['user']['fullname']); ?></p>
            </div>

            <!-- Sao lưu & Khôi phục -->
            <div class="settings-card">
                <h3>💾 Sao Lưu & Khôi Phục</h3>
                <p>Tạo bản sao lưu dữ liệu để đảm bảo an toàn.</p>
                
                <form method="POST" style="margin: 20px 0;">
                    <input type="hidden" name="action" value="backup_data">
                    <button type="submit" class="btn">Tạo bản sao lưu ngay</button>
                </form>
                
                <h4>Các bản sao lưu gần đây:</h4>
                <div class="backup-list">
                    <?php if (empty($backup_files)): ?>
                        <p>Chưa có bản sao lưu nào.</p>
                    <?php else: ?>
                        <?php foreach (array_slice($backup_files, 0, 5) as $backup): ?>
                        <div class="backup-item">
                            <span><?php echo $backup; ?></span>
                            <a href="backups/<?php echo $backup; ?>" download class="btn-small btn-view">Tải về</a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quản lý dữ liệu -->
            <div class="settings-card">
                <h3>🔄 Quản Lý Dữ Liệu</h3>
                <p>Tạo dữ liệu demo hoặc làm mới hệ thống.</p>
                
                <form method="POST" style="margin: 20px 0;" onsubmit="return confirm('Bạn có chắc chắn muốn tạo lại dữ liệu demo? Điều này sẽ thay thế toàn bộ dữ liệu hiện tại.')">
                    <input type="hidden" name="action" value="reset_demo_data">
                    <button type="submit" class="btn-warning">Tạo dữ liệu demo</button>
                </form>
                
                <p><small><strong>Lưu ý:</strong> Dữ liệu demo sẽ thay thế toàn bộ dữ liệu yêu cầu và khách hàng hiện tại. Hãy sao lưu trước khi thực hiện.</small></p>
            </div>

            <!-- Vùng nguy hiểm -->
            <div class="settings-card danger-zone">
                <h3>⚠️ Vùng Nguy Hiểm</h3>
                <p><strong>Cảnh báo:</strong> Các hành động dưới đây có thể gây mất dữ liệu vĩnh viễn!</p>
                
                <h4>Xóa toàn bộ dữ liệu</h4>
                <p>Xóa tất cả yêu cầu và khách hàng. Dữ liệu nhân viên sẽ được giữ lại.</p>
                
                <form method="POST" onsubmit="return confirm('CẢNH BÁO: Bạn sắp xóa toàn bộ dữ liệu! Hành động này không thể hoàn tác. Bạn có chắc chắn?')">
                    <input type="hidden" name="action" value="clear_all_data">
                    <div class="confirm-input">
                        <label for="confirm">Nhập "DELETE_ALL" để xác nhận:</label>
                        <input type="text" id="confirm" name="confirm" required placeholder="DELETE_ALL">
                    </div>
                    <button type="submit" class="btn-danger">XÓA TOÀN BỘ DỮ LIỆU</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
