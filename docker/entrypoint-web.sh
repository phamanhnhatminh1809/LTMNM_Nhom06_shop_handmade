#!/bin/bash
set -e

# Đảm bảo các thư mục cần thiết của Laravel luôn tồn tại
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/cache \
         /var/www/html/bootstrap/cache

# Cấp quyền ghi đầy đủ cho tiến trình Apache www-data
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Thực thi lệnh Apache mặc định
exec apache2-foreground
