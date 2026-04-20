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

# Auto-seed idempotent des données de base : formes juridiques, admin, entreprise par défaut.
# En prod, on utilise du SQL direct (le bundle fixtures n'est dispo qu'en dev/test).
echo "[5b/6] Seed idempotent données de base..."
php -r '
try {
    $url = getenv("DATABASE_URL");
    if (!$url) { echo "  - DATABASE_URL absent, seed skip\n"; exit; }
    $p = parse_url($url);
    $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4", $p["host"], $p["port"] ?? 3306, ltrim($p["path"], "/"));
    $pdo = new PDO($dsn, $p["user"], $p["pass"] ?? "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Formes juridiques
    $nbFj = (int) $pdo->query("SELECT COUNT(*) FROM forme_juridique")->fetchColumn();
    if ($nbFj === 0) {
        foreach (["SARL","SAS","EURL","SA","Auto-entrepreneur","SASU"] as $n) {
            $pdo->prepare("INSERT INTO forme_juridique (nom) VALUES (?)")->execute([$n]);
        }
        echo "  + 6 formes juridiques ajoutées\n";
    }

    // Admin par défaut
    $nbUsr = (int) $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
    if ($nbUsr === 0) {
        $hash = password_hash("admin123", PASSWORD_BCRYPT, ["cost" => 13]);
        $pdo->prepare("INSERT INTO user (username, email, nom, prenom, roles, password, menu_toggel, is_active, failed_login_attempts, created_at) VALUES (?,?,?,?,?,?,0,1,0,NOW())")
            ->execute(["admin@hk.fr", "admin@hk.fr", "Admin", "HK", json_encode(["ROLE_ADMIN"]), $hash]);
        echo "  + admin@hk.fr / admin123 créé\n";
    }

    // Entreprise par défaut
    $nbEnt = (int) $pdo->query("SELECT COUNT(*) FROM entreprise")->fetchColumn();
    if ($nbEnt === 0) {
        $pdo->exec("INSERT INTO entreprise (nom, forme_juridique, email, validite_offre, delai_execution, mode_reglement) VALUES (\"HK Entreprise\",\"SARL\",\"contact@hk-entreprise.fr\",\"30 jours\",\"À convenir\",\"30 jours fin de mois\")");
        echo "  + entreprise HK Entreprise créée\n";
    }
} catch (Throwable $e) {
    echo "  ⚠️ seed ignoré: " . $e->getMessage() . "\n";
}
' 2>&1 || true

echo "[6/6] Permissions finales..."
chown -R www-data:www-data var public/uploads /var/log/php
chmod -R 777 var
chmod -R 755 public/uploads

echo "=========================================="
echo "✅ HK Entreprise prêt - Démarrage"
echo "=========================================="

exec "$@"
