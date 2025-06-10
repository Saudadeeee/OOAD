<?php
session_start();

if (!isset($_SESSION['user']) && !isset($_SESSION['customer'])) {
    header('Location: login_selection.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: support_dashboard.php');
    exit();
}

$request_id = $_GET['id'];
$selected_request = null;
$customer_details = null;

$requests = json_decode(file_get_contents('data/requests.json'), true);
foreach ($requests as $req) {
    if ($req['maYeuCau'] === $request_id) {
        $selected_request = $req;
        break;
    }
}

if ($selected_request === null) {
    echo "Yêu cầu không tồn tại.";
    exit();
}

// Lấy thông tin khách hàng (logic cũ vẫn đúng)
if (!empty($selected_request['maKhachHang'])) {
    $customers = json_decode(file_get_contents('data/customers.json'), true);
    foreach ($customers as $customer) {
        if ($customer['maKhachHang'] === $selected_request['maKhachHang']) {
            $customer_details = $customer;
            break;
        }
    }
} else {
    $customer_details = $selected_request['thongTinKhachHang'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Yêu Cầu - <?php echo htmlspecialchars($selected_request['maYeuCau']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Bố cục chính */
        .detail-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        /* Thiết kế thẻ Card */
        .card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .card h3 {
            margin-top: 0;
            color: #0056b3;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .card dl {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 8px;
        }
        .card dt {
            font-weight: bold;
            color: #555;
        }
        .card dd {
            margin: 0;
            word-break: break-word;
        }

        /* Timeline cho trạng thái */
        .timeline { list-style: none; padding: 0; }
        .timeline-item { position: relative; padding-bottom: 20px; padding-left: 30px; border-left: 2px solid #e0e0e0; }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -11px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #e0e0e0;
        }
        .timeline-item.completed::before { background-color: #28a745; border: 3px solid #fff; box-shadow: 0 0 0 2px #28a745; }
        .timeline-item .time { font-size: 0.9em; color: #777; }
        
        /* Ghi chú và hành động */
        .notes-section { grid-column: 1 / -1; } /* Trải dài toàn bộ chiều rộng */
        .note { background-color: #f9f9f9; border-left: 4px solid #007bff; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .note .author { font-weight: bold; }
        .note .timestamp { font-size: 0.9em; color: #777; }
        .actions-form { margin-top: 20px; padding: 20px; background-color: #eef7ff; border-radius: 8px;}
    </style>
</head>
<body>
    <div class="container">
        <a href="<?php echo isset($_SESSION['user']) ? 'support_dashboard.php' : 'customer_history.php'; ?>">&larr; Quay lại danh sách</a>
        <h1>Chi Tiết Yêu Cầu: <?php echo htmlspecialchars($selected_request['maYeuCau']); ?></h1>

        <div class="detail-container">
            <div class="card">
                <h3>Thông tin Yêu cầu</h3>
                <dl>
                    <dt>Tiêu đề:</dt>
                    <dd><?php echo htmlspecialchars($selected_request['tenYeuCau']); ?></dd>
                    <dt>Thời gian gửi:</dt>
                    <dd><?php echo htmlspecialchars($selected_request['thoiGianGui']); ?></dd>
                    <dt>Nội dung:</dt>
                    <dd><?php echo nl2br(htmlspecialchars($selected_request['noiDung'])); ?></dd>
                </dl>
            </div>

            <div class="card">
                <h3>Thông tin Khách hàng</h3>
                <?php if ($customer_details): ?>
                    <dl>
                        <dt>Mã KH:</dt>
                        <dd><?php echo htmlspecialchars($customer_details['maKhachHang'] ?? 'Khách vãng lai'); ?></dd>
                        <dt>Họ và Tên:</dt>
                        <dd><?php echo htmlspecialchars($customer_details['ten']); ?></dd>
                        <dt>Công ty:</dt>
                        <dd><?php echo htmlspecialchars($customer_details['tencongty'] ?? 'Không có'); ?></dd>
                        <dt>Chức vụ:</dt>
                        <dd><?php echo htmlspecialchars($customer_details['chucvu'] ?? 'Không có'); ?></dd>
                       
                        <dt>Số điện thoại:</dt>
                        <dd><?php echo htmlspecialchars($customer_details['sdt']); ?></dd>
                        <dt>Địa chỉ:</dt>
                        <dd><?php echo htmlspecialchars($customer_details['diaChi'] ?: 'Không có'); ?></dd>
                    </dl>
                <?php else: ?>
                    <p>Không tìm thấy thông tin khách hàng.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Trạng thái Tiếp nhận</h3>
                <ul class="timeline">
                    <li class="timeline-item completed">
                        <strong>Yêu cầu đã được tạo</strong>
                        <div class="time"><?php echo htmlspecialchars($selected_request['thoiGianGui']); ?></div>
                    </li>
                    <?php if ($selected_request['trangThai'] === 'Đã tiếp nhận'): ?>
                        <li class="timeline-item completed">
                            <strong>Đã được tiếp nhận</strong>
                            <?php 
                                // Tìm thời gian tiếp nhận trong ghi chú
                                $reception_time = '';
                                foreach ($selected_request['ghiChu'] as $note) {
                                    if (strpos($note['content'], 'Trạng thái đã được cập nhật') !== false) {
                                        $reception_time = $note['timestamp'];
                                        break;
                                    }
                                }
                            ?>
                            <div class="time"><?php echo htmlspecialchars($reception_time); ?></div>
                        </li>
                    <?php else: ?>
                        <li class="timeline-item">
                            <strong>Chờ tiếp nhận</strong>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="card">
                <h3>Người tiếp nhận</h3>
                <dl>
                    <dt>Nhân viên:</dt>
                    <dd>
                        <?php 
                            if (!empty($selected_request['nguoiTiepNhan'])) {
                                echo htmlspecialchars($selected_request['nguoiTiepNhan']['fullname']);
                            } else {
                                echo "Chưa có";
                            }
                        ?>
                    </dd>
                </dl>
            </div>
            
            <?php if (isset($_SESSION['user'])): ?>
            <div class="card notes-section">
                <h3>Lịch sử Trao đổi & Ghi chú</h3>
                <?php if (empty($selected_request['ghiChu'])): ?>
                    <p>Chưa có ghi chú nào.</p>
                <?php else: ?>
                    <?php foreach ($selected_request['ghiChu'] as $note): ?>
                        <div class="note">
                            <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                            <p class="timestamp">
                                Bởi <span class="author"><?php echo htmlspecialchars($note['author']); ?></span>
                                lúc <?php echo htmlspecialchars($note['timestamp']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if(isset($_SESSION['user'])): ?>
            <div class="actions-form">
                <?php if ($selected_request['trangThai'] === 'Mới tạo'): ?>
                    <form action="update_request.php" method="POST" style="margin-bottom:20px;">
                        <input type="hidden" name="request_id" value="<?php echo $selected_request['maYeuCau']; ?>">
                        <input type="hidden" name="action" value="update_status">
                        <button type="submit">Đánh dấu "Đã tiếp nhận"</button>
                    </form>
                <?php endif; ?>
                
                <h3>Thêm Ghi chú mới</h3>
                <form action="update_request.php" method="POST">
                    <input type="hidden" name="request_id" value="<?php echo $selected_request['maYeuCau']; ?>">
                    <input type="hidden" name="action" value="add_note">
                    <label for="note_content">Nội dung ghi chú</label>
                    <textarea id="note_content" name="note_content" required></textarea>
                    <button type="submit">Thêm ghi chú</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>