FROM php:8.2-apache

# Kích hoạt module Apache phục vụ routing, nén gzip và HTTP cache headers
RUN a2enmod rewrite expires headers

# Cài đặt công cụ, thư viện hệ thống và các extension PHP tối ưu hiệu năng
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo_mysql mysqli zip gd opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Cấu hình OPcache & realpath cache giúp PHP bỏ qua stat() chậm qua mount
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

# Cài đặt Composer và dựng sẵn thư mục vendor bên trong image Linux (cho vendor_data volume)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-interaction --prefer-dist --optimize-autoloader \
    && sha256sum composer.lock | cut -d' ' -f1 > vendor/.composer-lock.sha256

# Cấu hình DocumentRoot chuẩn cho Laravel vào /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Cho phép .htaccess hoạt động đầy đủ trong thư mục public
RUN { \
        echo '<Directory /var/www/html/public>'; \
        echo '    Options Indexes FollowSymLinks'; \
        echo '    AllowOverride All'; \
        echo '    Require all granted'; \
        echo '</Directory>'; \
    } >> /etc/apache2/apache2.conf

# Script khởi động tự động kiểm tra vendor volume, tối ưu Laravel cache và phân quyền
COPY docker/entrypoint-web.sh /usr/local/bin/entrypoint-web.sh
RUN sed -i -e 's/\r$//' /usr/local/bin/entrypoint-web.sh && chmod +x /usr/local/bin/entrypoint-web.sh

ENTRYPOINT ["/usr/local/bin/entrypoint-web.sh"]
