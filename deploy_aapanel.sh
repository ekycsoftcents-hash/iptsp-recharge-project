#!/usr/bin/env bash
set -Eeuo pipefail

# Run as root on the aaPanel server. This script does not delete the existing site;
# it moves it to a timestamped backup before creating the Laravel application.

APP_DIR="/www/wwwroot/itopup.softcents.com"
REPO_URL="https://github.com/ekycsoftcents-hash/iptsp-recharge-project.git"
BACKUP_DIR="${APP_DIR}.backup.$(date +%Y%m%d_%H%M%S)"
TMP_DIR="/tmp/iptsp-recharge-project-$(date +%s)"

if [[ "$(id -u)" != "0" ]]; then
  echo "Run this script as root or with sudo."
  exit 1
fi

command -v git >/dev/null || { echo "git is required"; exit 1; }
command -v composer >/dev/null || { echo "composer is required"; exit 1; }
command -v php >/dev/null || { echo "php is required"; exit 1; }

php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' || {
  echo "PHP 8.2+ is required; detected: $(php -r 'echo PHP_VERSION;')"
  exit 1
}

mkdir -p "$(dirname "$APP_DIR")"
if [[ -d "$APP_DIR" ]]; then
  echo "Backing up existing directory to $BACKUP_DIR"
  mv "$APP_DIR" "$BACKUP_DIR"
fi

mkdir -p "$TMP_DIR"
git clone --depth=1 "$REPO_URL" "$TMP_DIR/repo"

# Create the missing full Laravel application scaffold.
composer create-project laravel/laravel "$APP_DIR" "^12.0" --no-interaction

# Merge the IPTSP starter code into the Laravel scaffold.
cp -a "$TMP_DIR/repo/app/." "$APP_DIR/app/"
cp -a "$TMP_DIR/repo/config/." "$APP_DIR/config/"
cp -a "$TMP_DIR/repo/database/migrations/." "$APP_DIR/database/migrations/"
cp -a "$TMP_DIR/repo/database/seeders/." "$APP_DIR/database/seeders/"
cp -a "$TMP_DIR/repo/resources/." "$APP_DIR/resources/"
cp -a "$TMP_DIR/repo/routes/." "$APP_DIR/routes/"
cp -a "$TMP_DIR/repo/docs" "$APP_DIR/"
cp -a "$TMP_DIR/repo/.env.example" "$APP_DIR/.env.example"
cp -a "$TMP_DIR/repo/README.md" "$APP_DIR/README-IPTSP.md"

cd "$APP_DIR"
composer install --no-dev --optimize-autoloader --no-interaction

if [[ ! -f .env ]]; then
  cp .env.example .env
fi
php artisan key:generate --force
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --force

chown -R www:www "$APP_DIR"
chmod -R ug+rwX storage bootstrap/cache

rm -rf "$TMP_DIR"
echo "Deployment base created at $APP_DIR"
echo "Next: edit $APP_DIR/.env, set aaPanel document root to $APP_DIR/public, then run php artisan migrate."
echo "Backup retained at $BACKUP_DIR"
