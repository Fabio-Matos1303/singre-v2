#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

action="${1:-}"
case "$action" in
  up)
    docker compose up -d --build ;;
  down)
    docker compose down -v ;;
  logs)
    docker compose logs -f --tail=200 ;;
  ps)
    docker compose ps ;;
  rebuild)
    docker compose build --no-cache ;;
  *)
    echo "Usage: $0 {up|down|logs|ps|rebuild}" ; exit 1 ;;
 esac
