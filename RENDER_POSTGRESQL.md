# PostgreSQL Connection on Render.com

This document explains how the PostgreSQL connection check works with Render.com's internal database connections.

## Overview

The `render-start.sh` script includes a PostgreSQL readiness check that is specifically optimized for Render.com's infrastructure, including the free tier.

## How It Works

### Connection Check Process

1. **Environment Variables**: The script verifies that all required database environment variables are set:
   - `DB_HOST` - Database hostname (internal or external)
   - `DB_PORT` - Database port (defaults to 5432)
   - `DB_DATABASE` - Database name
   - `DB_USERNAME` - Database user
   - `DB_PASSWORD` - Database password

2. **Readiness Check**: Uses PostgreSQL's `pg_isready` utility:
   ```bash
   pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME"
   ```

3. **Configurable Timeout**: Waits up to `DB_READY_TIMEOUT` seconds (default: 60s) for database to become ready

4. **Progress Logging**: Reports progress every 5 seconds to reduce log noise while providing visibility

5. **Error Handling**: Provides helpful troubleshooting tips if connection fails

## Render.com Internal Connections

### What Are Internal Connections?

Render.com provides two types of database connection strings:

| Type | Format | Network | Speed | Bandwidth |
|------|--------|---------|-------|-----------|
| **Internal** | `<database-name>` | Private | Fast | Free |
| **External** | `dpg-xxx.region.render.com` | Public | Slower | Metered |

### Why Internal Connections Are Recommended

1. **Performance**: Uses Render's private network (lower latency)
2. **Cost**: No bandwidth charges
3. **Security**: Not exposed to public internet
4. **Reliability**: Better for service-to-service communication

### How pg_isready Works with Internal Connections

The `pg_isready` command works seamlessly with Render's internal hostnames because:

1. **DNS Resolution**: Render's private network handles DNS for internal hostnames
2. **TCP Connection**: `pg_isready` uses standard PostgreSQL protocol over TCP
3. **Network Access**: Services in the same Render account can access internal hostnames
4. **No Special Configuration**: Works exactly like external hostnames

Example:
```bash
# Internal hostname (recommended)
DB_HOST=tinder-api-db
pg_isready -h tinder-api-db -p 5432 -U tinder_user
# Output: tinder-api-db:5432 - accepting connections

# External hostname (also works, but slower)
DB_HOST=dpg-xxxxx-a.oregon-postgres.render.com
pg_isready -h dpg-xxxxx-a.oregon-postgres.render.com -p 5432 -U tinder_user
# Output: dpg-xxxxx-a.oregon-postgres.render.com:5432 - accepting connections
```

## Free Tier Considerations

### Cold Start Delay

Render's free tier databases **spin down after 15 minutes of inactivity**:

- First request after sleep triggers database wake-up
- Wake-up can take **30-60 seconds**
- This is why `DB_READY_TIMEOUT` defaults to 60 seconds

### Timeout Configuration

Adjust timeout based on your needs:

```yaml
# render.yaml
- key: DB_READY_TIMEOUT
  value: 90  # Increase for slower cold starts
```

Or set as environment variable in Render dashboard:
```
DB_READY_TIMEOUT=90
```

### Best Practices for Free Tier

1. **Use Internal Connections**: Faster and free bandwidth
2. **Accept Cold Start Delays**: First request may take 60+ seconds
3. **Monitor Startup Logs**: Check if database is sleeping
4. **Keep Services in Same Region**: Reduces latency

## Troubleshooting

### Connection Timeouts

**Symptom**: "PostgreSQL is not ready after 60 seconds"

**Possible Causes**:
1. Database is still spinning up (cold start)
2. Database doesn't exist
3. Wrong credentials
4. Services in different regions
5. Using external hostname (slower)

**Solutions**:
1. Increase `DB_READY_TIMEOUT` to 90 or 120 seconds
2. Verify database exists in Render dashboard
3. Check environment variables match database credentials
4. Ensure both services in same region
5. Use internal hostname instead of external

### DNS Resolution Errors

**Symptom**: "Name or service not known"

**Causes**:
1. Wrong `DB_HOST` format
2. Database not in same Render account
3. Typo in database name

**Solutions**:
1. For Blueprint: Verify `render.yaml` uses `fromDatabase` syntax
2. For Manual: Use internal hostname format: `<database-name>`
3. Check database name matches in Render dashboard

### pg_isready Not Found

**Symptom**: "pg_isready: command not found"

**Cause**: postgresql-client not installed

**Solution**: Already handled in Dockerfile:
```dockerfile
RUN apt-get install -y postgresql-client
```

If error persists, the script includes a fallback mechanism.

## Verification

To verify your database connection configuration:

1. Check Render dashboard → Database → Connections
2. Copy the **Internal Database URL**
3. Extract the hostname (first part before `:5432`)
4. Ensure `DB_HOST` matches this internal hostname

Example:
- Internal URL: `postgresql://tinder_user:password@tinder-api-db:5432/tinder_db`
- Internal hostname: `tinder-api-db`
- Your `DB_HOST`: Should be `tinder-api-db`

## Advanced Configuration

### Custom Wait Strategy

To implement custom wait logic, modify `render-start.sh`:

```bash
# Example: Exponential backoff
wait_time=1
max_wait=60
total_wait=0

while [ $total_wait -lt $max_wait ]; do
    if pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME"; then
        echo "✓ Database ready!"
        break
    fi
    sleep $wait_time
    total_wait=$((total_wait + wait_time))
    wait_time=$((wait_time * 2))
    if [ $wait_time -gt 10 ]; then
        wait_time=10
    fi
done
```

### Skip Connection Check

To skip the pg_isready check entirely (not recommended):

```yaml
# render.yaml
- key: SKIP_DB_READY_CHECK
  value: true
```

Then modify `render-start.sh`:
```bash
if [ "$SKIP_DB_READY_CHECK" != "true" ]; then
    # ... pg_isready check ...
fi
```

## Resources

- [Render.com Databases Docs](https://render.com/docs/databases)
- [PostgreSQL pg_isready](https://www.postgresql.org/docs/current/app-pg-isready.html)
- [Render Private Services](https://render.com/docs/private-services)
- [Render Free Tier Details](https://render.com/docs/free)

## Summary

✅ **pg_isready works perfectly with Render's internal PostgreSQL connections**

Key Points:
- Internal hostnames are DNS-resolvable within Render's network
- No special configuration needed
- Already included in Docker image via postgresql-client
- Optimized for free tier with 60s default timeout
- Provides helpful error messages and troubleshooting tips

The current implementation in `render-start.sh` is well-suited for Render.com deployment, including the free tier.
