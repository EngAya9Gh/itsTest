# Dockerfile - Laravel Backend
FROM php:8.2-fpm

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    curl \
    libsodium-dev \
    pkg-config \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy project files
COPY . .

# Install Laravel dependencies
RUN composer install --no-interaction --optimize-autoloader

# Generate app key (can be run later inside the container)
# RUN php artisan key:generate

CMD ["php-fpm"]
