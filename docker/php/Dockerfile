FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

WORKDIR /var/www

# Rodar como root em DEV (resolve problema de volume Windows)
USER root

EXPOSE 9000
CMD ["php-fpm"]