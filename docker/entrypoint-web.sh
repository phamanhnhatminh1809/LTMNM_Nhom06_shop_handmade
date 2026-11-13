#!/bin/bash
set -e

cd /var/www/html

# Đảm bảo các thư mục cần thiết của Laravel luôn tồn tại
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/cache \
         /var/www/html/bootstrap/cache \
         /tmp/laravel-cache \
         /tmp/laravel-views

# Cấp quyền ghi đầy đủ cho tiến trình Apache www-data
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /tmp/laravel-cache /tmp/laravel-views

# Đồng bộ named volume vendor_data nếu trống hoặc composer.lock có thay đổi
LOCK_HASH=""
if [ -f /var/www/html/composer.lock ]; then
    LOCK_HASH=$(sha256sum /var/www/html/composer.lock | cut -d' ' -f1)
fi

CURRENT_HASH=""
if [ -f /var/www/html/vendor/.composer-lock.sha256 ]; then
    CURRENT_HASH=$(cat /var/www/html/vendor/.composer-lock.sha256)
fi

if [ ! -f /var/www/html/vendor/autoload.php ] || [ "$LOCK_HASH" != "$CURRENT_HASH" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
    if [ -n "$LOCK_HASH" ]; then
        echo "$LOCK_HASH" > /var/www/html/vendor/.composer-lock.sha256
    fi
elif [ ! -f /var/www/html/vendor/.app-autoload-synced ]; then
    composer dump-autoload --optimize --no-interaction
    php artisan package:discover --ansi || true
    touch /var/www/html/vendor/.app-autoload-synced
fi

# Dựng cache config/route/view/event vào vùng Linux nội bộ (/tmp/laravel-*)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan event:cache || true

# Thực thi lệnh Apache mặc định
exec apache2-foreground
