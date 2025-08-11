#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "$ROOT_DIR"

# Ensure .env root exists for compose variables
cp -n .env.example .env 2>/dev/null || true

# Create Laravel in a temp dir inside the backend container and move it into place
docker compose exec -T backend bash -lc '
  set -euo pipefail
  export COMPOSER_MEMORY_LIMIT=-1
  WORKDIR=/var/www/html
  if [ -f "$WORKDIR/artisan" ]; then
    echo "Laravel já está presente em $WORKDIR"; exit 0
  fi
  mkdir -p "$WORKDIR/.bootstrap" && cd "$WORKDIR/.bootstrap"
  composer create-project laravel/laravel app --no-interaction --prefer-dist
  shopt -s dotglob
  mv app/* "$WORKDIR"/
  rmdir app
  cd "$WORKDIR"
  rm -rf .bootstrap || true
'

# Create backend .env from example if missing and adjust DB/mail for compose
docker compose exec -T backend bash -lc '
  set -euo pipefail
  cd /var/www/html
  [ -f .env ] || cp .env.example .env
  sed -i "s/^APP_URL=.*/APP_URL=http:\/\/localhost:8080/" .env || true
  sed -i "s/^DB_HOST=.*/DB_HOST=mysql/" .env || true
  sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${MYSQL_DATABASE:-singre}/" .env || true
  sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${MYSQL_USER:-singre}/" .env || true
  sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${MYSQL_PASSWORD:-singre}/" .env || true
  sed -i "s/^CACHE_DRIVER=.*/CACHE_DRIVER=redis/" .env || true
  sed -i "s/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/" .env || true
  sed -i "s/^REDIS_HOST=.*/REDIS_HOST=redis/" .env || true
  sed -i "s/^MAIL_MAILER=.*/MAIL_MAILER=smtp/" .env || true
  sed -i "s/^MAIL_HOST=.*/MAIL_HOST=mailpit/" .env || true
  sed -i "s/^MAIL_PORT=.*/MAIL_PORT=1025/" .env || true
  php artisan key:generate --force
'

# Quick status
echo "Laravel bootstrapped. Próximos passos: scripts/backend-sanctum.sh"
