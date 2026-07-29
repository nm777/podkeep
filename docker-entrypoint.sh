#!/bin/sh
set -e

echo "Fixing storage permissions..."
mkdir -p storage/app/public/temp-youtube \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    database

chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache database

if [ -f database/database.sqlite ]; then
    chown www-data:www-data database/database.sqlite
    chmod 664 database/database.sqlite
fi

if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
    su-exec www-data php artisan migrate --force
else
    echo "Skipping migrations (RUN_MIGRATIONS is not set; only the app service migrates)."
fi

echo "Clearing caches..."
su-exec www-data php artisan view:clear
su-exec www-data php artisan config:clear
su-exec www-data php artisan cache:clear

# PHP-FPM starts as root and drops to www-data through its pool configuration.
if [ "$1" = "php-fpm" ]; then
    exec "$@"
fi

exec su-exec www-data "$@"
