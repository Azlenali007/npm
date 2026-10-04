#!/usr/bin/env bash
set -e

# Ensure MariaDB is running
if ! pgrep -f mariadbd >/dev/null 2>&1; then
    mariadbd-safe --skip-syslog >/dev/null 2>&1 &
    sleep 2
fi

# Ensure database and base schema are initialized
php init_db.php >/dev/null 2>&1 || true

# Start PHP built-in server on port 3000
echo "Starting N.A Fresh Fruits & Coconuts PHP server on port 3000..."
exec php -S 0.0.0.0:3000 router.php
