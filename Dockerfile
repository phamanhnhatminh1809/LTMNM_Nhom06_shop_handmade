FROM php:8.2-apache

# Cài đặt và kích hoạt extension pdo_mysql và mysqli để PHP kết nối được với MySQL
RUN docker-php-ext-install pdo_mysql mysqli