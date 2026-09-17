FROM php:8.2-cli

# Cài đặt các extension và công cụ hệ thống cần thiết cho Composer và PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mysqli zip \
    && rm -rf /var/lib/apt/lists/*

# Cài đặt Composer từ official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thư mục làm việc mặc định trong container (chứa toàn bộ source Laravel)
WORKDIR /var/www/html
