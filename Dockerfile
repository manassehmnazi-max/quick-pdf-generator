FROM php:8.2-apache

RUN a2enmod rewrite

# Step 1: update
RUN apt-get update

# Step 2: install system packages
RUN apt-get install -y \
    unzip \
    git \
    libsqlite3-dev

# Step 3: install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www/html

COPY . /var/www/html/

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
