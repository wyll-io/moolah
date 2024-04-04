FROM composer:latest AS composer
FROM php:8.2.0-fpm as moolah-php

# Set the timezone
ENV TZ=Europe/Paris
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install `gd` dependencies
RUN apt-get update && apt-get install -y libfreetype-dev libjpeg62-turbo-dev libpng-dev libwebp-dev

# Install `zip` dependencies
RUN apt-get update && apt-get install -y libzip-dev zip

# Install `pdo_pgsql` dependencies
RUN apt-get update && apt-get install -y libpq-dev

# Install `intl` dependencies
RUN apt-get update && apt-get install -y libicu-dev

# Install `mbstring` dependencies
RUN apt-get update && apt-get install -y libonig-dev

# Configure GD with Freetype (see: https://www.php.net/manual/en/function.imagettftext.php#refsect1-function.imagettftext-notes)
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp

# Install PHP extensions
RUN docker-php-ext-install gd zip pdo_pgsql intl fileinfo mbstring

# Show all modules
RUN php -m

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy data
WORKDIR /var/www/html
COPY . .

# Run composer
RUN composer install --no-cache --no-progress

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Caddy (with only public folder)
FROM caddy:latest as moolah-caddy
COPY --from=moolah-php /var/www/html/public /var/www/html/public
COPY build/docker/Caddyfile /etc/caddy/Caddyfile