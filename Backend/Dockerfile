FROM php:8.2-cli

# Installer les dépendances système + extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git unzip zip curl libpq-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql

# Installer Composer manuellement (plus stable que via image "composer")
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le dossier de travail
WORKDIR /app

# Copier uniquement composer.* d'abord pour profiter du cache Docker
COPY ./composer.json ./composer.lock /app/

# Installer les dépendances PHP
RUN composer install --no-interaction

# Puis copier le reste du projet Symfony
COPY . /app

# Port non exposé : Symfony CLI va écouter via le docker-compose
