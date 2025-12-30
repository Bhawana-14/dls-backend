# Quick Fix for "Could not open input file: artisan"

## Immediate Solution

1. **Make sure you're in the backend directory:**
   ```powershell
   cd backend
   ```

2. **Run the quick setup script:**
   ```powershell
   powershell -ExecutionPolicy Bypass -File quick-setup.ps1
   ```

   OR manually:
   ```powershell
   composer install
   copy .env.example .env
   php artisan key:generate
   ```

3. **Start the server:**
   ```powershell
   php artisan serve
   ```

## What the Script Does

The `quick-setup.ps1` script will:
- ✅ Check if PHP and Composer are installed
- ✅ Create all missing Laravel directories
- ✅ Install all PHP dependencies
- ✅ Set up the .env file
- ✅ Generate the application key

## If Script Doesn't Work

### Method 1: Fresh Laravel Install

```powershell
# Go to parent directory
cd ..

# Create new Laravel project
composer create-project laravel/laravel backend_new

# Copy our custom files:
# - app/Services/DLSCalculatorService.php
# - app/Http/Controllers/DLSCalculatorController.php  
# - routes/api.php (merge with existing)
# - config/cors.php
```

### Method 2: Manual Directory Creation

```powershell
cd backend

# Create directories
mkdir bootstrap, public, storage, storage\framework, storage\framework\cache, storage\framework\sessions, storage\framework\views, storage\logs, bootstrap\cache

# Install dependencies
composer install

# Setup .env
copy .env.example .env
php artisan key:generate
```

## Verify It Works

```powershell
# Check artisan exists
dir artisan

# Test artisan
php artisan --version

# Should show: Laravel Framework 10.x.x
```

## Still Having Issues?

1. Make sure you're in the `backend` folder
2. Make sure `composer install` completed successfully
3. Check that `vendor/` directory exists
4. Verify PHP version: `php -v` (needs 8.1+)


