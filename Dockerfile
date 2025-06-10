FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html/data-importer

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libzip-dev \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application source code
COPY . /var/www/html/data-importer

# Copy apache configuration
COPY docker/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache modules
RUN a2enmod rewrite headers

# Set permissions
RUN chown -R www-data:www-data /var/www/html/data-importer \
    && chmod -R 755 /var/www/html/data-importer

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
