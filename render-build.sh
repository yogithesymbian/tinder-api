#!/usr/bin/env bash
# Render Build Script for Laravel Application

set -e  # Exit on error

echo "========================================="
echo "Starting Render Build Process"
echo "========================================="

# Update system packages
echo "Installing system dependencies..."
apt-get update -qq
apt-get install -y -qq postgresql-client

# Install PHP extensions required for Laravel
echo "Installing PHP extensions..."
apt-get install -y -qq php-pgsql php-xml php-mbstring php-curl php-zip php-gd

# Install Composer dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Create storage directories and set permissions
echo "Setting up storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Clear and cache configuration
echo "Optimizing Laravel application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache config for better performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Install npm dependencies and build assets
if [ -f "package.json" ]; then
    echo "Installing Node dependencies..."
    npm ci --omit=dev
    echo "Building frontend assets..."
    npm run build
fi

echo "========================================="
echo "Build completed successfully!"
echo "========================================="
