#!/bin/sh
set -e

echo "=========================================="
echo "HK Entreprise Backend - Démarrage"
echo "=========================================="

# JWT keys depuis env vars base64 (injectées par Coolify)
echo "[0/6] Configuration JWT..."
mkdir -p /var/www/html/config/jwt
if [ -n "$JWT_PRIVATE_KEY_B64" ]; then
    echo "$JWT_PRIVATE_KEY_B64" | base64 -d > /var/www/html/config/jwt/private.pem
    echo "$JWT_PUBLIC_KEY_B64" | base64 -d > /var/www/html/config/jwt/public.pem
    chown www-data:www-data /var/www/html/config/jwt/private.pem /var/www/html/config/jwt/public.pem
    chmod 640 /var/www/html/config/jwt/private.pem
    echo "✅ Clés JWT configurées"
else
    # Générer des clés si pas de B64 (fallback)
    APP_ENV=prod php bin/console lexik:jwt:generate-keypair --no-interaction 2>/dev/null || true
    chown www-data:www-data /var/www/html/config/jwt/*.pem 2>/dev/null || true
    chmod 640 /var/www/html/config/jwt/private.pem 2>/dev/null || true
fi

echo "[1/6] Création des répertoires..."
mkdir -p var/cache var/log public/uploads /var/log/php /var/log/supervisor /var/log/nginx

echo "[2/6] Permissions initiales..."
chmod -R 777 var

echo "[3/6] Réchauffement du cache Symfony..."
php bin/console cache:clear --env=prod --no-debug 2>/dev/null || true
php bin/console cache:warmup --env=prod --no-debug 2>/dev/null || true

echo "[4/6] Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 || echo "⚠️ Migrations ignorées"

echo "[5/6] Installation des assets..."
php bin/console assets:install public 2>/dev/null || true

echo "[6/6] Permissions finales..."
chown -R www-data:www-data var public/uploads /var/log/php
chmod -R 777 var
chmod -R 755 public/uploads

echo "=========================================="
echo "✅ HK Entreprise prêt - Démarrage"
echo "=========================================="

exec "$@"
