# docker/php/Dockerfile

# Étape 1 : Construction (Utilisation de l'image officielle PHP)
FROM php:8.2-fpm-alpine AS symfony_base

# Installation des extensions PHP nécessaires
RUN apk add --no-cache \
    postgresql-dev \
    git \
    libxml2-dev \
    icu-dev \
    zlib-dev \
    libsodium-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql \
    && docker-php-ext-install -j$(nproc) intl opcache bcmath \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    # Installation de l'extension sodium, essentielle pour JWT
    && docker-php-ext-install sodium

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail dans le conteneur
WORKDIR /srv/app

# Étape 2 : Production (Image finale légère)
FROM symfony_base AS app_php

# Copie des fichiers de configuration spécifiques à l'environnement
# Si vous avez un fichier INI pour les surcharges, ajoutez-le ici
# COPY docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/

# Copie du code source (sera géré par le volume dans compose.yaml)
# Le point d'entrée par défaut est php-fpm, 
CMD ["php-fpm"]