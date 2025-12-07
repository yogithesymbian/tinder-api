#!/usr/bin/env bash
# Render Start Script for Laravel Application

set -e  # Exit on error

echo "========================================="
echo "Starting Laravel Application"
echo "========================================="

# Wait for database to be ready
echo "Waiting for database connection..."
until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE"; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Generate Swagger documentation
echo "Generating API documentation..."
php artisan l5-swagger:generate

# Clear cache in case of any issues
echo "Clearing application cache..."
php artisan config:clear
php artisan cache:clear

# Start PHP-FPM or Laravel server
echo "Starting web server..."

# Use PHP's built-in server on the PORT provided by Render
# Render sets the PORT environment variable automatically
PORT=${PORT:-8000}

echo "Server starting on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT --no-reload
