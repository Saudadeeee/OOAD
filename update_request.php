<?php
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo "Bạn không có quyền thực hiện hành động này.";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['request_id']) || !isset($_POST['action'])) {
    header('Location: support_dashboard.php');
    exit();
}
$request_id = $_POST['request_id'];
$action = $_POST['action'];
$file_path = 'data/requests.json';

$requests = json_decode(file_get_contents($file_path), true);

foreach ($requests as &$request) {
    if ($request['maYeuCau'] === $request_id) {
        $staff_info = $_SESSION['user'];
        
        switch ($action) {
            case 'update_status':
                if ($request['trangThai'] === 'Mới tạo') {
                    $request['trangThai'] = 'Đã tiếp nhận';
                    
                    // *** NÂNG CẤP QUAN TRỌNG ***
                    // Lưu lại toàn bộ thông tin nhân viên đã tiếp nhận
                    $request['nguoiTiepNhan'] = [
                        'username' => $staff_info['username'],
                        'fullname' => $staff_info['fullname']
                    ];
                    
                    $log_note = [
                        'content' => 'Trạng thái đã được cập nhật thành "Đã tiếp nhận".',
                        'author' => $staff_info['fullname'],
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    $request['ghiChu'][] = $log_note;
                }
                break;

            case 'add_note':
                $note_content = trim($_POST['note_content']);
                if (!empty($note_content)) {
                    $new_note = [
                        'content' => $note_content,
                        'author' => $staff_info['fullname'],
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    $request['ghiChu'][] = $new_note;
                }
                break;
        }
        
        break;
    }
}
unset($request); 

file_put_contents($file_path, json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header('Location: request_detail.php?id=' . $request_id);
exit();
?>