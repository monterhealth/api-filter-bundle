#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/test-unit.sh

Purpose:
  Run fast unit and service wiring tests only.
EOF
  exit 0
fi

# Run fast unit/service-level tests only.
docker compose run --rm php ./vendor/bin/simple-phpunit --testsuite unit
