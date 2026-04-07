#!/bin/sh
set -e

# Fix storage permissions for YouTube temp directory
echo "Fixing storage permissions..."
mkdir -p /var/www/html/storage/app/public/temp-youtube
chown -R www-data:www-data /var/www/html/storage/app/public/temp-youtube
chmod -R 775 /var/www/html/storage/app/public/temp-youtube

# Run migrations
php artisan migrate --force

exec "$@"
