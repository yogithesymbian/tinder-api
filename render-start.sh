#!/usr/bin/env bash
# Render Start Script for Laravel Application

set -e  # Exit on error

echo "========================================="
echo "Starting Laravel Application"
echo "========================================="

# Wait for database to be ready
echo "Waiting for database connection..."
# Simple database connection check using PHP artisan
max_retries=30
retry_count=0

while [ $retry_count -lt $max_retries ]; do
    if php artisan migrate:status --no-interaction > /dev/null 2>&1; then
        echo "Database is ready!"
        break
    else
        echo "Database is unavailable - sleeping (attempt $((retry_count + 1))/$max_retries)"
        sleep 2
        retry_count=$((retry_count + 1))
    fi
done

if [ $retry_count -eq $max_retries ]; then
    echo "Warning: Could not verify database connection after $max_retries attempts"
    echo "Proceeding anyway..."
fi

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
