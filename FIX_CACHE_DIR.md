# Fix: bootstrap/cache directory must be present and writable

## Quick Fix

Run these commands in the backend directory:

```bash
cd backend

# Create the cache directory
mkdir -p bootstrap/cache

# Set proper permissions (Linux/Mac)
chmod -R 775 bootstrap/cache
chmod -R 775 storage

# If the above doesn't work, try:
chmod -R 777 bootstrap/cache
chmod -R 777 storage
```

## For Windows

If you're on Windows, just create the directory:

```powershell
cd backend
mkdir bootstrap\cache
```

## Verify

After creating the directory, try:

```bash
php artisan --version
```

## If Still Having Issues

If you still get permission errors:

```bash
# Make sure you own the directories
sudo chown -R $USER:$USER bootstrap/cache
sudo chown -R $USER:$USER storage

# Then set permissions
chmod -R 775 bootstrap/cache
chmod -R 775 storage
```

## Alternative: Create All Required Directories

Run this script to create all required directories:

```bash
cd backend

# Create all required directories
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set permissions
chmod -R 775 bootstrap/cache
chmod -R 775 storage
```


