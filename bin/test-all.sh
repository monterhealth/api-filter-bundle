#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/test-all.sh

Purpose:
  Run unit + integration tests (with MariaDB available via Docker Compose).
EOF
  exit 0
fi

# Ensure the full sandbox stack is up.
docker compose up -d

# Install dependencies if needed.
docker compose exec -T php sh -lc 'if [ ! -x ./vendor/bin/simple-phpunit ]; then composer install; fi'

# Run all tests (unit + integration) in the PHP container.
docker compose exec -T php ./vendor/bin/simple-phpunit
