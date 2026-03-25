#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/test-application.sh

Purpose:
  Run the sandbox integration tests against MariaDB (PHP + DB started via Docker Compose).
EOF
  exit 0
fi

# Ensure the full sandbox stack is up.
docker compose up -d

# Install dependencies if needed.
docker compose exec -T php sh -lc 'if [ ! -x ./vendor/bin/simple-phpunit ]; then composer install; fi'

# Run HTTP + Doctrine sandbox tests against MariaDB.
docker compose exec -T php ./vendor/bin/simple-phpunit --testsuite application
