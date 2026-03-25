#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/dev-up.sh

Purpose:
  Start MariaDB and install dependencies in the PHP container.
EOF
  exit 0
fi

# Start MariaDB once for local dev/test sessions.
docker compose up -d mariadb
# Install/update PHP dependencies inside the PHP container.
docker compose run --rm php composer install
