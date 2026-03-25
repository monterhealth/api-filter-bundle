#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/dev-up.sh

Purpose:
  Start the full sandbox stack (PHP + MariaDB) and install dependencies.
EOF
  exit 0
fi

# Start the full sandbox stack (PHP + MariaDB).
docker compose up -d

# Install dependencies inside the long-running PHP container.
docker compose exec -T php composer install
