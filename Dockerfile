FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

# 1. Copy seluruh source code project
COPY . /var/www/html

# 2. Buat folder database dan file database.sqlite kosong agar package:discover tidak error
RUN mkdir -p database && touch database/database.sqlite

# 3. Jalankan dump-autoload / composer install
RUN composer dump-autoload --optimize

# 4. Set permission folder wajib Laravel
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configuration Environment Variables
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_CLI 1
ENV REAL_IP_HEADER 1

EXPOSE 80