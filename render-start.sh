#!/usr/bin/env bash

echo "========================================="
echo "Starting Laravel Application"
echo "========================================="

# Force Laravel to use environment variables
export DB_CONNECTION=$DB_CONNECTION
export DB_HOST=$DB_HOST
export DB_PORT=$DB_PORT
export DB_DATABASE=$DB_DATABASE
export DB_USERNAME=$DB_USERNAME
export DB_PASSWORD=$DB_PASSWORD

# Show DB connection info for debugging
echo "DB_CONNECTION=$DB_CONNECTION"
echo "DB_HOST=$DB_HOST"
echo "DB_DATABASE=$DB_DATABASE"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force || exit 1
php artisan db:seed

# Generate Swagger (non-critical)
echo "Generating API documentation..."
php artisan l5-swagger:generate || true

# Clear config & cache safely
echo "Clearing caches..."
php artisan config:clear || true
CACHE_DRIVER=file php artisan cache:clear || true

# Serve Laravel on the port Render provides
PORT=${PORT:-10000}
echo "Server starting on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT
