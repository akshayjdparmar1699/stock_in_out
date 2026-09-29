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

# Debian's Apache ships mod_alias enabled by default with a built-in
# `Alias /icons/ /usr/share/apache2/icons/` (used for mod_autoindex's
# directory-listing icons) — since this app also serves its own PWA
# icons from public/icons/, every request under that path was being
# silently redirected to that unrelated system directory instead of the
# app's own public/icons/, which is why they 404'd even though the
# files were correct and readable the whole time. Nothing here needs
# mod_alias (Laravel handles all its own routing/redirects), so it's
# simplest to just turn it off.
RUN apt-get update && apt-get install -y \
        libpq-dev libzip-dev libpng-dev unzip git \
    && docker-php-ext-install pdo pdo_pgsql pgsql bcmath gd zip \
    && docker-php-ext-enable opcache \
    && a2enmod rewrite \
    && a2dismod alias \
    && rm -rf /var/lib/apt/lists/*

COPY docker/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build

# A second, redundant `COPY public/icons ./public/icons` used to sit here
# as a "belt and braces" fix for icons 404ing at runtime — it's exactly
# backwards: public/images (copied only once, by the blanket COPY above)
# has always served correctly, while public/icons (copied a second time
# on its own layer here) was the one 404ing, confirmed by curling the
# live container's own loopback and getting Apache's own 404 for a file
# that demonstrably exists on disk with correct permissions. Two COPY
# instructions writing the same destination directory is the anomaly, not
# the blanket COPY missing it — so this now relies on that single COPY
# only, same as every other public/ subdirectory.

# Fail the build loudly if it's ever actually missing, rather than
# shipping an image that silently 404s on every icon at runtime.
RUN test -f public/icons/icon-512.png \
    && test -f public/icons/icon-192.png \
    && test -f public/icons/apple-touch-icon.png \
    || (echo "ERROR: public/icons is missing from the build image — aborting build." && exit 1)

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Written as a literal path rather than Apache's ${APACHE_DOCUMENT_ROOT}
# env-var syntax, to keep this unambiguous — not the icons 404 fix itself
# (that turned out to be the redundant COPY above), but no reason to
# leave the indirection in place either.
RUN sed -ri -e "s!/var/www/html!/var/www/html/public!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!/var/www/html/public/!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker/apache-laravel.conf /etc/apache2/conf-enabled/laravel.conf

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
