#!/usr/bin/env bash
# Render Start Script for Laravel Application
#
# This script handles the startup process for the Laravel application on Render.com:
# 1. Waits for database connection to be ready
# 2. Runs database migrations
# 3. Generates API documentation
# 4. Clears application cache (using file driver to avoid DB dependency)
# 5. Starts the web server
#
# Note: We don't use 'set -e' here because we want to handle errors gracefully
# and continue the startup process even if some non-critical commands fail.
# Only critical failures (like migration errors) will cause the script to exit.

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
if ! php artisan migrate --force --no-interaction; then
    echo "ERROR: Database migrations failed!"
    exit 1
fi

# Generate Swagger documentation
echo "Generating API documentation..."
if ! php artisan l5-swagger:generate; then
    echo "Warning: API documentation generation failed, but continuing..."
fi

# Clear cache in case of any issues
# Use file cache driver to avoid database dependency issues during startup
echo "Clearing application cache..."
if ! php artisan config:clear; then
    echo "Warning: Config cache clear failed, but continuing..."
fi

if ! CACHE_STORE=file php artisan cache:clear; then
    echo "Warning: Cache clear failed, but continuing..."
fi

# Start PHP-FPM or Laravel server
echo "Starting web server..."

# Use PHP's built-in server on the PORT provided by Render
# Render sets the PORT environment variable automatically
PORT=${PORT:-8000}

echo "Server starting on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT --no-reload
