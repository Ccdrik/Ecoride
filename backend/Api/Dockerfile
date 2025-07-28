# Étape 1 : Installer Composer dans un conteneur temporaire
FROM composer:latest AS composer_stage

# Étape 2 : Image finale Symfony avec Apache
FROM php:8.2-apache

# Install dépendances PHP et OS
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libpq-dev libzip-dev libonig-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# Copier Composer depuis l'étape 1
COPY --from=composer_stage /usr/bin/composer /usr/bin/composer

# Dossier de travail Symfony
WORKDIR /var/www/html

# Copier les fichiers Symfony dans le conteneur
COPY . .

# Donne les bons droits
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Exposer le port Apache
EXPOSE 80
