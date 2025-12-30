#!/bin/bash

echo "========================================"
echo "Starting DLS Backend on Remote Server"
echo "========================================"
echo ""

# Get server IP
SERVER_IP=$(hostname -I | awk '{print $1}')
echo "Server IP: $SERVER_IP"
echo ""

# Check if port 8000 is available
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Port 8000 is already in use!"
    echo "   Kill the process or use a different port"
    exit 1
fi

echo "Starting Laravel server..."
echo "Access at: http://$SERVER_IP:8000"
echo ""
echo "Press Ctrl+C to stop"
echo ""

# Start server on all interfaces
php artisan serve --host=0.0.0.0 --port=8000


