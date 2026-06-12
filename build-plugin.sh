#!/usr/bin/env bash
# build-plugin.sh — builds a ready-to-upload WordPress plugin ZIP
# Usage: ./build-plugin.sh [--rebuild-js]
#
#   --rebuild-js   Re-run `npm run build` even if dist/ already exists

set -e

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_SLUG="gohigh-page-builder"
# Actual directory name on disk (may differ in capitalisation from the slug)
DIR_NAME="$(basename "$PLUGIN_DIR")"
MAIN_FILE="$PLUGIN_DIR/$PLUGIN_SLUG.php"
REBUILD_JS=false

# Parse flags
for arg in "$@"; do
  case $arg in
    --rebuild-js) REBUILD_JS=true ;;
  esac
done

# ── 1. Read version from main plugin file ──────────────────────────────────────
if [ ! -f "$MAIN_FILE" ]; then
  echo "ERROR: Plugin file not found: $MAIN_FILE" >&2
  exit 1
fi

VERSION=$(grep -m1 "define( 'GHPB_VERSION'" "$MAIN_FILE" | sed "s/.*'\([0-9][^']*\)'.*/\1/")
if [ -z "$VERSION" ]; then
  echo "ERROR: Could not read GHPB_VERSION from $MAIN_FILE" >&2
  exit 1
fi

ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"
ZIP_PATH="$PLUGIN_DIR/../$ZIP_NAME"

echo "=========================================="
echo " GoHigh Page Builder — Build Script"
echo " Version : $VERSION"
echo " Output  : $ZIP_NAME"
echo "=========================================="

# ── 2. Install Composer dependencies (required for autoloader) ─────────────────
echo ""
echo "→ Installing Composer dependencies..."
if ! command -v composer &>/dev/null; then
  echo "ERROR: composer is not installed or not in PATH." >&2
  echo "Install it from https://getcomposer.org" >&2
  exit 1
fi

cd "$PLUGIN_DIR"
composer install --no-dev --optimize-autoloader --quiet
echo "  ✓ vendor/ ready"

# ── 3. Rebuild JS/CSS assets (optional) ───────────────────────────────────────
if $REBUILD_JS; then
  echo ""
  echo "→ Rebuilding JS/CSS assets (--rebuild-js flag)..."
  if ! command -v npm &>/dev/null; then
    echo "WARNING: npm not found — skipping JS rebuild. Using existing dist/ files." >&2
  else
    npm install --silent
    npm run build --silent
    echo "  ✓ dist/ rebuilt"
  fi
elif [ ! -d "$PLUGIN_DIR/dist/js" ] || [ -z "$(ls -A "$PLUGIN_DIR/dist/js" 2>/dev/null)" ]; then
  echo ""
  echo "→ dist/ is empty — attempting to build JS/CSS assets..."
  if command -v npm &>/dev/null; then
    npm install --silent
    npm run build --silent
    echo "  ✓ dist/ built"
  else
    echo "WARNING: dist/ is empty and npm is not available." >&2
    echo "  The plugin may not work correctly without compiled assets." >&2
    echo "  Run: npm install && npm run build" >&2
  fi
else
  echo ""
  echo "→ Using existing dist/ assets (pass --rebuild-js to force rebuild)"
fi

# ── 4. Create the ZIP ──────────────────────────────────────────────────────────
echo ""
echo "→ Creating plugin ZIP..."

# Remove old ZIP if it exists
rm -f "$ZIP_PATH"

cd "$PLUGIN_DIR/.."

zip -r "$ZIP_PATH" "$DIR_NAME" \
  --exclude "$DIR_NAME/.git/*" \
  --exclude "$DIR_NAME/.gitignore" \
  --exclude "$DIR_NAME/node_modules/*" \
  --exclude "$DIR_NAME/assets/src/*" \
  --exclude "$DIR_NAME/package.json" \
  --exclude "$DIR_NAME/package-lock.json" \
  --exclude "$DIR_NAME/webpack.config.js" \
  --exclude "$DIR_NAME/.babelrc" \
  --exclude "$DIR_NAME/.eslintrc.js" \
  --exclude "$DIR_NAME/build-plugin.sh" \
  --exclude "$DIR_NAME/dist/js/*.map" \
  --exclude "$DIR_NAME/dist/css/*.map" \
  --exclude "*.DS_Store" \
  --exclude "*__MACOSX*" \
  -q

# ── 5. Done ────────────────────────────────────────────────────────────────────
if [ -f "$ZIP_PATH" ]; then
  SIZE=$(du -sh "$ZIP_PATH" | cut -f1)
  echo "  ✓ ZIP created: $ZIP_NAME ($SIZE)"
  echo ""
  echo "=========================================="
  echo " Install via WordPress Admin:"
  echo " Plugins → Add New → Upload Plugin"
  echo " → Select: $ZIP_NAME"
  echo "=========================================="
  echo ""
  echo "Output file: $ZIP_PATH"
else
  echo "ERROR: ZIP was not created." >&2
  exit 1
fi
