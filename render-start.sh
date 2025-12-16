#!/usr/bin/env bash

set -e

echo "========================================="
echo "Starting Laravel Application on Render"
echo "========================================="

php -m | grep intl || {
  echo "❌ intl extension NOT installed"
  exit 1
}


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
echo "  Port: ${DB_PORT:-5432}"
echo "  Database: $DB_DATABASE"
echo "  Username: $DB_USERNAME"

# Configurable timeout for PostgreSQL readiness check (default: 60 seconds for Render free tier)
# Render's free tier databases may take longer to spin up from cold start
DB_READY_TIMEOUT=${DB_READY_TIMEOUT:-60}

# Wait for PostgreSQL to be ready
echo "Waiting for PostgreSQL to be ready (timeout: ${DB_READY_TIMEOUT}s)..."
echo "Note: Render.com free tier databases may take 30-60s to wake up from sleep"

# Check if pg_isready is available
if command -v pg_isready > /dev/null 2>&1; then
    echo "Using pg_isready for connection check..."
    export PGPASSWORD="$DB_PASSWORD"
    
    # Use arithmetic expansion for better portability
    for ((i=1; i<=DB_READY_TIMEOUT; i++)); do
        if pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" > /dev/null 2>&1; then
            echo "✓ PostgreSQL is ready! (took ${i}s)"
            break
        fi
        if [ $i -eq "$DB_READY_TIMEOUT" ]; then
            echo "ERROR: PostgreSQL is not ready after ${DB_READY_TIMEOUT} seconds"
            echo "Troubleshooting tips:"
            echo "  1. Check if database is created in Render dashboard"
            echo "  2. Verify DB_HOST uses internal connection string (e.g., <db-name>)"
            echo "  3. Ensure database and web service are in same region"
            echo "  4. Check Render database logs for errors"
            exit 1
        fi
        # Show progress at start (3s) and then every 5 seconds to reduce log noise
        if [ $i -eq 3 ] || [ $((i % 5)) -eq 0 ]; then
            echo "  Still waiting... (${i}/${DB_READY_TIMEOUT}s)"
        fi
        sleep 1
    done
    unset PGPASSWORD
else
    # Fallback: Try direct database connection test with Laravel
    # Wait proportionally to configured timeout (minimum 10s, up to half of DB_READY_TIMEOUT)
    FALLBACK_WAIT=$((DB_READY_TIMEOUT / 2))
    if [ $FALLBACK_WAIT -lt 10 ]; then
        FALLBACK_WAIT=10
    fi
    echo "pg_isready not available, using Laravel connection test..."
    echo "Waiting ${FALLBACK_WAIT}s before attempting connection..."
    sleep "$FALLBACK_WAIT"
fi

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
