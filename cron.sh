#!/usr/bin/env bash
# ==============================================================================
# COOCA ID - Automated Cron Scheduler Runner (cron.sh)
# ==============================================================================
# This script executes Laravel's scheduler (php artisan schedule:run).
# It automates:
#   1. Daily subscription expiration and lifecycle processing (00:01 WIB)
#   2. Daily AI token monthly allowances reset (00:05 WIB)
#   3. Daily WhatsApp expiry reminders: H-7, H-3, H-1, H-Day (09:00 WIB)
#   4. Every-minute background publishing for scheduled social media posts
#
# Production crontab setup:
#   1. Give execute permission:
#      chmod +x /path/to/cooca_core/cron.sh
#   2. Edit crontab:
#      crontab -e
#   3. Add this line (runs every minute):
#      * * * * * cd /path/to/cooca_core && ./cron.sh >> /dev/null 2>&1
# ==============================================================================

set -e

# Resolve directory of this script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Detect PHP CLI binary
PHP_BIN=""
if command -v php >/dev/null 2>&1; then
    PHP_BIN="$(command -v php)"
elif [ -x "/usr/bin/php" ]; then
    PHP_BIN="/usr/bin/php"
elif [ -x "/usr/local/bin/php" ]; then
    PHP_BIN="/usr/local/bin/php"
elif [ -x "/usr/bin/php8.3" ]; then
    PHP_BIN="/usr/bin/php8.3"
elif [ -x "/usr/bin/php8.2" ]; then
    PHP_BIN="/usr/bin/php8.2"
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: PHP CLI binary not found." >&2
    exit 1
fi

# Ensure storage/logs directory exists
mkdir -p "$SCRIPT_DIR/storage/logs"

# Auto-pull latest code from origin main if .git is present
if [ -d "$SCRIPT_DIR/.git" ]; then
    git -C "$SCRIPT_DIR" pull origin main >> "$SCRIPT_DIR/storage/logs/git_pull.log" 2>&1 || true
fi

# Auto-purge stale route cache file so newly pulled routes take effect immediately
if [ -f "$SCRIPT_DIR/bootstrap/cache/routes-v7.php" ]; then
    rm -f "$SCRIPT_DIR/bootstrap/cache/routes-v7.php"
fi

# Auto-apply pending database migrations if any
"$PHP_BIN" artisan migrate --force >> "$SCRIPT_DIR/storage/logs/migrations.log" 2>&1

# Auto-retry pending failed TikTok post with automatic fallback to TikTok Inbox
if [ ! -f "$SCRIPT_DIR/storage/framework/retry_tiktok_0b21a626.lock" ]; then
    "$PHP_BIN" artisan social-media:retry-post 0b21a626-00e2-4d1f-980c-b652fe621736 >> "$SCRIPT_DIR/storage/logs/tiktok_retry.log" 2>&1 || true
    touch "$SCRIPT_DIR/storage/framework/retry_tiktok_0b21a626.lock" 2>/dev/null || true
fi

# Execute Laravel Schedule Run
"$PHP_BIN" artisan schedule:run >> "$SCRIPT_DIR/storage/logs/scheduler.log" 2>&1

