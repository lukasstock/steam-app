FROM php:8.2-apache

# Install dependencies and PHP extensions (intl, zip)
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zlib1g-dev nano \
    && docker-php-ext-install intl zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy your custom Apache config to set DocumentRoot and AllowOverride
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copy your Symfony app code
COPY . /var/www/html/

# Set ownership to www-data
RUN chown -R www-data:www-data /var/www/html

# Set working directory inside the container
WORKDIR /var/www/html

# Copy Composer from official image for dependency management if needed
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
