# Alternative Free Deployment Platforms

While this repository is primarily configured for Render.com, here are other free platforms you can use to deploy this Laravel application with PostgreSQL.

## 1. Railway.app

**Free Tier:** $5 in credits per month (resets monthly)

### Pros:
- Easy deployment from GitHub
- Built-in PostgreSQL database
- Automatic deployments on git push
- Simple environment variable management
- Good free tier limits

### Deployment Steps:

1. **Sign up**: https://railway.app
2. **Create New Project**: "Deploy from GitHub repo"
3. **Add PostgreSQL**: Click "+ New" → "Database" → "PostgreSQL"
4. **Add Web Service**: Click "+ New" → "GitHub Repo" → Select your repository
5. **Configure**:
   - Build Command: `composer install --no-dev && npm install && npm run build`
   - Start Command: `php artisan serve --host=0.0.0.0 --port=$PORT`
6. **Set Environment Variables**: Reference the PostgreSQL variables automatically
7. **Deploy**: Railway will automatically deploy

### Railway Configuration File

Create `railway.json`:
```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "nixpacks"
  },
  "deploy": {
    "numReplicas": 1,
    "startCommand": "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT",
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

## 2. Fly.io

**Free Tier:** 
- 3 shared-cpu-1x VMs with 256MB RAM
- 3GB persistent volume storage
- 160GB outbound data transfer

### Pros:
- Global deployment locations
- Good performance
- PostgreSQL support via fly-postgres
- SSH access to containers

### Deployment Steps:

1. **Install flyctl**: https://fly.io/docs/hands-on/install-flyctl/
   ```bash
   curl -L https://fly.io/install.sh | sh
   ```

2. **Login**:
   ```bash
   fly auth login
   ```

3. **Initialize app**:
   ```bash
   fly launch
   ```
   - Follow prompts to create app
   - Choose region
   - Don't deploy yet

4. **Create PostgreSQL**:
   ```bash
   fly postgres create
   fly postgres attach <postgres-app-name>
   ```

5. **Configure Dockerfile**:
   Create `Dockerfile`:
   ```dockerfile
   FROM php:8.2-fpm
   
   # Install dependencies
   RUN apt-get update && apt-get install -y \
       libpq-dev \
       libzip-dev \
       zip \
       unzip \
       nodejs \
       npm
   
   # Install PHP extensions
   RUN docker-php-ext-install pdo pdo_pgsql pgsql zip
   
   # Install Composer
   COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
   
   # Set working directory
   WORKDIR /var/www
   
   # Copy application
   COPY . .
   
   # Install dependencies
   RUN composer install --no-dev --optimize-autoloader
   RUN npm ci --omit=dev && npm run build
   
   # Set permissions
   RUN chown -R www-data:www-data storage bootstrap/cache
   
   EXPOSE 8080
   
   CMD php artisan migrate --force && \
       php artisan config:cache && \
       php artisan route:cache && \
       php artisan view:cache && \
       php artisan serve --host=0.0.0.0 --port=8080
   ```

6. **Deploy**:
   ```bash
   fly deploy
   ```

## 3. Heroku (Student Pack Required)

**Free Tier:** No longer available for general use, but available through GitHub Student Developer Pack

### Pros:
- Well-documented
- Easy to use
- Many add-ons available
- PostgreSQL included

### Deployment Steps:

1. **Install Heroku CLI**: https://devcenter.heroku.com/articles/heroku-cli
2. **Login**:
   ```bash
   heroku login
   ```

3. **Create app**:
   ```bash
   heroku create tinder-api
   ```

4. **Add PostgreSQL**:
   ```bash
   heroku addons:create heroku-postgresql:mini
   ```

5. **Set buildpacks**:
   ```bash
   heroku buildpacks:add heroku/php
   heroku buildpacks:add heroku/nodejs
   ```

6. **Configure**: The `Procfile` is already included in this repository

7. **Deploy**:
   ```bash
   git push heroku main
   ```

8. **Run migrations**:
   ```bash
   heroku run php artisan migrate --force
   ```

## 4. DigitalOcean App Platform

**Free Tier:** 
- Static sites only (free)
- Apps start at $5/month (not free, but affordable)

### Note:
DigitalOcean App Platform doesn't have a truly free tier for dynamic apps, but their starter tier ($5/month) is very affordable and includes:
- 512 MB RAM
- PostgreSQL database
- 1 GB disk

### Deployment Steps:

1. **Sign up**: https://cloud.digitalocean.com
2. **Create App**: Apps → Create App → GitHub
3. **Select Repository**
4. **Configure**:
   - Build Command: `composer install --no-dev && npm ci --omit=dev && npm run build`
   - Run Command: `php artisan serve --host=0.0.0.0 --port=8080`
5. **Add PostgreSQL**: Add a managed database
6. **Set Environment Variables**
7. **Deploy**

## 5. Vercel (Limited for Laravel)

**Note:** Vercel is primarily for static sites and serverless functions. Running a full Laravel app is possible but not ideal.

**Better Alternative:** Use Vercel for frontend + separate backend on Render/Railway

## 6. Netlify (Not Recommended for Laravel)

Netlify is designed for static sites and JAMstack applications. Not suitable for Laravel backend.

## 7. Back4App (Parse Server Based)

**Free Tier:**
- 250MB database storage
- 25k requests/month
- Limited but workable for demos

## 8. AWS Free Tier (Most Complex)

**Free Tier (12 months):**
- EC2 t2.micro instance (750 hours/month)
- RDS db.t2.micro (750 hours/month)
- 5GB storage

### Pros:
- Industry standard
- Scalable
- Many services available

### Cons:
- Complex setup
- Requires AWS knowledge
- Easy to accidentally exceed free tier

## 9. Oracle Cloud Free Tier (Forever Free)

**Free Tier:**
- 2 AMD-based VMs (1/8 OCPU, 1GB RAM each)
- Up to 4 Arm-based VMs (4 OCPUs, 24GB RAM total)
- 2 Block Volumes (100 GB total)
- 10GB Object Storage

### Pros:
- Generous free tier
- Forever free (doesn't expire after 12 months)
- Good for learning

### Cons:
- Complex setup
- Manual server management required
- Learning curve

## Platform Comparison

| Platform | Free Tier | Database | Auto-Deploy | Ease of Setup | Best For |
|----------|-----------|----------|-------------|---------------|----------|
| **Render.com** | ✅ Yes | PostgreSQL | ✅ Yes | ⭐⭐⭐⭐⭐ Easy | **Recommended** |
| **Railway.app** | $5/month credit | PostgreSQL | ✅ Yes | ⭐⭐⭐⭐⭐ Easy | Great alternative |
| **Fly.io** | ✅ Limited | PostgreSQL | ✅ Yes | ⭐⭐⭐⭐ Moderate | Good for global apps |
| **Heroku** | Student only | PostgreSQL | ✅ Yes | ⭐⭐⭐⭐⭐ Easy | With student pack |
| **DigitalOcean** | ❌ $5/month | PostgreSQL | ✅ Yes | ⭐⭐⭐⭐ Easy | Low-cost production |
| **AWS** | 12 months | RDS | ❌ Manual | ⭐⭐ Complex | Learning AWS |
| **Oracle Cloud** | ✅ Forever | Manual setup | ❌ Manual | ⭐ Very Complex | High resources needed |

## Recommendation for Demo

**Primary Choice: Render.com** (This repository is configured for it)
- Easy setup with `render.yaml`
- Free tier sufficient for demos
- Auto-deployment from GitHub
- Managed PostgreSQL database

**Alternative: Railway.app**
- If you exceed Render's free tier
- $5/month credit usually sufficient for demos
- Similar ease of use

**For Learning Cloud Platforms: AWS or Oracle Cloud**
- If you want to learn cloud infrastructure
- More manual but teaches valuable skills

## Getting Help

- **Render.com**: https://render.com/docs
- **Railway.app**: https://docs.railway.app
- **Fly.io**: https://fly.io/docs
- **Heroku**: https://devcenter.heroku.com
- **DigitalOcean**: https://docs.digitalocean.com

## Final Notes

This repository is optimized for **Render.com**, but the application can run on any platform that supports:
- PHP 8.2+
- PostgreSQL (or SQLite)
- Composer
- Node.js/npm

The deployment scripts (`render-build.sh`, `render-start.sh`) can be adapted for other platforms with minimal changes.
