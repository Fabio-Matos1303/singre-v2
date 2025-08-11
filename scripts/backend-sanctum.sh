#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "$ROOT_DIR"

docker compose exec -T backend bash -lc '
  set -euo pipefail
  cd /var/www/html
  composer require laravel/sanctum
  php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
  php artisan migrate
'

echo "Sanctum instalado e migrations rodadas. Próximos passos: scripts/backend-auth-endpoints.sh"
