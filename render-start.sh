#!/usr/bin/env bash

set -e

echo "========================================="
echo "Starting Laravel Application on Render"
echo "========================================="

# Verify required environment variables
if [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    echo "ERROR: Database environment variables are not set!"
    echo "DB_CONNECTION=$DB_CONNECTION"
    echo "DB_HOST=$DB_HOST"
    echo "DB_DATABASE=$DB_DATABASE"
    echo "DB_USERNAME=$DB_USERNAME"
    exit 1
fi

echo "Database Configuration:"
echo "  Connection: $DB_CONNECTION"
echo "  Host: $DB_HOST"
echo "  Database: $DB_DATABASE"
echo "  Username: $DB_USERNAME"

# Wait for PostgreSQL to be ready (max 30 seconds)
echo "Waiting for PostgreSQL to be ready..."
export PGPASSWORD="$DB_PASSWORD"
for i in {1..30}; do
    if pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" > /dev/null 2>&1; then
        echo "✓ PostgreSQL is ready!"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "ERROR: PostgreSQL is not ready after 30 seconds"
        exit 1
    fi
    echo "  Waiting... ($i/30)"
    sleep 1
done
unset PGPASSWORD

# Test database connection
echo "Testing database connection..."
php artisan db:show || {
    echo "ERROR: Cannot connect to database!"
    exit 1
}

# Run migrations
echo "Running database migrations..."
php artisan migrate --force || {
    echo "ERROR: Migration failed!"
    exit 1
}

# Run seeders only if SEED_DATABASE is set to true
if [ "$SEED_DATABASE" = "true" ]; then
    echo "Running database seeders..."
    php artisan db:seed || {
        echo "WARNING: Seeding failed, continuing anyway..."
    }
else
    echo "Skipping database seeders (set SEED_DATABASE=true to enable)"
fi

# Generate Swagger documentation (non-critical)
echo "Generating API documentation..."
php artisan l5-swagger:generate || {
    echo "WARNING: Swagger generation failed, continuing anyway..."
}

# Clear and warm up cache
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear

# Serve Laravel on the port Render provides
PORT=${PORT:-10000}
echo "========================================="
echo "✓ Application ready!"
echo "  Listening on port $PORT"
echo "========================================="
php artisan serve --host=0.0.0.0 --port=$PORT
