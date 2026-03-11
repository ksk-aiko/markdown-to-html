FROM php:8.3-fpm-alpine

WORKDIR /var/www

RUN apk add --no-cache git unzip && docker-php-ext-install opcache

CMD ["php-fpm"]

