#!/bin/bash

echo "========================================"
echo "Fixing Laravel Directory Permissions"
echo "========================================"
echo ""

# Navigate to backend directory
cd "$(dirname "$0")"

# Create required directories
echo "[1/3] Creating required directories..."
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

echo "  ✓ Directories created"
echo ""

# Set permissions
echo "[2/3] Setting permissions..."
chmod -R 775 bootstrap/cache 2>/dev/null || chmod -R 777 bootstrap/cache
chmod -R 775 storage 2>/dev/null || chmod -R 777 storage

echo "  ✓ Permissions set"
echo ""

# Try to set ownership (may require sudo)
echo "[3/3] Setting ownership..."
if [ -n "$USER" ]; then
    sudo chown -R $USER:$USER bootstrap/cache 2>/dev/null || true
    sudo chown -R $USER:$USER storage 2>/dev/null || true
    echo "  ✓ Ownership set (if possible)"
else
    echo "  ⚠ Could not determine user, skipping ownership"
fi

echo ""
echo "========================================"
echo "Done! Try running: php artisan --version"
echo "========================================"


