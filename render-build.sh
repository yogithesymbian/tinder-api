#!/usr/bin/env bash
# Render Build Script for Laravel Application

set -e  # Exit on error

echo "========================================="
echo "Starting Render Build Process"
echo "========================================="

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
# Use array cache driver during build to avoid database dependency
echo "Optimizing Laravel application..."
CACHE_STORE=array php artisan config:clear
CACHE_STORE=array php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache config for better performance
# Use array cache driver to avoid database dependency during build
CACHE_STORE=array php artisan config:cache
CACHE_STORE=array php artisan route:cache
CACHE_STORE=array php artisan view:cache

# Install npm dependencies and build assets (if needed)
if [ -f "package.json" ]; then
    echo "Installing Node dependencies..."
    npm ci --ignore-scripts
    echo "Building frontend assets..."
    npm run build || echo "Warning: Frontend asset build failed, continuing anyway..."
fi

echo "========================================="
echo "Build completed successfully!"
echo "========================================="
