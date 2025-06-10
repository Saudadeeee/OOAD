<?php
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Đọc dữ liệu
$requests = json_decode(file_get_contents('data/requests.json'), true);
$customers = json_decode(file_get_contents('data/customers.json'), true);
$users = json_decode(file_get_contents('data/users.json'), true);

// Thống kê tổng quan
$total_requests = count($requests);
$total_customers = count($customers);
$total_users = count($users);

// Thống kê theo trạng thái
$status_stats = [];
foreach ($requests as $req) {
    $status = $req['trangThai'];
    if (!isset($status_stats[$status])) {
        $status_stats[$status] = 0;
    }
    $status_stats[$status]++;
}

// Thống kê theo tháng (12 tháng gần nhất)
$monthly_stats = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthly_stats[$month] = 0;
}

foreach ($requests as $req) {
    $month = date('Y-m', strtotime($req['thoiGianGui']));
    if (isset($monthly_stats[$month])) {
        $monthly_stats[$month]++;
    }
}

// Top khách hàng có nhiều yêu cầu nhất
$customer_request_count = [];
foreach ($requests as $req) {
    $customer_id = null;
    $customer_name = '';
    
    if (isset($req['maKhachHang'])) {
        $customer_id = $req['maKhachHang'];
        // Tìm tên khách hàng
        foreach ($customers as $customer) {
            if ($customer['maKhachHang'] === $customer_id) {
                $customer_name = $customer['ten'];
                break;
            }
        }
    } elseif (isset($req['thongTinKhachHang']['ten'])) {
        $customer_name = $req['thongTinKhachHang']['ten'];
        $customer_id = $req['thongTinKhachHang']['maKhachHang'] ?? $customer_name;
    }
    
    if ($customer_id) {
        if (!isset($customer_request_count[$customer_id])) {
            $customer_request_count[$customer_id] = [
                'name' => $customer_name,
                'count' => 0
            ];
        }
        $customer_request_count[$customer_id]['count']++;
    }
}

// Sắp xếp và lấy top 10
uasort($customer_request_count, function($a, $b) {
    return $b['count'] - $a['count'];
});
$top_customers = array_slice($customer_request_count, 0, 10, true);

// Thống kê nhân viên
$staff_stats = [];
foreach ($requests as $req) {
    if (isset($req['nguoiTiepNhan']['fullname'])) {
        $staff_name = $req['nguoiTiepNhan']['fullname'];
        if (!isset($staff_stats[$staff_name])) {
            $staff_stats[$staff_name] = 0;
        }
        $staff_stats[$staff_name]++;
    }
}
arsort($staff_stats);

// Tính tỷ lệ hoàn thành
$completed_requests = ($status_stats['Đã hoàn thành'] ?? 0) + ($status_stats['Đã đóng'] ?? 0);
$completion_rate = $total_requests > 0 ? round(($completed_requests / $total_requests) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Thống Kê</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .chart-container h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            text-align: center;
        }
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        .data-table {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .data-table h3 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin: 0;
            font-size: 1.2rem;
        }
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .data-table tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        .export-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            margin-bottom: 30px;
        }
        .export-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-export {
            padding: 12px 24px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(40, 167, 69, 0.4);
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            border-left: 5px solid #667eea;
        }
        .kpi-value {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }
        .kpi-label {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1>📊 Báo Cáo Thống Kê</h1>
            <a href="admin_dashboard.php">&larr; Quay lại Dashboard</a>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_requests; ?></div>
                <div class="stat-label">Tổng Yêu Cầu</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_customers; ?></div>
                <div class="stat-label">Khách Hàng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Nhân Viên</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $completion_rate; ?>%</div>
                <div class="stat-label">Tỷ Lệ Hoàn Thành</div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-value"><?php echo $status_stats['Mới tạo'] ?? 0; ?></div>
                <div class="kpi-label">Yêu cầu chờ xử lý</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?php echo $status_stats['Đã tiếp nhận'] ?? 0; ?></div>
                <div class="kpi-label">Đang được xử lý</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?php echo count($top_customers); ?></div>
                <div class="kpi-label">Khách hàng tích cực</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?php echo count($staff_stats); ?></div>
                <div class="kpi-label">Nhân viên đã hỗ trợ</div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="report-grid">
            <!-- Biểu đồ trạng thái -->
            <div class="chart-container">
                <h3>Phân Bố Theo Trạng Thái</h3>
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
            
            <!-- Biểu đồ theo tháng -->
            <div class="chart-container">
                <h3>Xu Hướng Theo Tháng</h3>
                <canvas id="monthlyChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Export Section -->
        <div class="export-section">
            <h3>📁 Xuất Báo Cáo</h3>
            <p>Tải về các báo cáo chi tiết dưới dạng file Excel hoặc CSV</p>            <div class="export-buttons">
                <button onclick="exportRequests()" class="btn-export">📋 Xuất Danh Sách Yêu Cầu</button>
                <button onclick="exportCustomers()" class="btn-export">👥 Xuất Danh Sách Khách Hàng</button>
                <button onclick="exportUsers()" class="btn-export">👨‍💼 Xuất Danh Sách Nhân Viên</button>
                <button onclick="exportSummary()" class="btn-export">📊 Xuất Báo Cáo Tổng Hợp</button>
            </div>
        </div>

        <!-- Bảng dữ liệu -->
        <div class="report-grid">
            <!-- Top khách hàng -->
            <div class="data-table">
                <h3>🏆 Top Khách Hàng</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Số yêu cầu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_customers as $customer_id => $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['name']); ?></td>
                            <td><strong><?php echo $data['count']; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Thống kê nhân viên -->
            <div class="data-table">
                <h3>👨‍💼 Hiệu Suất Nhân Viên</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Yêu cầu đã xử lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_stats as $staff_name => $count): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff_name); ?></td>
                            <td><strong><?php echo $count; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($staff_stats)): ?>
                        <tr>
                            <td colspan="2" style="text-align: center;">Chưa có dữ liệu</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Dữ liệu cho biểu đồ
        const statusData = <?php echo json_encode($status_stats); ?>;
        const monthlyData = <?php echo json_encode($monthly_stats); ?>;

        // Biểu đồ trạng thái (Pie Chart)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: [
                        '#ffc107',
                        '#28a745', 
                        '#17a2b8',
                        '#6c757d',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Biểu đồ theo tháng (Line Chart)
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: Object.keys(monthlyData),
                datasets: [{
                    label: 'Số yêu cầu',
                    data: Object.values(monthlyData),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });        // Hàm xuất dữ liệu
        function exportRequests() {
            window.location.href = 'export.php?type=requests';
        }

        function exportCustomers() {
            window.location.href = 'export.php?type=customers';
        }

        function exportUsers() {
            window.location.href = 'export.php?type=users';
        }

        function exportSummary() {
            // Tạo dữ liệu tổng hợp CSV
            let csv = 'Loại,Giá trị\n';
            csv += 'Tổng yêu cầu,<?php echo $total_requests; ?>\n';
            csv += 'Tổng khách hàng,<?php echo $total_customers; ?>\n';
            csv += 'Tổng nhân viên,<?php echo $total_users; ?>\n';
            csv += 'Tỷ lệ hoàn thành,<?php echo $completion_rate; ?>%\n';
            csv += '\nTrạng thái,Số lượng\n';
            <?php foreach ($status_stats as $status => $count): ?>
            csv += '<?php echo $status; ?>,<?php echo $count; ?>\n';
            <?php endforeach; ?>

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'summary_report.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
