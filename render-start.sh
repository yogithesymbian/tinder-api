#!/usr/bin/env bash

echo "========================================="
echo "Starting Laravel Application"
echo "========================================="

# Run migrations (this already proves DB connectivity)
echo "Running database migrations..."
php artisan migrate --force --no-interaction || {
  echo "ERROR: Database migrations failed!"
  exit 1
}

# Generate Swagger (non-critical)
echo "Generating API documentation..."
php artisan l5-swagger:generate || true

# Clear config & cache safely
echo "Clearing caches..."
php artisan config:clear || true
CACHE_STORE=file php artisan cache:clear || true

# Start server
PORT=${PORT:-10000}
echo "Server starting on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT --no-reload
