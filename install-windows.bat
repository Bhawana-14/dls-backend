@echo off
echo ========================================
echo DLS Calculator - Backend Setup (Windows)
echo ========================================
echo.

REM Check if PHP is installed
php -v >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP 8.1+ and add it to your PATH
    echo See SETUP_WINDOWS.md for instructions
    pause
    exit /b 1
)

echo [1/4] PHP found: 
php -v
echo.

REM Check if Composer is installed
composer --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Composer is not installed or not in PATH
    echo Please install Composer and add it to your PATH
    echo See INSTALL_COMPOSER.md for instructions
    pause
    exit /b 1
)

echo [2/4] Composer found:
composer --version
echo.

REM Install dependencies
echo [3/4] Installing PHP dependencies...
composer install
if errorlevel 1 (
    echo ERROR: Failed to install dependencies
    pause
    exit /b 1
)
echo.

REM Copy .env file if it doesn't exist
if not exist .env (
    echo [4/4] Creating .env file...
    copy .env.example .env
    echo.
    echo Generating application key...
    php artisan key:generate
) else (
    echo [4/4] .env file already exists, skipping...
)

echo.
echo ========================================
echo Setup complete!
echo ========================================
echo.
echo To start the server, run:
echo   php artisan serve
echo.
pause


