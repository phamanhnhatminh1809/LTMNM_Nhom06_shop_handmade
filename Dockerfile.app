FROM php:8.2-cli

# Cài đặt các extension và công cụ hệ thống cần thiết cho Composer và PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mysqli zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Cấu hình OPcache cho CLI để tối ưu tốc độ artisan serve và commands
RUN { \
        echo 'zend_extension=opcache'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.revalidate_freq=60'; \
        echo 'opcache.validate_timestamps=1'; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Cài đặt Composer từ official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thư mục làm việc mặc định trong container (chứa toàn bộ source Laravel)
WORKDIR /var/www/html
