#!/usr/bin/env bash
# Render Build Script for Laravel Application

set -e

echo "========================================="
echo "Starting Render Build Process"
echo "========================================="

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "Setting up storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Clearing Laravel caches (NO config cache)..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# ❌ DO NOT cache config / routes / views on Render
# php artisan config:cache   <-- REMOVE
# php artisan route:cache    <-- REMOVE
# php artisan view:cache     <-- REMOVE

if [ -f "package.json" ]; then
    echo "Installing Node dependencies..."
    npm ci --ignore-scripts
    echo "Building frontend assets..."
    npm run build
fi

echo "========================================="
echo "Build completed successfully!"
echo "========================================="
