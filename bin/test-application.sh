#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/test-application.sh

Purpose:
  Start MariaDB (if needed) and run sandbox integration tests against MariaDB.
EOF
  exit 0
fi

# Ensure MariaDB is running for integration tests.
docker compose up -d mariadb
# Run HTTP + Doctrine sandbox tests against MariaDB.
docker compose run --rm php ./vendor/bin/simple-phpunit --testsuite application
