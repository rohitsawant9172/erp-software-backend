#!/bin/sh
set -e

cd /var/www/html

# Run post-install scripts (safe after .env is available via env vars)
composer run-script post-autoload-dump 2>/dev/null || true

# Cache config/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Start supervisor (nginx + php-fpm)
exec /usr/bin/supervisord -c /etc/supervisord.conf
