#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/serve-sandbox.sh

Purpose:
  Start a local browser-accessible sandbox for the bundle on http://127.0.0.1:18080

Notes:
  - Starts the full sandbox stack if needed (PHP + MariaDB).
  - Uses PHP's built-in server inside the Docker container (not PHPUnit).
  - Keep this command running while you browse.
  - The site root (/) shows Symfony's default welcome page; use the API routes:
      http://127.0.0.1:18080/books
      http://127.0.0.1:18080/authors
    Example filters:
      http://127.0.0.1:18080/books?title[partial]=Harry
      http://127.0.0.1:18080/books?author:name[partial]=Robert
  - When done, Ctrl+C stops only this server session; run ./bin/dev-down.sh
    (or `docker compose down`) to stop and remove the containers.
EOF
  exit 0
fi

echo "==> Monterhealth API Filter sandbox"
echo

echo "==> Starting Docker stack (PHP + MariaDB)..."
docker compose up -d
echo "    Containers are up."
echo

echo "==> Checking PHP dependencies..."
docker compose exec -T php sh -lc 'if [ ! -f ./vendor/autoload.php ]; then echo "    Running composer install..."; composer install; else echo "    Dependencies already installed."; fi'
echo

echo "==> Seeding database (schema + test data)..."
docker compose exec -T php php tests/Application/seed.php
echo "    Database ready."
echo

echo "==> Starting PHP built-in server (waits for HTTP requests; does not run tests)..."
echo "    The site root shows Symfony's welcome page — use the sandbox API instead:"
echo "      http://127.0.0.1:18080/books"
echo "      http://127.0.0.1:18080/authors"
echo "    Example filters:"
echo "      http://127.0.0.1:18080/books?title[partial]=Harry"
echo "      http://127.0.0.1:18080/books?author:name[partial]=Robert"
echo "    Press Ctrl+C to stop the server (containers keep running)."
echo "    Run ./bin/test-application.sh to run integration tests."
echo "    Run ./bin/dev-down.sh when you are done with the sandbox."
echo

# Runs in the foreground so you can Ctrl+C to stop the server.
docker compose exec -T php php -S 0.0.0.0:8080 -t tests/Application/public tests/Application/public/index.php

