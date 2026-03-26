# Use PHP 8.4 FPM as base image
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    autoconf \
    curl \
    freetype-dev \
    g++ \
    git \
    icu-data-full \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxml2-dev \
    libzip-dev \
    make \
    nodejs \
    npm \
    oniguruma-dev \
    pkgconfig \
    sqlite-dev \
    unzip \
    wget \
    yt-dlp \
    zip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    ctype \
    gd \
    intl \
    mbstring \
    opcache \
    pdo \
    pdo_sqlite \
    xml \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY src/ .

# Copy custom PHP-FPM configuration
COPY custom-www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy startup script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Set permissions and ownership
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Switch to non-root user
USER www-data

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
ENTRYPOINT ["docker-entrypoint.sh"]
