FROM php:8.3-fpm-alpine

# Dépendances système
RUN apk add --no-cache \
    nginx supervisor curl git unzip \
    icu-dev oniguruma-dev libzip-dev \
    freetype-dev libjpeg-turbo-dev libpng-dev \
    $PHPIZE_DEPS

# Extensions PHP (pré-compilées)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_mysql intl mbstring zip gd opcache

# Nettoyer
RUN apk del $PHPIZE_DEPS && rm -rf /tmp/* /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances PHP
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Code source
COPY . .

# Config
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html/var 2>/dev/null || true

EXPOSE 8080
CMD ["/entrypoint.sh"]
