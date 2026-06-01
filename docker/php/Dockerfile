FROM php:8.2-apache

RUN a2enmod rewrite \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html/Real-Estate-website-in-PHP-main

COPY . /var/www/html/Real-Estate-website-in-PHP-main

RUN composer install --no-interaction --no-dev --prefer-dist --working-dir=/var/www/html/Real-Estate-website-in-PHP-main

# Create upload directories with correct permissions
RUN mkdir -p /var/www/html/Real-Estate-website-in-PHP-main/admin/user \
             /var/www/html/Real-Estate-website-in-PHP-main/admin/property \
             /var/www/html/Real-Estate-website-in-PHP-main/admin/upload \
    && chown -R www-data:www-data /var/www/html/Real-Estate-website-in-PHP-main/admin \
    && chmod -R 775 /var/www/html/Real-Estate-website-in-PHP-main/admin