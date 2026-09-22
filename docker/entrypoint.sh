#!/bin/sh
set -e

# Render assigns the port at runtime via $PORT; Apache's config is static
# at build time, so wire it up here before starting.
PORT="${PORT:-80}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
