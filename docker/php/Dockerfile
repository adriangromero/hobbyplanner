# ===========================================
# BASE
# ===========================================
FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    git curl bash unzip \
    libzip-dev libpng-dev libxml2-dev \
    oniguruma-dev icu-dev

RUN docker-php-ext-install \
    pdo pdo_mysql mbstring zip intl opcache bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /srv/app

EXPOSE 9000

# ===========================================
# DEVELOPMENT
# ===========================================
FROM base AS development

RUN echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/dev.ini

CMD ["php-fpm"]

# ===========================================
# PRODUCTION
# ===========================================
FROM base AS production

RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/prod.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/prod.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/prod.ini

COPY --chown=www-data:www-data . /srv/app

RUN composer install --no-dev --optimize-autoloader

USER www-data

CMD ["php-fpm"]