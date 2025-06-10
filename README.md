# Hệ Thống Hỗ Trợ Khách Hàng Bệnh Viện

Hệ thống quản lý yêu cầu hỗ trợ khách hàng với giao diện web hiện đại, hỗ trợ đầy đủ chức năng cho khách hàng, nhân viên hỗ trợ và quản trị viên.

## 🚀 Tính Năng Chính

### Dành cho Khách Hàng
- ✅ Đăng ký tài khoản mới
- ✅ Đăng nhập an toàn với mật khẩu đã mã hóa
- ✅ Gửi yêu cầu hỗ trợ mới
- ✅ Xem lịch sử các yêu cầu đã gửi
- ✅ Theo dõi trạng thái xử lý yêu cầu

### Dành cho Nhân Viên Hỗ Trợ
- ✅ Dashboard hiển thị tất cả yêu cầu
- ✅ Tiếp nhận và xử lý yêu cầu
- ✅ Thêm ghi chú và trao đổi với khách hàng
- ✅ Cập nhật trạng thái yêu cầu

### Dành cho Admin (Quản Trị Viên)
- ✅ **Quản lý nhân viên**: Thêm, sửa, xóa tài khoản nhân viên
- ✅ **Quản lý khách hàng**: Xem danh sách và thông tin chi tiết khách hàng
- ✅ **Quản lý yêu cầu**: Xem, chỉnh sửa và xóa yêu cầu hỗ trợ
- ✅ **Báo cáo thống kê**: Biểu đồ và thống kê chi tiết
- ✅ **Xuất dữ liệu**: Xuất Excel/CSV cho tất cả dữ liệu
- ✅ **Sao lưu hệ thống**: Tạo và quản lý các bản sao lưu
- ✅ **Cài đặt hệ thống**: Quản lý dữ liệu và cấu hình

## 👥 Tài Khoản Mặc Định

### Admin
- **Tên đăng nhập**: `admin`
- **Mật khẩu**: `admin123`
- **Quyền**: Toàn quyền quản lý hệ thống

### Nhân Viên Hỗ Trợ
- **Tên đăng nhập**: `support1`
- **Mật khẩu**: `1`
- **Quyền**: Xử lý yêu cầu hỗ trợ

- **Tên đăng nhập**: `support2`
- **Mật khẩu**: `1`
- **Quyền**: Xử lý yêu cầu hỗ trợ

## 🛠 Cài Đặt và Chạy

### Sử dụng Docker (Khuyến nghị)
```bash
# Clone project
git clone <repository-url>
cd OOAD

# Chạy với Docker Compose
docker-compose up -d

# Truy cập ứng dụng
# Mở trình duyệt và vào: http://localhost:8080
```

### Chạy thủ công với XAMPP/WAMP
1. Copy toàn bộ project vào thư mục `htdocs` (XAMPP) hoặc `www` (WAMP)
2. Khởi động Apache server
3. Truy cập: `http://localhost/OOAD`

## 📁 Cấu Trúc Project

```
OOAD/
├── admin_*.php          # Các trang quản trị
├── customer_*.php       # Các trang khách hàng  
├── *.php               # Các trang chính
├── css/
│   └── style.css       # Styling với glassmorphism
├── data/
│   ├── users.json      # Dữ liệu nhân viên
│   ├── customers.json  # Dữ liệu khách hàng
│   └── requests.json   # Dữ liệu yêu cầu
├── includes/
│   └── classes.php     # Các class PHP
├── backups/            # Thư mục sao lưu
├── docker-compose.yml  # Cấu hình Docker
└── Dockerfile         # Docker image
```

## 🎯 Hướng Dẫn Sử Dụng Admin

### 1. Đăng Nhập Admin
- Truy cập trang web và chọn "Tôi là Nhân viên"
- Đăng nhập với tài khoản admin (xem phần tài khoản mặc định)

