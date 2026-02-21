#!/bin/sh
set -e

cd /var/www/html

# ─────────────────────────────────────────────────────────────
# 1. Write .env from environment variables (platform-injected)
#    .env is gitignored and NOT baked into the image, so we
#    generate it at runtime from the host environment.
# ─────────────────────────────────────────────────────────────
echo "Writing .env from environment variables..."
cat > /var/www/html/.env << EOF
APP_NAME=${APP_NAME:-Laravel}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=${LOG_LEVEL:-error}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-laravel}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}

SESSION_DRIVER=${SESSION_DRIVER:-database}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}

CACHE_STORE=${CACHE_STORE:-database}

MAIL_MAILER=${MAIL_MAILER:-log}

JWT_SECRET=${JWT_SECRET:-}
EOF

# ─────────────────────────────────────────────────────────────
# 2. Generate APP_KEY if not provided
# ─────────────────────────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not set — generating one now..."
    php artisan key:generate --force
else
    echo "APP_KEY already set, skipping generation."
fi

# ─────────────────────────────────────────────────────────────
# 3. Cache config/routes/views
# ─────────────────────────────────────────────────────────────
php artisan config:cache  || echo "config:cache skipped"
php artisan route:cache   || echo "route:cache skipped"
php artisan view:cache    || echo "view:cache skipped"

# ─────────────────────────────────────────────────────────────
# 4. Storage symlink
# ─────────────────────────────────────────────────────────────
php artisan storage:link --quiet || echo "storage:link skipped"

# ─────────────────────────────────────────────────────────────
# 5. Run migrations (retry a few times if DB isn't ready yet)
# ─────────────────────────────────────────────────────────────
for i in 1 2 3 4 5; do
    php artisan migrate --force && break
    echo "DB not ready, retry $i/5 in 5s..."
    sleep 5
done

# ─────────────────────────────────────────────────────────────
# 6. Configure Nginx port ($PORT injected by Railway/Render)
# ─────────────────────────────────────────────────────────────
PORT="${PORT:-8080}"
echo "Starting Nginx on port $PORT..."
sed -i "s/listen 80;/listen $PORT;/" /etc/nginx/http.d/default.conf

# ─────────────────────────────────────────────────────────────
# 7. Hand off to Supervisor (manages nginx + php-fpm)
# ─────────────────────────────────────────────────────────────
exec /usr/bin/supervisord -c /etc/supervisord.conf
