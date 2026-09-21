FROM richarvey/nginx-php-fpm:latest

# Set working directory
WORKDIR /var/www/html

# 1. Copy file composer dulu agar memanfaatkan cache Docker saat build
COPY composer.json composer.lock /var/www/html/

# 2. Jalankan composer install
RUN composer install --no-dev --prefer-dist --no-scripts --no-autoloader

# 3. Copy seluruh file project
COPY . /var/www/html

# 4. Generate autoloader composer
RUN composer dump-autoload --optimize

# 5. Set permission folder wajib Laravel
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configuration Environment Variables
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_CLI 1
ENV REAL_IP_HEADER 1

EXPOSE 80