#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "$ROOT_DIR"

docker compose exec -T backend bash -lc '
  set -euo pipefail
  cd /var/www/html
  php artisan make:controller Api/AuthController
  php artisan make:request Api/LoginRequest
  php artisan make:request Api/RegisterRequest
  php artisan make:test Api/AuthTest --unit
'

echo "Arquivos base criados. Edite o controller e requests conforme necessário."
