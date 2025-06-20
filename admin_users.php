<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $users_file = 'data/users.json';
    $users = json_decode(file_get_contents($users_file), true);
    
    switch ($action) {
        case 'add':
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $fullname = trim($_POST['fullname']);
            $role = $_POST['role'];
            $tuoi = (int)$_POST['tuoi'];
            $diaChi = trim($_POST['diaChi']);
            $vaiTro = trim($_POST['vaiTro']);
            
            $exists = false;
            foreach ($users as $user) {
                if ($user['username'] === $username) {
                    $exists = true;
                    break;
                }
            }
            
            if ($exists) {
                $message = "Tên đăng nhập đã tồn tại!";
                $message_type = "error";
            } elseif (empty($username) || empty($password) || empty($fullname) || empty($vaiTro)) {
                $message = "Vui lòng điền đầy đủ thông tin bắt buộc!";
                $message_type = "error";
            } elseif ($tuoi < 18 || $tuoi > 70) {
                $message = "Tuổi phải từ 18 đến 70!";
                $message_type = "error";
            } else {
                $new_user = [
                    'username' => $username,
                    'password' => $password,
                    'fullname' => $fullname,
                    'role' => $role,
                    'tuoi' => $tuoi,
                    'diaChi' => $diaChi,
                    'vaiTro' => $vaiTro
                ];
                $users[] = $new_user;
                file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $message = "Thêm nhân viên thành công!";
                $message_type = "success";
            }
            break;
            
        case 'delete':
            $username_to_delete = $_POST['username'];
            
            if ($username_to_delete === $_SESSION['user']['username']) {
                $message = "Không thể xóa tài khoản của chính mình!";
                $message_type = "error";
            } else {
                $users = array_filter($users, function($user) use ($username_to_delete) {
                    return $user['username'] !== $username_to_delete;
                });
                $users = array_values($users); 
                file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $message = "Xóa nhân viên thành công!";
                $message_type = "success";
            }
            break;
            
        case 'edit':
            $username = $_POST['username'];
            $new_password = trim($_POST['password']);
            $new_fullname = trim($_POST['fullname']);
            $new_role = $_POST['role'];
            $new_tuoi = (int)$_POST['tuoi'];
            $new_diaChi = trim($_POST['diaChi']);
            $new_vaiTro = trim($_POST['vaiTro']);
            
            if (empty($new_fullname) || empty($new_vaiTro)) {
                $message = "Vui lòng điền đầy đủ thông tin bắt buộc!";
                $message_type = "error";
            } elseif ($new_tuoi < 18 || $new_tuoi > 70) {
                $message = "Tuổi phải từ 18 đến 70!";
                $message_type = "error";
            } else {
                foreach ($users as &$user) {
                    if ($user['username'] === $username) {
                        if (!empty($new_password)) {
                            $user['password'] = $new_password;
                        }
                        $user['fullname'] = $new_fullname;
                        $user['role'] = $new_role;
                        $user['tuoi'] = $new_tuoi;
                        $user['diaChi'] = $new_diaChi;
                        $user['vaiTro'] = $new_vaiTro;
                        break;
                    }
                }
                file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $message = "Cập nhật thông tin thành công!";
                $message_type = "success";
            }
            break;
    }
}

