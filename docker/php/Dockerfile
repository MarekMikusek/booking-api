FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    bash \
    curl \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql calendar intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./

RUN composer install --no-interaction --no-plugins --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
