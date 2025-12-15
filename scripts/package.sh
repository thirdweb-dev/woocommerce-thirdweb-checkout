#!/bin/bash

# Package the WordPress plugin for distribution
set -e

PLUGIN_NAME="thirdweb-woocommerce-checkout"
BUILD_DIR="dist"
PLUGIN_DIR="$BUILD_DIR/$PLUGIN_NAME"

echo "📦 Packaging $PLUGIN_NAME..."

# Clean previous build
rm -rf "$BUILD_DIR"
mkdir -p "$PLUGIN_DIR"

# Copy production files
echo "📋 Copying files..."
rsync -av \
  --exclude='node_modules/' \
  --exclude='src/' \
  --exclude='.git/' \
  --exclude='dist/' \
  --exclude='.env' \
  --exclude='.DS_Store' \
  --exclude='*.log' \
  --exclude='scripts/' \
  --exclude='.gitignore' \
  --exclude='tsconfig.json' \
  --exclude='webpack.config.js' \
  --exclude='package.json' \
  --exclude='pnpm-lock.yaml' \
  --exclude='CLAUDE.md' \
  --exclude='.claude/' \
  --exclude='.npmrc' \
  --exclude='demo/' \
  --exclude='test-app/' \
  --exclude='test-setup.sh' \
  ./ "$PLUGIN_DIR/"

# Create ZIP
echo "🗜️  Creating ZIP archive..."
cd "$BUILD_DIR"
zip -r "$PLUGIN_NAME.zip" "$PLUGIN_NAME" > /dev/null

echo "✅ Package created: $BUILD_DIR/$PLUGIN_NAME.zip"
echo ""
echo "📍 Distribution package contains:"
echo "   - Main plugin files (PHP)"
echo "   - Compiled JavaScript (build/)"
echo "   - Assets (icons, etc.)"
echo "   - Documentation (README.md, .env.example)"
echo ""
echo "🚀 Ready to upload to WordPress!"
