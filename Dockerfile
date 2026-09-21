FROM richarvey/nginx-php-fpm:latest

COPY . /var/www/html

# Jalankan composer install agar folder vendor dibuat otomatis saat build
RUN composer install --no-dev --prefer-dist --optimize-autoloader

ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_CLI 1
ENV REAL_IP_HEADER 1

EXPOSE 80