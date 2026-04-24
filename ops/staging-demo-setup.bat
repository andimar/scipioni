@echo off
setlocal

set "ROOT_DIR=%~dp0.."
cd /d "%ROOT_DIR%"

set "COMPOSE_FILES=-f docker-compose.yml -f docker-compose.staging.yml -f docker-compose.server.staging.yml -f docker-compose.edge.staging.yml"

echo [staging] Avvio stack con rebuild...
docker compose %COMPOSE_FILES% up -d --build
if errorlevel 1 goto :error

echo [staging] Verifico database applicativo...
docker compose %COMPOSE_FILES% exec -T mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS scipioni_club CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 goto :error

echo [staging] Riallino dipendenze PHP e package discovery...
docker compose %COMPOSE_FILES% exec -T app composer install --no-interaction --prefer-dist
if errorlevel 1 goto :error

docker compose %COMPOSE_FILES% exec -T app php artisan package:discover --ansi
if errorlevel 1 goto :error

echo [staging] Eseguo migrazioni...
docker compose %COMPOSE_FILES% exec -T app php artisan migrate --force
if errorlevel 1 goto :error

echo [staging] Carico dati demo...
docker compose %COMPOSE_FILES% exec -T app php artisan db:seed --force
if errorlevel 1 goto :error

echo [staging] Pulizia cache applicativa...
docker compose %COMPOSE_FILES% exec -T app php artisan optimize:clear
if errorlevel 1 goto :error

echo [staging] Stato servizi:
docker compose %COMPOSE_FILES% ps
if errorlevel 1 goto :error

echo [staging] Setup completato.
exit /b 0

:error
echo [staging] Setup fallito.
exit /b 1
