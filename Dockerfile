# syntax=docker/dockerfile:1

############################
# 1) Front-end build (Vite)
############################
FROM node:22-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

##################################
# 2) PHP dependencies (Composer)
##################################
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

############################
# 3) Runtime image
############################
FROM php:8.4-cli-alpine AS app
WORKDIR /var/www/html

# Extensões comuns para Laravel
RUN apk add --no-cache \
      bash \
      icu-dev \
      libzip-dev \
      oniguruma-dev \
      sqlite-libs \
      sqlite-dev \
    && docker-php-ext-install \
      bcmath \
      intl \
      pdo \
      pdo_sqlite \
      zip

# Copia código da aplicação
COPY . .

# Copia vendor e assets gerados dos estágios anteriores
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Remove cache de bootstrap para evitar referências a pacotes dev no runtime
RUN rm -f bootstrap/cache/*.php

# Ajuste de permissões para storage/cache
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]


