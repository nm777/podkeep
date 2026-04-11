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

su-exec www-data php artisan migrate --force

# Run the main process (php-fpm starts as root, drops to www-data via pool config)
exec "$@"
