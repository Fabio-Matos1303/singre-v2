#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "$ROOT_DIR"

# Ensure .env exists (copy from example if missing)
if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
  echo ".env criado a partir de .env.example"
fi

docker compose up -d --build

echo "Services:"
echo "- Backend: http://localhost:8080"
echo "- Frontend: http://localhost:5173"
echo "- MySQL: localhost:3306 (user: singre / pass: singre)"
echo "- Redis: localhost:6379"
echo "- Mailpit UI: http://localhost:8025 (SMTP: localhost:1025)"
