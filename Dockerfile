FROM php:8.3-fpm

# --------------------------------------------------
# System dependencies
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# Install Node.js 20
# --------------------------------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# Install Composer
# --------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# Application directory
# --------------------------------------------------
WORKDIR /var/www

# Copy Composer files first
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize

# --------------------------------------------------
# Install Node dependencies and build frontend
# --------------------------------------------------
RUN npm ci \
    && npm run build \
    && rm -rf node_modules

# --------------------------------------------------
# Laravel permissions
# --------------------------------------------------
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache \
    && chmod -R 755 /var/www/public/build

# --------------------------------------------------
# Nginx
# --------------------------------------------------
COPY docker/nginx.conf /etc/nginx/sites-available/default

# --------------------------------------------------
# Supervisor
# --------------------------------------------------
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# --------------------------------------------------
# Entrypoint
# --------------------------------------------------
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]