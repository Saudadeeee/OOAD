<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Vai Trò - Bệnh viện</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .role-selection {
            text-align: center;
        }
        .role-selection a {
            display: inline-block;
            width: 200px;
            padding: 40px 20px;
            margin: 20px;
            font-size: 1.5em;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        .role-selection a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Chào mừng đến với Hệ thống Hỗ trợ</h1>
        <p>Vui lòng chọn vai trò của bạn để tiếp tục.</p>
        <div class="role-selection">
            <a href="customer_login.php">Tôi là Khách hàng</a>
            <a href="login.php">Tôi là Nhân viên</a>
        </div>
    </div>
</body>
</html>