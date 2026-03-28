#!/bin/sh
set -e

# JWT keys depuis env vars base64
if [ -n "$JWT_PRIVATE_KEY_B64" ]; then
    mkdir -p /var/www/html/config/jwt
    echo "$JWT_PRIVATE_KEY_B64" | base64 -d > /var/www/html/config/jwt/private.pem
    echo "$JWT_PUBLIC_KEY_B64" | base64 -d > /var/www/html/config/jwt/public.pem
    chmod 600 /var/www/html/config/jwt/private.pem
fi

# Dossiers uploads
mkdir -p /var/www/html/public/uploads/logos
mkdir -p /var/www/html/public/uploads/products
mkdir -p /var/www/html/public/uploads/categories
chmod -R 777 /var/www/html/public/uploads
chmod -R 777 /var/www/html/var

# Cache Symfony
php bin/console cache:warmup --env=prod 2>/dev/null || true

# Migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod 2>/dev/null || true

# Lancer supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf
