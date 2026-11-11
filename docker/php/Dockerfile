FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev libicu-dev libonig-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd intl mbstring fileinfo \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/bakehouse.ini
COPY docker/php/zz-bakehouse.conf /usr/local/etc/php-fpm.d/zz-bakehouse.conf

WORKDIR /var/www/html
