# PHP-FPM for Laravel (dev)
FROM php:8.3-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    bash git curl zip unzip libzip-dev oniguruma-dev icu-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    mariadb-connector-c-dev shadow

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql mbstring zip intl gd opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy dev php.ini
COPY ./docker/backend/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./docker/backend/fpm-overrides.conf /usr/local/etc/php-fpm.d/zz-overrides.conf

# Create non-root user for dev container
RUN useradd -u 1000 -ms /bin/bash appuser \
 && chown -R appuser:appuser /var/www/html

USER appuser

# Expose php-fpm port
EXPOSE 9000

CMD ["php-fpm"]
