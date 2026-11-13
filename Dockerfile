FROM php:8.2-apache

# Kích hoạt module rewrite của Apache cho routing / .htaccess
RUN a2enmod rewrite

# Cài đặt và kích hoạt extension pdo_mysql, mysqli và opcache
RUN docker-php-ext-install pdo_mysql mysqli opcache

# Cấu hình OPcache
RUN { \
        echo 'zend_extension=opcache'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.revalidate_freq=60'; \
        echo 'opcache.validate_timestamps=1'; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

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

# Script khởi động tự động kiểm tra và phân quyền storage cho www-data
COPY docker/entrypoint-web.sh /usr/local/bin/entrypoint-web.sh
RUN sed -i -e 's/\r$//' /usr/local/bin/entrypoint-web.sh && chmod +x /usr/local/bin/entrypoint-web.sh

ENTRYPOINT ["/usr/local/bin/entrypoint-web.sh"]
