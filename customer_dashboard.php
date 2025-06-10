<?php
session_start();

if (!isset($_SESSION['customer'])) {
    header('Location: customer_login.php');
    exit();
}
$customer_name = $_SESSION['customer']['ten'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bảng điều khiển Khách hàng</title>
    <link rel="stylesheet" href="css/style.css">    <style>
        .dashboard-options { text-align: center; }
        .dashboard-options a {
            display: inline-block; 
            width: 350px; 
            padding: 30px 20px; 
            margin: 15px;
            font-size: 1.2em; 
            text-decoration: none; 
            background-color: #007bff;
            color: white; 
            border-radius: 8px; 
            transition: background-color 0.3s;
            box-sizing: border-box;
            text-align: center;
        }
        .dashboard-options a:hover { background-color: #0056b3; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="header-bar">
        <h1>Xin chào, <?php echo htmlspecialchars($customer_name); ?>!</h1>
        <a href="logout.php">Đăng xuất</a>
    </div>
    <p>Bạn muốn làm gì tiếp theo?</p>
    <div class="dashboard-options">
        <a href="index.php">Gửi Yêu cầu Hỗ trợ Mới</a>
        <a href="customer_history.php">Xem Lịch sử các Yêu cầu đã gửi</a>
    </div>
</div>
</body>
</html>