### 2. Quản Lý Nhân Viên (`admin_users.php`)
- **Thêm nhân viên mới**: Điền form và chọn vai trò
- **Sửa thông tin**: Click nút "Sửa" để cập nhật
- **Xóa nhân viên**: Click nút "Xóa" (không thể xóa chính mình)

### 3. Quản Lý Khách Hàng (`admin_customers.php`)
- **Xem danh sách**: Tất cả khách hàng đã đăng ký
- **Tìm kiếm**: Sử dụng thanh tìm kiếm
- **Xem chi tiết**: Click nút "Chi tiết"
- **Xóa khách hàng**: Sẽ xóa cả các yêu cầu liên quan

### 4. Quản Lý Yêu Cầu (`admin_requests.php`)
- **Xem tất cả yêu cầu**: Bao gồm cả đã xóa
- **Lọc theo trạng thái**: Dropdown filter
- **Cập nhật trạng thái**: Admin có thể thay đổi bất kỳ trạng thái nào
- **Xuất Excel**: Tải về danh sách yêu cầu

### 5. Báo Cáo Thống Kê (`admin_reports.php`)
- **Biểu đồ tròn**: Phân bố theo trạng thái
- **Biểu đồ đường**: Xu hướng theo tháng
- **Top khách hàng**: Khách hàng có nhiều yêu cầu nhất
- **Hiệu suất nhân viên**: Số yêu cầu đã xử lý
- **Xuất báo cáo**: CSV/Excel

### 6. Cài Đặt Hệ Thống (`admin_settings.php`)
- **Sao lưu dữ liệu**: Tạo file ZIP chứa tất cả dữ liệu
- **Tạo dữ liệu demo**: Reset về dữ liệu mẫu
- **Xóa toàn bộ dữ liệu**: Xóa hết (cần xác nhận)

## 🔒 Bảo Mật

- Mật khẩu khách hàng được mã hóa bằng `password_hash()`
- Session được bảo vệ và kiểm tra quyền truy cập
- Admin có quyền cao nhất, có thể override mọi hành động
- Xác nhận trước khi thực hiện các hành động nguy hiểm

## 📊 Tính Năng Xuất Dữ Liệu

Admin có thể xuất các loại báo cáo:
1. **Danh sách yêu cầu** - Tất cả thông tin yêu cầu
2. **Danh sách khách hàng** - Thông tin và số yêu cầu
3. **Danh sách nhân viên** - Thông tin và hiệu suất
4. **Báo cáo tổng hợp** - Thống kê chung

## 🎨 Giao Diện

- **Glassmorphism Design**: Hiện đại với hiệu ứng kính mờ
- **Responsive**: Tương thích với mọi thiết bị
- **Dark/Light elements**: Tối ưu trải nghiệm người dùng
- **Interactive animations**: Smooth transitions

## 🔧 Cấu Hình Mạng Local

Để cho phép người khác trong mạng local truy cập:

1. **Với Docker**:
   ```yaml
   # docker-compose.yml đã cấu hình
   ports:
     - "0.0.0.0:8080:80"  # Bind tất cả interfaces
   ```

2. **Tìm IP máy tính**:
   ```cmd
   ipconfig
   # Tìm IPv4 Address
   ```

3. **Mở Windows Firewall**:
   - Tạo Inbound Rule cho port 8080
   - Protocol: TCP
   - Action: Allow

4. **Truy cập từ máy khác**:
   ```
   http://[IP_ADDRESS]:8080
   # Ví dụ: http://192.168.1.100:8080
   ```

## 🐞 Debug & Logs

- Dữ liệu được lưu trong các file JSON
- Backup tự động khi có thay đổi quan trọng
- Error handling cho tất cả operations

## 📞 Hỗ Trợ

Liên hệ team phát triển nếu gặp vấn đề:
- Email: support@hospital-system.com
- Issues: Tạo issue trên repository

---
*Phát triển bởi Team OOAD - Đại học Bách Khoa Hà Nội 2024.2*