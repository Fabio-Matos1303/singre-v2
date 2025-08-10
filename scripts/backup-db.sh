#!/usr/bin/env bash
set -euo pipefail
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
BACKUP_DIR="${BACKUP_DIR:-backups}"
mkdir -p "$BACKUP_DIR"
STAMP="$(date +%Y%m%d%H%M%S)"
FILE="$BACKUP_DIR/db-${DB_NAME}-${STAMP}.sql"

echo "Dumping MySQL database '$DB_NAME' to '$FILE'..."
docker compose exec -T mysql mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$FILE"
echo "Backup completed: $FILE"