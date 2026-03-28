# =============================================================================
# Dockerfile pour Kerala Restaurant Backend (Symfony 7.3)
# Déploiement sur Coolify
# =============================================================================
# Usage:
#   - Production (défaut) : pas de build args nécessaire
#   - Dev : ajouter BUILD_ENV=dev dans les Build Arguments de Coolify
# =============================================================================

# Argument global pour l'environnement
ARG BUILD_ENV=prod

# Stage 1: Composer dependencies (PHP 8.2 pour compatibilité)
FROM php:8.4-cli-alpine AS composer_stage

ARG BUILD_ENV
ENV BUILD_ENV=${BUILD_ENV}

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer les extensions nécessaires pour Composer
RUN apk add --no-cache git unzip

WORKDIR /app
COPY composer.json composer.lock ./

# Installer avec ou sans dev selon l'environnement
RUN if [ "$BUILD_ENV" = "dev" ]; then \
    echo "📦 Installation des dépendances DEV..." && \
    composer install --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs; \
    else \
    echo "📦 Installation des dépendances PROD (sans dev)..." && \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs; \
    fi

# Stage 2: Production image
FROM php:8.4-fpm-alpine

# Récupérer l'argument de build
ARG BUILD_ENV=prod

# Définir les variables d'environnement selon le mode
ENV APP_ENV=${BUILD_ENV}
ENV APP_DEBUG=0

# Extensions PHP requises pour Symfony + DomPDF + Firebase
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    gd \
    zip \
    intl \
    opcache \
    mbstring

# Configuration PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Configuration Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configuration Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Créer les répertoires nécessaires
RUN mkdir -p /var/log/php /var/log/supervisor /var/log/nginx \
    && chown -R www-data:www-data /var/log/php

WORKDIR /var/www/html

# Copier les dépendances Composer
COPY --from=composer_stage /app/vendor ./vendor

# Copier le code source
COPY . .

# Installer Composer pour dump-autoload
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Permissions, autoloader et assets
# Note: on utilise BUILD_ENV car APP_ENV n'est pas encore disponible au build
ARG BUILD_ENV=prod
RUN echo "🔧 Configuration pour l'environnement: ${BUILD_ENV}" \
    && composer dump-autoload --optimize --classmap-authoritative \
    && mkdir -p var/cache var/log public/uploads public/bundles var \
    && php bin/console assets:install public --env=${BUILD_ENV} --no-debug || echo "⚠️ Assets ignorés" \
    && php bin/console cache:warmup --env=${BUILD_ENV} --no-debug || echo "⚠️ Cache warmup ignoré" \
    && chown -R www-data:www-data var public/uploads public/bundles \
    && chmod -R 777 var \
    && chmod -R 755 public/uploads public/bundles

# Exposer le port 8080
EXPOSE 8080

# Script d'entrée
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
