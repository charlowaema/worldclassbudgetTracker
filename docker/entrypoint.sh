#!/bin/sh

set -e

echo "Starting Laravel application..."

cd /var/www

echo "Running database migrations..."

php artisan migrate --force

echo "Clearing Laravel caches..."

php artisan optimize:clear

echo "Creating storage link..."

php artisan storage:link || true

echo "Caching Laravel configuration..."

php artisan config:cache

echo "Caching Laravel routes..."

php artisan route:cache

echo "Caching Laravel views..."

php artisan view:cache

echo "Starting services..."

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf