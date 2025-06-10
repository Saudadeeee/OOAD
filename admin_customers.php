<?php
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

// Xử lý xóa khách hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $customer_id = $_POST['customer_id'];
    $customers_file = 'data/customers.json';
    $customers = json_decode(file_get_contents($customers_file), true);
    
    // Xóa khách hàng
    $customers = array_filter($customers, function($customer) use ($customer_id) {
        return $customer['maKhachHang'] !== $customer_id;
    });
    $customers = array_values($customers);
    
    // Cũng cần xóa hoặc cập nhật các yêu cầu liên quan
    $requests_file = 'data/requests.json';
    if (file_exists($requests_file)) {
        $requests = json_decode(file_get_contents($requests_file), true);
        $requests = array_filter($requests, function($request) use ($customer_id) {
            return !((isset($request['maKhachHang']) && $request['maKhachHang'] === $customer_id) ||
                    (isset($request['thongTinKhachHang']['maKhachHang']) && $request['thongTinKhachHang']['maKhachHang'] === $customer_id));
        });
        $requests = array_values($requests);
        file_put_contents($requests_file, json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    file_put_contents($customers_file, json_encode($customers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $message = "Xóa khách hàng và các yêu cầu liên quan thành công!";
    $message_type = "success";
}

// Đọc dữ liệu khách hàng
$customers = json_decode(file_get_contents('data/customers.json'), true);

// Đọc dữ liệu yêu cầu để đếm số yêu cầu của mỗi khách hàng
$requests = json_decode(file_get_contents('data/requests.json'), true);
$customer_request_count = [];
foreach ($requests as $request) {
    $customer_id = null;
    if (isset($request['maKhachHang'])) {
        $customer_id = $request['maKhachHang'];
    } elseif (isset($request['thongTinKhachHang']['maKhachHang'])) {
        $customer_id = $request['thongTinKhachHang']['maKhachHang'];
    }
    
    if ($customer_id) {
        if (!isset($customer_request_count[$customer_id])) {
            $customer_request_count[$customer_id] = 0;
        }
        $customer_request_count[$customer_id]++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khách Hàng</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .search-bar {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
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
        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .customer-name {
            font-weight: 600;
            color: #2c3e50;
        }
        .customer-company {
            font-size: 0.9em;
            color: #666;
        }
        .btn-small {
            padding: 8px 16px;
            font-size: 0.9rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 8px;
        }
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
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
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
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
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .info-value {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1>Quản lý Khách Hàng</h1>
            <a href="admin_dashboard.php">&larr; Quay lại Dashboard</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($customers); ?></div>
                <div>Tổng khách hàng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($customer_request_count); ?></div>
                <div>Khách hàng có yêu cầu</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo array_sum($customer_request_count); ?></div>
                <div>Tổng yêu cầu</div>
            </div>
        </div>

        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Tìm kiếm khách hàng theo tên, công ty, số điện thoại..." oninput="searchCustomers()">
        </div>

        <div class="card">
            <h2>Danh Sách Khách Hàng</h2>
            <table id="customersTable">
                <thead>
                    <tr>
                        <th>Mã KH</th>
                        <th>Thông tin khách hàng</th>
                        <th>Liên hệ</th>
                        <th>Tên đăng nhập</th>
                        <th>Số yêu cầu</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr class="customer-row">
                        <td><?php echo htmlspecialchars($customer['maKhachHang']); ?></td>
                        <td>
                            <div class="customer-info">
                                <div class="customer-name"><?php echo htmlspecialchars($customer['ten']); ?></div>
                                <?php if (!empty($customer['tencongty'])): ?>
                                    <div class="customer-company"><?php echo htmlspecialchars($customer['tencongty']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($customer['chucvu'])): ?>
                                    <div class="customer-company"><?php echo htmlspecialchars($customer['chucvu']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div><?php echo htmlspecialchars($customer['sdt']); ?></div>
                            <?php if (!empty($customer['diaChi'])): ?>
                                <div style="font-size: 0.9em; color: #666;"><?php echo htmlspecialchars($customer['diaChi']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($customer['username']); ?></td>
                        <td>
                            <span class="stat-number" style="font-size: 1.2em; color: #667eea;">
                                <?php echo isset($customer_request_count[$customer['maKhachHang']]) ? $customer_request_count[$customer['maKhachHang']] : 0; ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="viewCustomer('<?php echo $customer['maKhachHang']; ?>')" class="btn-small btn-view">Chi tiết</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khách hàng này? Điều này sẽ xóa tất cả yêu cầu liên quan.')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="customer_id" value="<?php echo $customer['maKhachHang']; ?>">
                                <button type="submit" class="btn-small btn-delete">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal chi tiết khách hàng -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Chi Tiết Khách Hàng</h2>
            <div id="customerDetails"></div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('customerModal');
        const span = document.getElementsByClassName('close')[0];
        const customers = <?php echo json_encode($customers); ?>;
        const customerRequestCount = <?php echo json_encode($customer_request_count); ?>;

        function viewCustomer(customerId) {
            const customer = customers.find(c => c.maKhachHang === customerId);
            if (customer) {
                const requestCount = customerRequestCount[customerId] || 0;
                document.getElementById('customerDetails').innerHTML = `
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Mã khách hàng:</div>
                            <div class="info-value">${customer.maKhachHang}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tên đăng nhập:</div>
                            <div class="info-value">${customer.username}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Họ và tên:</div>
                            <div class="info-value">${customer.ten}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Số điện thoại:</div>
                            <div class="info-value">${customer.sdt}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Công ty:</div>
                            <div class="info-value">${customer.tencongty || 'Không có'}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Chức vụ:</div>
                            <div class="info-value">${customer.chucvu || 'Không có'}</div>
                        </div>
                        <div class="info-item" style="grid-column: 1 / -1;">
                            <div class="info-label">Địa chỉ:</div>
                            <div class="info-value">${customer.diaChi || 'Không có'}</div>
                        </div>
                        <div class="info-item" style="grid-column: 1 / -1;">
                            <div class="info-label">Số yêu cầu đã gửi:</div>
                            <div class="info-value">${requestCount} yêu cầu</div>
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <a href="admin_requests.php?customer_id=${customerId}" class="btn">Xem tất cả yêu cầu của khách hàng này</a>
                    </div>
                `;
                modal.style.display = 'block';
            }
        }

        function searchCustomers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.customer-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
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