$users = json_decode(file_get_contents('data/users.json'), true);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhân Viên</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-actions {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .form-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .btn-small {
            padding: 8px 16px;
            font-size: 0.9rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-edit {
            background: #28a745;
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
            margin: 15% auto;
            padding: 30px;
            border-radius: 16px;
            width: 80%;
            max-width: 500px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1>Quản lý Nhân Viên</h1>
            <a href="admin_dashboard.php">&larr; Quay lại Dashboard</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="admin-actions">
            <h2>Thêm Nhân Viên Mới</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <div>
                        <label for="username">Tên đăng nhập (*)</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div>
                        <label for="password">Mật khẩu (*)</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div>
                        <label for="fullname">Họ và tên (*)</label>
                        <input type="text" id="fullname" name="fullname" required>
                    </div>
                    <div>
                        <label for="role">Vai trò hệ thống (*)</label>
                        <select id="role" name="role" required>
                            <option value="support_staff">Nhân viên hỗ trợ</option>
                            <option value="admin">Quản trị viên</option>
                        </select>
                    </div>
                    <div>
                        <label for="tuoi">Tuổi (*)</label>
                        <input type="number" id="tuoi" name="tuoi" min="18" max="70" required>
                    </div>
                    <div>
                        <label for="diaChi">Địa chỉ</label>
                        <input type="text" id="diaChi" name="diaChi">
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label for="vaiTro">Mô tả vai trò (*)</label>
                        <input type="text" id="vaiTro" name="vaiTro" placeholder="VD: Nhân viên hỗ trợ khách hàng, Nhân viên hỗ trợ kỹ thuật..." required>
                    </div>
                </div>
                <button type="submit" class="btn">Thêm Nhân Viên</button>
            </form>
        </div>

        <div class="card">
            <h2>Danh Sách Nhân Viên</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tên đăng nhập</th>
                        <th>Thông tin cá nhân</th>
                        <th>Vai trò</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <div><strong><?php echo htmlspecialchars($user['fullname']); ?></strong></div>
                            <div style="font-size: 0.9em; color: #666;">
                                Tuổi: <?php echo $user['tuoi'] ?? 'N/A'; ?>
                                <?php if (!empty($user['diaChi'])): ?>
                                    | <?php echo htmlspecialchars($user['diaChi']); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div><?php echo $user['role'] === 'admin' ? 'Quản trị viên' : 'Nhân viên hỗ trợ'; ?></div>
                            <div style="font-size: 0.9em; color: #666;"><?php echo htmlspecialchars($user['vaiTro'] ?? ''); ?></div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="editUser('<?php echo $user['username']; ?>', '<?php echo htmlspecialchars($user['fullname']); ?>', '<?php echo $user['role']; ?>', <?php echo $user['tuoi'] ?? 25; ?>, '<?php echo htmlspecialchars($user['diaChi'] ?? ''); ?>', '<?php echo htmlspecialchars($user['vaiTro'] ?? ''); ?>')" class="btn-small btn-edit">Sửa</button>
                                <?php if ($user['username'] !== $_SESSION['user']['username']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="username" value="<?php echo $user['username']; ?>">
                                    <button type="submit" class="btn-small btn-delete">Xóa</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal sửa thông tin -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Sửa Thông Tin Nhân Viên</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_username" name="username">
                
                <label for="edit_fullname">Họ và tên (*)</label>
                <input type="text" id="edit_fullname" name="fullname" required>
                
                <label for="edit_password">Mật khẩu mới (để trống nếu không đổi)</label>
                <input type="password" id="edit_password" name="password">
                
                <label for="edit_role">Vai trò hệ thống (*)</label>
                <select id="edit_role" name="role" required>
                    <option value="support_staff">Nhân viên hỗ trợ</option>
                    <option value="admin">Quản trị viên</option>
                </select>
                
                <label for="edit_tuoi">Tuổi (*)</label>
                <input type="number" id="edit_tuoi" name="tuoi" min="18" max="70" required>
                
                <label for="edit_diaChi">Địa chỉ</label>
                <input type="text" id="edit_diaChi" name="diaChi">
                
                <label for="edit_vaiTro">Mô tả vai trò (*)</label>
                <input type="text" id="edit_vaiTro" name="vaiTro" required>
                
                <button type="submit" class="btn">Cập nhật</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('editModal');
        const span = document.getElementsByClassName('close')[0];

        function editUser(username, fullname, role, tuoi, diaChi, vaiTro) {
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_fullname').value = fullname;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_tuoi').value = tuoi;
            document.getElementById('edit_diaChi').value = diaChi;
            document.getElementById('edit_vaiTro').value = vaiTro;
            modal.style.display = 'block';
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