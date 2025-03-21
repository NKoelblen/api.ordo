FROM php:8.3-apache

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip unzip git libonig-dev \
    libxml2-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql opcache intl xml

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# Définir le répertoire de travail
WORKDIR /srv/app

# Copier les fichiers de configuration Apache
COPY ./vhost.conf /etc/apache2/sites-available/000-default.conf