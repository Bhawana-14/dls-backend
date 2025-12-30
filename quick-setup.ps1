# Quick Setup Script for Windows PowerShell
# Run this from the backend directory

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "DLS Calculator - Backend Quick Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path "composer.json")) {
    Write-Host "ERROR: composer.json not found!" -ForegroundColor Red
    Write-Host "Please run this script from the backend directory" -ForegroundColor Yellow
    exit 1
}

# Check PHP
Write-Host "[1/5] Checking PHP..." -ForegroundColor Yellow
try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    Write-Host "PHP found: $phpVersion" -ForegroundColor Green
} catch {
    Write-Host "ERROR: PHP not found!" -ForegroundColor Red
    Write-Host "Please install PHP 8.1+ and add it to PATH" -ForegroundColor Yellow
    exit 1
}

# Check Composer
Write-Host "[2/5] Checking Composer..." -ForegroundColor Yellow
try {
    $composerVersion = composer --version 2>&1 | Select-Object -First 1
    Write-Host "Composer found: $composerVersion" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Composer not found!" -ForegroundColor Red
    Write-Host "Please install Composer and add it to PATH" -ForegroundColor Yellow
    exit 1
}

# Create missing directories
Write-Host "[3/5] Creating directory structure..." -ForegroundColor Yellow
$directories = @(
    'bootstrap',
    'public',
    'storage',
    'storage\framework',
    'storage\framework\cache',
    'storage\framework\sessions',
    'storage\framework\views',
    'storage\logs',
    'bootstrap\cache'
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Force -Path $dir | Out-Null
        Write-Host "  Created: $dir" -ForegroundColor Gray
    }
}

# Install dependencies
Write-Host "[4/5] Installing dependencies (this may take a while)..." -ForegroundColor Yellow
composer install
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Failed to install dependencies" -ForegroundColor Red
    exit 1
}

# Setup .env
Write-Host "[5/5] Setting up environment..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "  Created .env file" -ForegroundColor Gray
    } else {
        Write-Host "  WARNING: .env.example not found" -ForegroundColor Yellow
    }
    
    # Generate key
    if (Test-Path "artisan") {
        php artisan key:generate
        Write-Host "  Generated application key" -ForegroundColor Gray
    }
} else {
    Write-Host "  .env file already exists" -ForegroundColor Gray
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "To start the server, run:" -ForegroundColor Cyan
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host ""


