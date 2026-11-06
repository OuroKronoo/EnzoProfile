#!/bin/sh
set -e

STORAGE_DIR="${STORAGE_DIR:-/var/data}"
export PORT="${PORT:-10000}"

mkdir -p "$STORAGE_DIR/data" "$STORAGE_DIR/uploads"

# First run (empty disk): seed with the content committed to the repo. Never overwrite.
cp -n /opt/seed/data/content.json "$STORAGE_DIR/data/content.json" 2>/dev/null || true
cp -rn /opt/seed/uploads/. "$STORAGE_DIR/uploads/" 2>/dev/null || true

# The mounted disk is owned by root; Apache workers run as www-data.
chown -R www-data:www-data "$STORAGE_DIR"
chmod 755 "$STORAGE_DIR/data" "$STORAGE_DIR/uploads"

exec "$@"
