FROM php:8.2-fpm-alpine

# System deps
RUN apk add --no-cache \
    nginx \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    unzip \
    git \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    supervisor

# PHP extensions that need explicit install
# (tokenizer, ctype, fileinfo, dom, xml are built-in to php:8.2-fpm-alpine)
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache \
    intl \
    zip

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-reqs

# Copy application
COPY . .

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Nginx config
RUN printf 'server {\n\
    listen 80;\n\
    root /var/www/html/public;\n\
    index index.php;\n\
    location / {\n\
    try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \\.php$ {\n\
    fastcgi_pass 127.0.0.1:9000;\n\
    fastcgi_index index.php;\n\
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
    include fastcgi_params;\n\
    }\n\
    }\n' > /etc/nginx/http.d/default.conf

# Supervisor config
RUN printf '[supervisord]\n\
    nodaemon=true\n\
    [program:nginx]\n\
    command=nginx -g "daemon off;"\n\
    autostart=true\n\
    autorestart=true\n\
    stderr_logfile=/dev/stderr\n\
    stderr_logfile_maxbytes=0\n\
    stdout_logfile=/dev/stdout\n\
    stdout_logfile_maxbytes=0\n\
    [program:php-fpm]\n\
    command=php-fpm\n\
    autostart=true\n\
    autorestart=true\n\
    stderr_logfile=/dev/stderr\n\
    stderr_logfile_maxbytes=0\n\
    stdout_logfile=/dev/stdout\n\
    stdout_logfile_maxbytes=0\n' > /etc/supervisord.conf

EXPOSE 80

COPY docker-start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start

CMD ["/usr/local/bin/start"]
