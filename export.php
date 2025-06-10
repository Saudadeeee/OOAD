<?php
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}

if (!isset($_GET['type'])) {
    header('Location: admin_reports.php');
    exit();
}

$type = $_GET['type'];
$filename = '';
$csv_content = '';

switch ($type) {
    case 'requests':
        $filename = 'requests_export_' . date('Y-m-d') . '.csv';
        $requests = json_decode(file_get_contents('data/requests.json'), true);
        $customers = json_decode(file_get_contents('data/customers.json'), true);
        
        // Tạo map khách hàng
        $customer_map = [];
        foreach ($customers as $customer) {
            $customer_map[$customer['maKhachHang']] = $customer;
        }
        
        $csv_content = "Mã Yêu Cầu,Tiêu Đề,Khách Hàng,Email KH,SĐT KH,Công Ty,Thời Gian Gửi,Trạng Thái,Người Tiếp Nhận,Nội Dung\n";
        
        foreach ($requests as $req) {
            $customer_name = '';
            $customer_phone = '';
            $customer_company = '';
            
            if (isset($req['maKhachHang']) && isset($customer_map[$req['maKhachHang']])) {
                $customer = $customer_map[$req['maKhachHang']];
                $customer_name = $customer['ten'];
                $customer_phone = $customer['sdt'];
                $customer_company = $customer['tencongty'] ?? '';
            } elseif (isset($req['thongTinKhachHang'])) {
                $customer_name = $req['thongTinKhachHang']['ten'] ?? '';
                $customer_phone = $req['thongTinKhachHang']['sdt'] ?? '';
                $customer_company = $req['thongTinKhachHang']['tencongty'] ?? '';
            }
            
            $assigned_staff = '';
            if (isset($req['nguoiTiepNhan']['fullname'])) {
                $assigned_staff = $req['nguoiTiepNhan']['fullname'];
            }
            
            $csv_content .= '"' . str_replace('"', '""', $req['maYeuCau']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $req['tenYeuCau']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $customer_name) . '",';
            $csv_content .= '"' . str_replace('"', '""', '') . '",'; // Email (chưa có)
            $csv_content .= '"' . str_replace('"', '""', $customer_phone) . '",';
            $csv_content .= '"' . str_replace('"', '""', $customer_company) . '",';
            $csv_content .= '"' . str_replace('"', '""', $req['thoiGianGui']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $req['trangThai']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $assigned_staff) . '",';
            $csv_content .= '"' . str_replace('"', '""', $req['noiDungYeuCau']) . '"';
            $csv_content .= "\n";
        }
        break;
        
    case 'customers':
        $filename = 'customers_export_' . date('Y-m-d') . '.csv';
        $customers = json_decode(file_get_contents('data/customers.json'), true);
        $requests = json_decode(file_get_contents('data/requests.json'), true);
        
        // Đếm số yêu cầu của mỗi khách hàng
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
        
        $csv_content = "Mã Khách Hàng,Tên Đăng Nhập,Họ Tên,Công Ty,Chức Vụ,Số Điện Thoại,Địa Chỉ,Số Yêu Cầu\n";
        
        foreach ($customers as $customer) {
            $request_count = $customer_request_count[$customer['maKhachHang']] ?? 0;
            
            $csv_content .= '"' . str_replace('"', '""', $customer['maKhachHang']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $customer['username']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $customer['ten']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $customer['tencongty'] ?? '') . '",';
            $csv_content .= '"' . str_replace('"', '""', $customer['chucvu'] ?? '') . '",';
            $csv_content .= '"' . str_replace('"', '""', $customer['sdt']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $customer['diaChi'] ?? '') . '",';
            $csv_content .= $request_count;
            $csv_content .= "\n";
        }
        break;
        
    case 'users':
        $filename = 'users_export_' . date('Y-m-d') . '.csv';
        $users = json_decode(file_get_contents('data/users.json'), true);
        $requests = json_decode(file_get_contents('data/requests.json'), true);
        
        // Đếm số yêu cầu đã xử lý của mỗi nhân viên
        $staff_request_count = [];
        foreach ($requests as $request) {
            if (isset($request['nguoiTiepNhan']['username'])) {
                $username = $request['nguoiTiepNhan']['username'];
                if (!isset($staff_request_count[$username])) {
                    $staff_request_count[$username] = 0;
                }
                $staff_request_count[$username]++;
            }
        }
        
        $csv_content = "Tên Đăng Nhập,Họ Tên,Vai Trò,Số Yêu Cầu Đã Xử Lý\n";
        
        foreach ($users as $user) {
            $request_count = $staff_request_count[$user['username']] ?? 0;
            $role_name = $user['role'] === 'admin' ? 'Quản trị viên' : 'Nhân viên hỗ trợ';
            
            $csv_content .= '"' . str_replace('"', '""', $user['username']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $user['fullname']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $role_name) . '",';
            $csv_content .= $request_count;
            $csv_content .= "\n";
        }
        break;
        
    default:
        header('Location: admin_reports.php');
        exit();
}

// Gửi file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Thêm BOM để Excel hiển thị UTF-8 đúng
echo "\xEF\xBB\xBF";
echo $csv_content;
exit();
?>
