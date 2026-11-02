FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS libonig-dev libxml2-dev libzip-dev unzip \
    && docker-php-ext-install dom xml xmlwriter mbstring zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

WORKDIR /app