# Use official PHP CLI image
# Note: Using CLI instead of FPM/Apache because the application 
# uses Laravel's built-in server (php artisan serve) via render-start.sh
FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-client \
    libpq-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js and npm (required for asset building)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Copy application files
COPY . .

# Make scripts executable
RUN chmod +x render-build.sh render-start.sh

# Run build script during Docker build
RUN ./render-build.sh

# Expose port
EXPOSE 8000

# Start command for the container
CMD ["./render-start.sh"]
