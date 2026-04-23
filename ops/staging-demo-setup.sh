#!/usr/bin/env sh
set -eu

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
ROOT_DIR="$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)"

cd "$ROOT_DIR"

echo "[staging] Avvio stack con rebuild..."
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  up -d --build

echo "[staging] Pulizia cache applicativa..."
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  exec -T app php artisan optimize:clear

echo "[staging] Eseguo migrazioni..."
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  exec -T app php artisan migrate --force

echo "[staging] Carico dati demo..."
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  exec -T app php artisan db:seed --force

echo "[staging] Stato servizi:"
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  ps

echo "[staging] Setup completato."
