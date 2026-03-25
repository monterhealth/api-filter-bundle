#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  cat <<'EOF'
Usage: ./bin/dev-down.sh [options]

Purpose:
  Stop and remove the sandbox Docker stack (PHP + MariaDB).

  Use this when you are done with ./bin/serve-sandbox.sh or ./bin/dev-up.sh.
  Ctrl+C only stops the PHP built-in server in that terminal; containers keep
  running until you run this or `docker compose down`.

Options:
  -v, --volumes  Also remove named volumes (wipes MariaDB data for this project).
EOF
  exit 0
fi

down_args=()
if [[ "${1:-}" == "-v" || "${1:-}" == "--volumes" ]]; then
  down_args+=(--volumes)
fi

docker compose down "${down_args[@]}"
