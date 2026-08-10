FROM php:8.3-fpm

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
        zip\
    unzip \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------
# PHP extensions
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
    && npm --version \
    && node --version \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------
# Install Composer
# ---------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------
# Application directory
# ---------------------------------------------------------
WORKDIR /var/www

COPY . .

# ---------------------------------------------------------
# Install PHP dependencies
# ---------------------------------------------------------
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress

# ---------------------------------------------------------
# Install Node dependencies and build Vite assets
# ---------------------------------------------------------
RUN npm ci \
    && npm run build \
    && rm -rf node_modules

# ---------------------------------------------------------
# Laravel storage/cache permissions
# ---------------------------------------------------------
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 storage \
    && chmod -R 775 bootstrap/cache

# ---------------------------------------------------------
# Nginx configuration
# ---------------------------------------------------------
COPY docker/nginx.conf /etc/nginx/sites-available/default

# ---------------------------------------------------------
# Supervisor configuration
# ---------------------------------------------------------
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ---------------------------------------------------------
# Entrypoint
# ---------------------------------------------------------
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

# Render expects the web service on port 80
EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]