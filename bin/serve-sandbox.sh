#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/serve-sandbox.sh

Purpose:
  Start a local browser-accessible sandbox for the bundle on http://127.0.0.1:18080

Notes:
  - Starts the full sandbox stack if needed (PHP + MariaDB).
  - Uses PHP's built-in server inside the Docker container.
  - Keep this command running while you browse.
  - When done, Ctrl+C stops only this server session; run ./bin/dev-down.sh
    (or `docker compose down`) to stop and remove the containers.
EOF
  exit 0
fi

# Start the full sandbox stack (PHP + MariaDB).
docker compose up -d

# Install dependencies if needed (so the bundle + Doctrine mapping autoload works).
docker compose exec -T php sh -lc 'if [ ! -f ./vendor/autoload.php ]; then composer install; fi'

# Runs in the foreground so you can Ctrl+C to stop the server.
docker compose exec -T php php -S 0.0.0.0:8080 -t tests/Application/public tests/Application/public/index.php

