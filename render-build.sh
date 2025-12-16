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

echo "Clearing Laravel caches (using file cache for build)..."
# Use file-based cache during build to avoid database dependency
CACHE_STORE=file php artisan config:clear
CACHE_STORE=file php artisan cache:clear
php artisan route:clear
php artisan view:clear

# ❌ DO NOT cache config / routes / views on Render
# Config cache causes issues when environment variables change
# php artisan config:cache   <-- REMOVE
# php artisan route:cache    <-- REMOVE
# php artisan view:cache     <-- REMOVE

# if [ -f "package.json" ]; then
#     echo "Installing Node dependencies..."
#     npm ci --ignore-scripts
#     echo "Building frontend assets..."
#     npm run build
# fi

echo "===== AFTER BUILD ====="
pwd
ls -la public
ls -la public/index.php || echo "❌ index.php missing after build"


echo "========================================="
echo "Build completed successfully!"
echo "========================================="
