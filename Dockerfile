FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

# 1. Copy file composer terlebih dahulu agar memanfaatkan cache Docker (Build lebih cepat)
COPY composer.json composer.lock /var/www/html/

# 2. Install dependencies composer tanpa mengeksekusi script artisan saat build
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# 3. Copy seluruh source code project
COPY . /var/www/html

# 4. Buat folder & file sqlite serta beri hak akses tulis (write permission)
RUN mkdir -p database && touch database/database.sqlite && chmod -R 777 database

# 5. Set permission folder wajib Laravel
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Configuration Environment Variables
ENV WEBROOT /var/www/html/public
ENV LARA_APP 1
ENV PHP_ERRORS_STDERR 1
ENV RUN_CLI 1
ENV REAL_IP_HEADER 1

EXPOSE 80