#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/test-all.sh

Purpose:
  Start MariaDB (if needed) and run all tests (unit + integration).
EOF
  exit 0
fi

# Ensure MariaDB is up before executing the full suite.
docker compose up -d mariadb
# Run all tests (unit + integration) in the PHP container.
docker compose run --rm php ./vendor/bin/simple-phpunit
