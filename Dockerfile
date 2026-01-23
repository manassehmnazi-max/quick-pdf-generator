# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install system dependencies needed for PHP extensions and composer
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    git \
    libsqlite3-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Set working directory to Apache web root
WORKDIR /var/www/html

# Copy all project files into container
COPY . /var/www/html/

# Copy Composer from the official composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Make Apache user own all files
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 for HTTP
EXPOSE 80

# Ensure Apache serves from api/ folder by default
RUN echo "DocumentRoot /var/www/html/api" >> /etc/apache2/sites-available/000-default.conf

# Optional: override Apache config to allow .htaccess rewrites
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
