FROM php:8.4-apache-bookworm

RUN docker-php-ext-install -j2 pdo_mysql \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY config/ config/
COPY src/ src/
COPY public/ public/
COPY docker/seed-demo.php docker/seed-demo.php

RUN mkdir -p uploads/pacientes \
    && chown -R www-data:www-data uploads
