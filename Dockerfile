# Sử dụng image chính thức của PHP phiên bản 8.2 cùng với máy chủ Apache
FROM php:8.2-apache

# Máy chủ Apache trong image này sẽ tìm các file web trong /var/www/html.
# Chúng ta sẽ ánh xạ thư mục dự án của mình vào đó bằng docker-compose.

# Bật module "rewrite" của Apache, rất hữu ích cho các dự án PHP trong tương lai
RUN a2enmod rewrite

# Ghi chú: Nếu trong tương lai cần cài thêm các extension cho PHP,
# bạn có thể thêm các lệnh như sau:
# RUN docker-php-ext-install pdo pdo_mysql