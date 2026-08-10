FROM php:8.3-fpm

# ---------------------------------------------------------
# Install system dependencies
# ---------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------
# Install PHP extensions
# ---------------------------------------------------------
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip

# ---------------------------------------------------------
# Install Node.js 20
# ---------------------------------------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------
# Install Composer
# ---------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# ---------------------------------------------------------
# Copy application
# ---------------------------------------------------------
COPY . .

# ---------------------------------------------------------
# Install PHP dependencies
# ---------------------------------------------------------
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress \
    --no-scripts

# ---------------------------------------------------------
# Install frontend dependencies and build assets
# ---------------------------------------------------------
RUN npm ci \
    && npm run build \
    && rm -rf node_modules

# ---------------------------------------------------------
# Set Laravel permissions
# ---------------------------------------------------------
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache \
    && chmod -R 755 /var/www/public/build

# ---------------------------------------------------------
# Copy server configuration
# ---------------------------------------------------------
COPY docker/nginx.conf /etc/nginx/sites-available/default

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]