FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

# 1. Copy seluruh source code project
COPY . /var/www/html

# 2. PAKSA Nginx menggunakan konfigurasi site.conf kustom untuk Laravel
RUN cp /var/www/html/conf/nginx/site.conf /etc/nginx/sites-available/default.conf

# 3. Install dependencies composer
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# 4. Buat file sqlite & set permission folder Laravel
RUN mkdir -p database && touch database/database.sqlite && chmod -R 777 database
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Environment Variables
ENV WEBROOT /var/www/html/public
ENV LARA_APP 1
ENV PHP_ERRORS_STDERR 1
ENV RUN_CLI 1
ENV REAL_IP_HEADER 1

EXPOSE 80