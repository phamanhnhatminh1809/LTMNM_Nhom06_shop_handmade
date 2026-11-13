FROM php:8.2-cli

# Cài đặt các extension và công cụ hệ thống cần thiết cho Composer, PHP & Redis
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mysqli zip opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Cấu hình OPcache & realpath cache cho CLI để tối ưu tốc độ artisan serve và commands
RUN { \
        echo 'zend_extension=opcache'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.revalidate_freq=300'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'opcache.enable_file_override=1'; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && { \
        echo 'realpath_cache_size=8192K'; \
        echo 'realpath_cache_ttl=600'; \
    } > /usr/local/etc/php/conf.d/99-performance.ini

# Cài đặt Composer từ official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Thư mục làm việc mặc định trong container (chứa toàn bộ source Laravel)
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-interaction --prefer-dist --optimize-autoloader \
    && sha256sum composer.lock | cut -d' ' -f1 > vendor/.composer-lock.sha256
