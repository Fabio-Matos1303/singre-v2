#!/usr/bin/env bash
set -euo pipefail
if [ $# -lt 1 ]; then
  echo "Usage: $0 <backup.sql>" >&2
  exit 1
fi
SQL_FILE="$1"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "$ROOT_DIR"

# Load env if exists
if [ -f .env ]; then
  set -a
  source .env
  set +a
fi

DB_NAME="${MYSQL_DATABASE:-singre}"
DB_USER="${MYSQL_USER:-singre}"
DB_PASS="${MYSQL_PASSWORD:-singre}"

echo "Restoring MySQL database '$DB_NAME' from '$SQL_FILE'..."
cat "$SQL_FILE" | docker compose exec -T mysql mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME"
echo "Restore completed."