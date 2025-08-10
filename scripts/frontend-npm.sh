#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

docker compose exec -it frontend sh -lc "npm $*"