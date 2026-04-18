#!/bin/sh
set -e

cd /var/www/html

if [ -f composer.json ] && [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

if [ -f artisan ]; then
  php artisan config:clear >/dev/null 2>&1 || true
fi

exec "$@"
