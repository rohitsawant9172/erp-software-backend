#!/bin/sh
set -e

cd /var/www/html

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Clear any stale cached config from the build phase
php artisan config:clear  || true

# Cache config/routes/views
php artisan config:cache  || echo "config:cache skipped"
php artisan route:cache   || echo "route:cache skipped"
php artisan view:cache    || echo "view:cache skipped"

# Create storage symlink if not exists
php artisan storage:link  || echo "storage:link skipped"

# Run migrations (retry a few times in case DB isn't ready)
for i in 1 2 3 4 5; do
    php artisan migrate --force && break
    echo "DB not ready, retry $i/5 in 5s..."
    sleep 5
done

# Set Nginx to listen on $PORT (Railway/Render inject this)
PORT="${PORT:-8080}"
echo "Starting Nginx on port $PORT..."

# Alpine's sed requires -i '' or just -i (no backup extension needed)
sed -i "s/listen 80;/listen $PORT;/" /etc/nginx/http.d/default.conf

# Start supervisor (manages nginx + php-fpm)
exec /usr/bin/supervisord -c /etc/supervisord.conf
