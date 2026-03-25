#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/test-unit.sh

Purpose:
  Run fast unit and service wiring tests only (with MariaDB available).
EOF
  exit 0
fi

# Ensure the full sandbox stack is up.
docker compose up -d

# Install dependencies if needed.
docker compose exec -T php sh -lc 'if [ ! -x ./vendor/bin/simple-phpunit ]; then composer install; fi'

# Run fast unit/service-level tests only.
docker compose exec -T php ./vendor/bin/simple-phpunit --testsuite unit
