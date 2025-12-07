# Quick Start Guide

This guide helps you test the application locally before deploying to Render.com.

## Local Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- PostgreSQL (or SQLite for quick testing)
- Node.js and npm

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yogithesymbian/tinder-api.git
   cd tinder-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   
   **Option A: Using SQLite (Quick Start)**
   ```bash
   touch database/database.sqlite
   ```
   The `.env` file is already configured for SQLite.

   **Option B: Using PostgreSQL (Production-like)**
   
   Create a PostgreSQL database and update `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=tinder_db
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Generate Swagger documentation**
   ```bash
   php artisan l5-swagger:generate
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

   The application will be available at: http://localhost:8000

## Testing the API

### Using Swagger UI

1. Open your browser and go to: http://localhost:8000/api/documentation
2. You'll see the interactive API documentation
3. Try the following:

   **Step 1: Register a user**
   - Expand `POST /api/v1/register`
   - Click "Try it out"
   - Fill in the request body:
     ```json
     {
       "name": "Test User",
       "email": "test@example.com",
       "password": "password123",
       "password_confirmation": "password123"
     }
     ```
   - Click "Execute"
   - You should receive a token in the response

   **Step 2: Login**
   - Expand `POST /api/v1/login`
   - Click "Try it out"
   - Fill in credentials:
     ```json
     {
       "email": "test@example.com",
       "password": "password123"
     }
     ```
   - Click "Execute"
   - Copy the token from the response

   **Step 3: Authorize**
   - Click the "Authorize" button at the top of the page
   - Enter: `Bearer YOUR_TOKEN_HERE` (replace with your actual token)
   - Click "Authorize"

   **Step 4: Test protected endpoints**
   - Try `GET /api/v1/people` - Get list of people
   - Try other endpoints that require authentication

### Using cURL

**Register a user:**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

**Get people (requires token):**
```bash
curl -X GET http://localhost:8000/api/v1/people \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Using Postman

1. Import the Swagger JSON from: http://localhost:8000/api/documentation/json
2. Or manually create requests following the API documentation

## Database Seeding (Optional)

To populate the database with sample data:

1. Create a seeder:
   ```bash
   php artisan make:seeder PeopleSeeder
   ```

2. Edit the seeder and run:
   ```bash
   php artisan db:seed --class=PeopleSeeder
   ```

## Running Tests

```bash
php artisan test
```

## Common Issues and Solutions

### Issue: "No application encryption key has been specified"
**Solution:**
```bash
php artisan key:generate
```

### Issue: "could not find driver" (PostgreSQL)
**Solution:** Install PHP PostgreSQL extension:
```bash
# Ubuntu/Debian
sudo apt-get install php-pgsql

# macOS with Homebrew
brew install php@8.2

# Restart your web server after installation
```

### Issue: "Class 'DOMDocument' not found"
**Solution:** Install PHP XML extension:
```bash
sudo apt-get install php-xml
```

### Issue: Storage directory not writable
**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
```

### Issue: npm run build fails
**Solution:**
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

## Development Tools

### Laravel Artisan Commands

- `php artisan route:list` - View all registered routes
- `php artisan migrate:fresh` - Drop all tables and re-run migrations
- `php artisan tinker` - Interactive PHP console
- `php artisan cache:clear` - Clear application cache
- `php artisan config:clear` - Clear configuration cache

### Development Server with Hot Reload

For frontend development with Vite:
```bash
npm run dev
```

Then in another terminal:
```bash
php artisan serve
```

## Next Steps

Once you've tested locally:
1. Push your code to GitHub
2. Follow the [DEPLOYMENT.md](DEPLOYMENT.md) guide to deploy on Render.com
3. Update the APP_URL in production environment variables
4. Test the production deployment

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Swagger/OpenAPI Documentation](https://swagger.io/docs/)
- [Laravel Sanctum Authentication](https://laravel.com/docs/sanctum)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
