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

echo "[staging] Verifico database applicativo..."
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  exec -T mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS scipioni_club CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "[staging] Riallino dipendenze PHP e package discovery..."
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  exec -T app composer install --no-interaction --prefer-dist

docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  exec -T app php artisan package:discover --ansi

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

echo "[staging] Pulizia cache applicativa..."
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  exec -T app php artisan optimize:clear

echo "[staging] Stato servizi:"
docker compose \
  -f docker-compose.yml \
  -f docker-compose.staging.yml \
  -f docker-compose.server.staging.yml \
  -f docker-compose.edge.staging.yml \
  ps

echo "[staging] Setup completato."
