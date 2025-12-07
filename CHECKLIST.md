# Deployment Checklist

Use this checklist to ensure a smooth deployment to Render.com.

## Pre-Deployment Checklist

- [ ] Repository is pushed to GitHub
- [ ] All tests are passing locally (`php artisan test`)
- [ ] Environment variables are documented
- [ ] Database migrations are tested
- [ ] API documentation is generated (`php artisan l5-swagger:generate`)

## Render.com Deployment Steps

### Method 1: Blueprint (Recommended - Automated)

- [ ] **Step 1**: Sign up at https://render.com
- [ ] **Step 2**: Click "New" → "Blueprint"
- [ ] **Step 3**: Connect your GitHub account
- [ ] **Step 4**: Select repository `yogithesymbian/tinder-api`
- [ ] **Step 5**: Review the services (Database + Web Service)
- [ ] **Step 6**: Click "Apply" to deploy
- [ ] **Step 7**: Wait 5-10 minutes for first deployment
- [ ] **Step 8**: Note your app URL (e.g., `https://tinder-api.onrender.com`)

### Method 2: Manual Setup

#### Database Setup
- [ ] **Step 1**: Create PostgreSQL database
  - Go to Render Dashboard → "New" → "PostgreSQL"
  - Name: `tinder-api-db`
  - Database: `tinder_db`
  - User: `tinder_user`
  - Plan: Free
- [ ] **Step 2**: Save database connection details

#### Web Service Setup
- [ ] **Step 1**: Create Web Service
  - Go to Render Dashboard → "New" → "Web Service"
  - Connect GitHub repository
  - Name: `tinder-api`
  - Runtime: PHP
  - Build Command: `./render-build.sh`
  - Start Command: `./render-start.sh`
  - Plan: Free

- [ ] **Step 2**: Configure Environment Variables
  ```
  APP_ENV=production
  APP_DEBUG=false
  APP_KEY=[auto-generated]
  DB_CONNECTION=pgsql
  DB_HOST=[from database]
  DB_PORT=5432
  DB_DATABASE=tinder_db
  DB_USERNAME=[from database]
  DB_PASSWORD=[from database]
  SESSION_DRIVER=database
  CACHE_STORE=database
  QUEUE_CONNECTION=database
  LOG_LEVEL=info
  ```

- [ ] **Step 3**: Deploy
  - Click "Create Web Service"
  - Wait for deployment to complete

## Post-Deployment Verification

- [ ] **Check deployment status**: Green checkmark in Render dashboard
- [ ] **Access homepage**: Visit `https://your-app-name.onrender.com`
- [ ] **Check API docs**: Visit `https://your-app-name.onrender.com/api/documentation`
- [ ] **Test registration**: Create a test user via Swagger UI
- [ ] **Test login**: Login with test user credentials
- [ ] **Test protected endpoints**: Use bearer token to access protected routes
- [ ] **Check logs**: Review deployment logs for any errors
- [ ] **Monitor performance**: Check response times

## Testing the Deployed API

### 1. Test Registration
```bash
curl -X POST https://your-app-name.onrender.com/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 2. Test Login
```bash
curl -X POST https://your-app-name.onrender.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 3. Test Protected Endpoint
```bash
curl -X GET https://your-app-name.onrender.com/api/v1/people \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Configuration Updates

- [ ] Update `APP_URL` in environment variables to your Render URL
- [ ] Update `L5_SWAGGER_CONST_HOST` to your Render URL
- [ ] Enable auto-deploy: Settings → "Auto-Deploy" → Enable
- [ ] Configure custom domain (optional, paid plans only)

## Monitoring and Maintenance

- [ ] Set up email notifications for deployment failures
- [ ] Bookmark Render dashboard for quick access
- [ ] Monitor database usage (1GB limit on free tier)
- [ ] Plan for database backup (free tier expires after 90 days)
- [ ] Monitor application logs regularly
- [ ] Keep dependencies updated

## Troubleshooting

If deployment fails, check:

- [ ] Build logs in Render dashboard
- [ ] Environment variables are set correctly
- [ ] Database connection details are correct
- [ ] Scripts are executable (`chmod +x render-*.sh`)
- [ ] PHP version compatibility (requires PHP 8.2+)
- [ ] All required PHP extensions are available

## Common Issues

### Issue: "Application error" on homepage
- Check logs in Render dashboard
- Verify `APP_KEY` is set
- Ensure database migrations ran successfully

### Issue: "Database connection failed"
- Verify database is running
- Check database credentials in environment variables
- Ensure database and web service are in same region

### Issue: "502 Bad Gateway"
- Application may be starting up (wait 30-60 seconds)
- Check start command is correct
- Review application logs for errors

### Issue: API returns 404
- Verify routes are cached (`php artisan route:cache`)
- Check `.htaccess` or nginx configuration
- Ensure Laravel routes are properly defined

## Success Criteria

✅ Deployment is complete when:
- Web service shows "Live" status
- Homepage loads without errors
- API documentation is accessible
- Test user can register and login
- Protected endpoints work with authentication
- No errors in application logs

## Next Steps

After successful deployment:
- Share the API URL with stakeholders
- Document any custom configuration
- Set up monitoring/alerts
- Plan for scaling if needed
- Consider upgrading to paid plan for production use

## Support Resources

- Render Documentation: https://render.com/docs
- Laravel Deployment: https://laravel.com/docs/deployment
- Project Documentation: See DEPLOYMENT.md, QUICKSTART.md, ALTERNATIVES.md
- GitHub Issues: Open an issue in this repository for problems

---

**Note**: First request after deployment may be slow (30-60 seconds) as the service spins up. This is normal on the free tier.
