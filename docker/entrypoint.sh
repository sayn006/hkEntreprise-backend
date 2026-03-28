#!/bin/sh
set -e

echo "=========================================="
echo "HK Entreprise Backend - Démarrage"
echo "=========================================="

# JWT keys depuis env vars base64
echo "[0/6] Configuration JWT..."
if [ -n "$JWT_PRIVATE_KEY_B64" ]; then
    mkdir -p /var/www/html/config/jwt
    echo "$JWT_PRIVATE_KEY_B64" | base64 -d > /var/www/html/config/jwt/private.pem
    echo "$JWT_PUBLIC_KEY_B64" | base64 -d > /var/www/html/config/jwt/public.pem
    chmod 600 /var/www/html/config/jwt/private.pem
    echo "✅ Clés JWT configurées"
fi

# Créer .env minimal si absent (Symfony le requiert même en prod)
if [ ! -f .env ]; then
    echo "APP_ENV=prod" > .env
    echo "APP_SECRET=${APP_SECRET:-$(cat /dev/urandom | tr -dc 'a-f0-9' | head -c 32)}" >> .env
fi

mkdir -p var/cache var/log public/uploads /var/log/php /var/log/supervisor /var/log/nginx
chmod -R 777 var 2>/dev/null || true

echo "[3/6] Réchauffement du cache Symfony..."
php bin/console cache:clear --env=prod --no-debug 2>/dev/null || true
php bin/console cache:warmup --env=prod --no-debug 2>/dev/null || true

echo "[4/6] Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 || echo "⚠️ Migrations ignorées"

echo "[5/6] Installation des assets..."
php bin/console assets:install public 2>/dev/null || true

echo "[6/6] Permissions finales..."
chown -R www-data:www-data var public/uploads /var/log/php 2>/dev/null || true
chmod -R 777 var 2>/dev/null || true
chmod -R 755 public/uploads 2>/dev/null || true

echo "=========================================="
echo "✅ HK Entreprise prêt - Démarrage"
echo "=========================================="

exec "$@"
