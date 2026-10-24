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

# Auto-purge stale route and config cache files so newly pulled code takes effect immediately
rm -f "$SCRIPT_DIR/bootstrap/cache/routes"*.php 2>/dev/null || true
"$PHP_BIN" artisan route:clear >> /dev/null 2>&1 || true
"$PHP_BIN" artisan config:clear >> /dev/null 2>&1 || true

# Auto-apply pending database migrations if any
"$PHP_BIN" artisan migrate --force >> "$SCRIPT_DIR/storage/logs/migrations.log" 2>&1

# Auto-retry pending failed TikTok post with automatic fallback to TikTok Inbox
if [ ! -f "$SCRIPT_DIR/storage/framework/retry_tiktok_0b21a626.lock" ]; then
    "$PHP_BIN" artisan social-media:retry-post 0b21a626-00e2-4d1f-980c-b652fe621736 >> "$SCRIPT_DIR/storage/logs/tiktok_retry.log" 2>&1 || true
    touch "$SCRIPT_DIR/storage/framework/retry_tiktok_0b21a626.lock" 2>/dev/null || true
fi

# Targeted TikTok Test Run for account Mustaqim (Feed, Reels, Carousel)
if [ ! -f "$SCRIPT_DIR/storage/framework/tiktok_test_v4.lock" ]; then
    "$PHP_BIN" artisan social-media:test-upload-suite --platform=tiktok --account-id=55d482c9-0c42-4413-b99b-a4892b7633a2 >> "$SCRIPT_DIR/storage/logs/tiktok_suite_v4.log" 2>&1 || true
    touch "$SCRIPT_DIR/storage/framework/tiktok_test_v4.lock" 2>/dev/null || true
fi

# Targeted LinkedIn Test Run for account Agung Mustaqim (Feed, Reels/Video, Carousel)
if [ ! -f "$SCRIPT_DIR/storage/framework/linkedin_test_v2.lock" ]; then
    "$PHP_BIN" artisan social-media:test-upload-suite --platform=linkedin --account-id=4c0ba6d2-3a27-4000-b5e4-336d9917d3cc >> "$SCRIPT_DIR/storage/logs/linkedin_suite.log" 2>&1 || true
    touch "$SCRIPT_DIR/storage/framework/linkedin_test_v2.lock" 2>/dev/null || true
fi

# Execute Laravel Schedule Run
"$PHP_BIN" artisan schedule:run >> "$SCRIPT_DIR/storage/logs/scheduler.log" 2>&1

