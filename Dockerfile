# --- Stage 1: build frontend assets (Vite) ---
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# --- Stage 2: PHP application (Apache, multi-process — safe for real traffic) ---
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
        libpq-dev libzip-dev libpng-dev unzip git \
    && docker-php-ext-install pdo pdo_pgsql pgsql bcmath gd zip \
    && docker-php-ext-enable opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build

# Belt-and-braces: the blanket COPY above has, on this service, sometimes
# ended up without public/icons/* present in the built image (404s on
# every icon at runtime despite the files being committed and correct in
# git) — copying it again explicitly gives it its own cache layer keyed
# to just this directory's contents, so it can't silently go missing.
COPY public/icons ./public/icons

# Fail the build loudly here rather than shipping an image that silently
# 404s on every icon at runtime — this exact failure mode has happened on
# this service before and the above COPY alone wasn't enough to stop it
# recurring.
RUN test -f public/icons/icon-512.png \
    && test -f public/icons/icon-192.png \
    && test -f public/icons/apple-touch-icon.png \
    || (echo "ERROR: public/icons is missing from the build image — aborting build." && exit 1)

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Written as a literal path, not Apache's ${APACHE_DOCUMENT_ROOT} env-var
# syntax — that indirection was the actual cause of every icon (and any
# other file under public/) 404ing in production: mod_rewrite's own
# per-directory -f/-d file-existence checks (used by public/.htaccess to
# decide whether to hand a request to index.php) don't reliably resolve a
# DocumentRoot that's set via env-var interpolation, so it was treating
# real, readable files as "not found" and routing everything through
# Laravel, which 404s on any path it has no route for. A plain literal
# path removes that ambiguity entirely.
RUN sed -ri -e "s!/var/www/html!/var/www/html/public!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!/var/www/html/public/!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker/apache-laravel.conf /etc/apache2/conf-enabled/laravel.conf

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
