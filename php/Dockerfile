FROM php:8.3-fpm-alpine

WORKDIR /var/www

RUN apk add --no-cache git unzip && docker-php-ext-install opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["php-fpm"]

