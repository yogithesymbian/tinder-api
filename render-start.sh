#!/usr/bin/env bash

echo "========================================="
echo "Starting Laravel Application"
echo "========================================="

# Run migrations (ensures DB connectivity)
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
CACHE_DRIVER=file php artisan cache:clear || true

# NO php artisan serve here!
# Render will automatically start PHP-FPM as CMD in Dockerfile
echo "Laravel is ready. PHP-FPM will serve requests automatically."
