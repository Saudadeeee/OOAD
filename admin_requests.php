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
    $requests_file = 'data/requests.json';
    $requests = json_decode(file_get_contents($requests_file), true);
    
    switch ($action) {
        case 'delete':
            $request_id = $_POST['request_id'];
            $requests = array_filter($requests, function($request) use ($request_id) {
                return $request['maYeuCau'] !== $request_id;
            });
            $requests = array_values($requests);
            file_put_contents($requests_file, json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message = "Xóa yêu cầu thành công!";
            $message_type = "success";
            break;
            
        case 'update_status':
            $request_id = $_POST['request_id'];
            $new_status = $_POST['new_status'];
            
            foreach ($requests as &$request) {
                if ($request['maYeuCau'] === $request_id) {
                    $request['trangThai'] = $new_status;
                    
                    // Thêm ghi chú về việc admin thay đổi trạng thái
                    if (!isset($request['ghiChu'])) {
                        $request['ghiChu'] = [];
                    }
                    $request['ghiChu'][] = [
                        'content' => 'Admin đã cập nhật trạng thái thành "' . $new_status . '"',
                        'author' => $_SESSION['user']['fullname'],
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    break;
                }
            }
            file_put_contents($requests_file, json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message = "Cập nhật trạng thái thành công!";
            $message_type = "success";
            break;
    }
}

// Đọc dữ liệu
$requests = json_decode(file_get_contents('data/requests.json'), true);
$customers = json_decode(file_get_contents('data/customers.json'), true);

// Tạo map khách hàng để dễ tìm kiếm
$customer_map = [];
foreach ($customers as $customer) {
    $customer_map[$customer['maKhachHang']] = $customer;
}

// Lọc theo khách hàng nếu có
$filter_customer_id = $_GET['customer_id'] ?? '';
if (!empty($filter_customer_id)) {
    $requests = array_filter($requests, function($request) use ($filter_customer_id) {
        return (isset($request['maKhachHang']) && $request['maKhachHang'] === $filter_customer_id) ||
               (isset($request['thongTinKhachHang']['maKhachHang']) && $request['thongTinKhachHang']['maKhachHang'] === $filter_customer_id);
    });
}

// Thống kê
$status_count = [];
foreach ($requests as $req) {
    $status = $req['trangThai'];
    if (!isset($status_count[$status])) {
        $status_count[$status] = 0;
    }
    $status_count[$status]++;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Yêu Cầu</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filters-bar {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr 200px;
            gap: 15px;
            align-items: end;
        }
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }
        .status-new { background: #ffc107; color: #000; }
        .status-accepted { background: #28a745; color: white; }
        .status-completed { background: #17a2b8; color: white; }
        .status-closed { background: #6c757d; color: white; }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 0.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
            transition: all 0.3s ease;
        }
        .btn-view { background: #17a2b8; color: white; }
        .btn-edit { background: #28a745; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-small:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: black; }
        
        .customer-info {
            font-size: 0.9em;
            color: #666;
        }
        .request-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1>Quản lý Yêu Cầu</h1>
            <a href="admin_dashboard.php">&larr; Quay lại Dashboard</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($filter_customer_id)): ?>
            <div class="message info">
                Đang xem yêu cầu của khách hàng: <strong><?php echo htmlspecialchars($filter_customer_id); ?></strong>
                <a href="admin_requests.php" style="margin-left: 15px;">Xem tất cả yêu cầu</a>
            </div>
        <?php endif; ?>

        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($requests); ?></div>
                <div>Tổng yêu cầu</div>
            </div>
            <?php foreach ($status_count as $status => $count): ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $count; ?></div>
                <div><?php echo $status; ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="filters-bar">
            <div>
                <label>Tìm kiếm:</label>
                <input type="text" id="searchInput" placeholder="Tìm theo tiêu đề, nội dung, khách hàng..." oninput="searchRequests()">
            </div>
            <div>
                <label>Lọc theo trạng thái:</label>
                <select id="statusFilter" onchange="filterByStatus()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Mới tạo">Mới tạo</option>
                    <option value="Đã tiếp nhận">Đã tiếp nhận</option>
                    <option value="Đã hoàn thành">Đã hoàn thành</option>
                    <option value="Đã đóng">Đã đóng</option>
                </select>
            </div>
            <div>
                <button onclick="exportData()" class="btn">Xuất Excel</button>
            </div>
        </div>

        <div class="card">
            <h2>Danh Sách Yêu Cầu</h2>
            <table id="requestsTable">
                <thead>
                    <tr>
                        <th>Mã YC</th>
                        <th>Tiêu đề & Khách hàng</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th>Người tiếp nhận</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($requests) as $req): ?>
                    <tr class="request-row" data-status="<?php echo $req['trangThai']; ?>">
                        <td><?php echo htmlspecialchars($req['maYeuCau']); ?></td>
                        <td>
                            <div class="request-title"><?php echo htmlspecialchars($req['tenYeuCau']); ?></div>
                            <div class="customer-info">
                                <?php 
                                    $customer_name = '';
                                    if (isset($req['maKhachHang']) && isset($customer_map[$req['maKhachHang']])) {
                                        $customer_name = $customer_map[$req['maKhachHang']]['ten'];
                                    } elseif (isset($req['thongTinKhachHang']['ten'])) {
                                        $customer_name = $req['thongTinKhachHang']['ten'];
                                    }
                                    echo htmlspecialchars($customer_name);
                                ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($req['thoiGianGui']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $req['trangThai'])); ?>">
                                <?php echo htmlspecialchars($req['trangThai']); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                                if (!empty($req['nguoiTiepNhan'])) {
                                    echo htmlspecialchars($req['nguoiTiepNhan']['fullname']);
                                } else {
                                    echo "Chưa có";
                                }
                            ?>
                        </td>
                        <td>
                            <a href="request_detail.php?id=<?php echo $req['maYeuCau']; ?>" class="btn-small btn-view">Chi tiết</a>
                            <button onclick="editStatus('<?php echo $req['maYeuCau']; ?>', '<?php echo $req['trangThai']; ?>')" class="btn-small btn-edit">Sửa TT</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu này?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="request_id" value="<?php echo $req['maYeuCau']; ?>">
                                <button type="submit" class="btn-small btn-delete">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Cập Nhật Trạng Thái</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" id="edit_request_id" name="request_id">
                
                <label for="new_status">Trạng thái mới:</label>
                <select id="new_status" name="new_status" required>
                    <option value="Mới tạo">Mới tạo</option>
                    <option value="Đã tiếp nhận">Đã tiếp nhận</option>
                    <option value="Đang xử lý">Đang xử lý</option>
                    <option value="Đã hoàn thành">Đã hoàn thành</option>
                    <option value="Đã đóng">Đã đóng</option>
                </select>
                
                <button type="submit" class="btn">Cập nhật</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('statusModal');
        const span = document.getElementsByClassName('close')[0];

        function editStatus(requestId, currentStatus) {
            document.getElementById('edit_request_id').value = requestId;
            document.getElementById('new_status').value = currentStatus;
            modal.style.display = 'block';
        }

        function searchRequests() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.request-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function filterByStatus() {
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('.request-row');
            
            rows.forEach(row => {
                if (!statusFilter || row.dataset.status === statusFilter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function exportData() {
          
            let csv = 'Mã YC,Tiêu đề,Khách hàng,Thời gian,Trạng thái,Người tiếp nhận\n';
            const rows = document.querySelectorAll('.request-row');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const data = [
                    cells[0].textContent.trim(),
                    cells[1].querySelector('.request-title').textContent.trim(),
                    cells[1].querySelector('.customer-info').textContent.trim(),
                    cells[2].textContent.trim(),
                    cells[3].textContent.trim(),
                    cells[4].textContent.trim()
                ];
                csv += data.map(field => '"' + field.replace(/"/g, '""') + '"').join(',') + '\n';
            });
            
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'requests_export.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        span.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>