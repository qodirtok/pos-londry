#!/usr/bin/env bash
# pos-londry deploy script
# Usage: ./deploy.sh
set -euo pipefail

PROJECT_DIR="/root/www/pos-londry"
WEB_USER="www-data"
PHP_BIN="php8.2"
NGINX_SITE="/etc/nginx/sites-enabled/pos-londry"
LOG="storage/logs/laravel.log"

cd "$PROJECT_DIR"

echo "[1/8] Pull latest from origin/main ..."
git fetch --all
git reset --hard origin/main
git clean -fd

echo "[2/8] Composer install --no-dev --optimize-autoloader ..."
composer install --no-interaction --prefer-dist --optimize-autoloader
chown -R "$WEB_USER":"$WEB_USER" vendor composer

echo "[3/8] npm install & build ..."
npm ci
npm run build
chown -R "$WEB_USER":"$WEB_USER" node_modules dist public/build

echo "[4/8] Fix storage & bootstrap/cache permissions ..."
mkdir -p storage/framework/views \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/logs \
         bootstrap/cache
chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache
chmod -R 775 storage/framework/views storage/framework/cache storage/framework/sessions

echo "[5/8] Cache config & routes ..."
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan event:cache
chown -R "$WEB_USER":"$WEB_USER" bootstrap/cache

echo "[6/8] Run migrations (if any new) ..."
"$PHP_BIN" artisan migrate --force || echo "  (migrate skipped/failed — non-blocking)"

echo "[7/8] Clear & rotate old log ..."
if [ -f "$LOG" ]; then
  : > "$LOG"
  chown "$WEB_USER":"$WEB_USER" "$LOG"
fi
"$PHP_BIN" artisan optimize:clear >/dev/null

echo "[8/8] Reload PHP-FPM + nginx ..."
systemctl reload "php${PHP_BIN}-fpm" 2>/dev/null || service "php${PHP_BIN}-fpm" reload || true
nginx -t && nginx -s reload

echo ""
echo "=== DEPLOY OK ==="
echo "Project: $PROJECT_DIR"
echo "Check:   https://pos.azelsq.my.id"
echo "Log tail: tail -f $PROJECT_DIR/$LOG"
