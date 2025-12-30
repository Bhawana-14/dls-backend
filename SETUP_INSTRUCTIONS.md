# Backend Setup Instructions

## The Problem

If you're getting "Could not open input file: artisan", it means:
1. You're not in the `backend` directory, OR
2. The Laravel project structure is incomplete

## Solution: Proper Setup

### Option 1: Create Fresh Laravel Project (Recommended)

This is the cleanest approach:

```powershell
# Navigate to parent directory (one level up from backend)
cd ..

# Remove the incomplete backend folder (optional - backup first!)
# Or rename it: ren backend backend_old

# Create a new Laravel project
composer create-project laravel/laravel backend

# Now copy our custom files into the new project
# Copy these files from backend_old to backend:
# - app/Services/DLSCalculatorService.php
# - app/Http/Controllers/DLSCalculatorController.php
# - routes/api.php (merge with existing)
# - config/cors.php
```

### Option 2: Complete Current Setup

If you want to keep the current structure, follow these steps:

1. **Make sure you're in the backend directory:**
```powershell
cd backend
dir artisan
```

2. **Install Laravel dependencies:**
```powershell
composer install
```

3. **Create missing directories:**
```powershell
mkdir bootstrap
mkdir public
mkdir storage
mkdir storage\framework
mkdir storage\framework\cache
mkdir storage\framework\sessions
mkdir storage\framework\views
mkdir storage\logs
```

4. **Set permissions (if on Linux/Mac):**
```bash
chmod -R 775 storage bootstrap/cache
```

5. **Copy environment file:**
```powershell
copy .env.example .env
```

6. **Generate application key:**
```powershell
php artisan key:generate
```

## Verify Setup

After setup, verify everything works:

```powershell
# Check you're in the right directory
cd backend
dir artisan

# Should show: artisan file exists

# Test artisan
php artisan --version

# Should show: Laravel Framework version
```

## Start the Server

Once setup is complete:

```powershell
php artisan serve
```

You should see:
```
INFO  Server running on [http://127.0.0.1:8000]
```

## Common Issues

### "artisan: No such file or directory"
- You're not in the `backend` directory
- Solution: `cd backend` first

### "Could not open input file: artisan"
- The artisan file doesn't exist
- Solution: Follow Option 1 or Option 2 above

### "Class 'Illuminate\Foundation\Application' not found"
- Dependencies not installed
- Solution: Run `composer install`

### Port 8000 already in use
- Another process is using port 8000
- Solution: `php artisan serve --port=8001`

## Quick Fix Script

Run this in PowerShell from the backend directory:

```powershell
# Create missing directories
@('bootstrap', 'public', 'storage', 'storage\framework', 'storage\framework\cache', 'storage\framework\sessions', 'storage\framework\views', 'storage\logs') | ForEach-Object { New-Item -ItemType Directory -Force -Path $_ }

# Install dependencies
composer install

# Copy .env if it doesn't exist
if (-not (Test-Path .env)) { Copy-Item .env.example .env }

# Generate key
php artisan key:generate
```


