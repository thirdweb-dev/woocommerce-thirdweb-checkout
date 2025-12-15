#!/bin/bash

##
# Build and Package Script for thirdweb WooCommerce Checkout Plugin
#
# This script creates a clean distribution package suitable for:
# - WordPress.org plugin directory
# - GitHub releases
# - Manual installation
#
# Usage:
#   ./package.sh [version]
#
# Example:
#   ./package.sh 1.0.0
##

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Plugin details
PLUGIN_SLUG="thirdweb-woocommerce-checkout"
PLUGIN_DIR=$(pwd)
DIST_DIR="$PLUGIN_DIR/dist"
BUILD_DIR="$DIST_DIR/$PLUGIN_SLUG"

# Get version from argument or ask user
if [ -z "$1" ]; then
    echo -e "${YELLOW}No version specified. Using version from plugin file...${NC}"
    VERSION=$(grep "Version:" thirdweb-woocommerce-checkout.php | awk '{print $3}')
    echo -e "${GREEN}Detected version: $VERSION${NC}"
else
    VERSION=$1
    echo -e "${GREEN}Building version: $VERSION${NC}"
fi

echo -e "${YELLOW}================================================${NC}"
echo -e "${YELLOW}Building $PLUGIN_SLUG v$VERSION${NC}"
echo -e "${YELLOW}================================================${NC}\n"

# Step 1: Clean up old build
echo -e "${YELLOW}[1/6] Cleaning up old builds...${NC}"
rm -rf "$DIST_DIR"
mkdir -p "$BUILD_DIR"
echo -e "${GREEN}✓ Clean up complete${NC}\n"

# Step 2: Install dependencies and build frontend
echo -e "${YELLOW}[2/6] Building frontend assets...${NC}"
if ! command -v pnpm &> /dev/null; then
    echo -e "${RED}Error: pnpm is not installed${NC}"
    echo "Install it with: npm install -g pnpm"
    exit 1
fi

pnpm install --frozen-lockfile
pnpm run build

if [ ! -f "build/index.tsx.js" ]; then
    echo -e "${RED}Error: Build failed - build/index.tsx.js not found${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Frontend build complete${NC}\n"

# Step 3: Copy plugin files
echo -e "${YELLOW}[3/6] Copying plugin files...${NC}"

# Core plugin files
cp thirdweb-woocommerce-checkout.php "$BUILD_DIR/"
cp readme.txt "$BUILD_DIR/"
cp README.md "$BUILD_DIR/"
cp LICENSE "$BUILD_DIR/" 2>/dev/null || echo "Warning: LICENSE file not found"
cp .env.example "$BUILD_DIR/"

# Directories
cp -r includes "$BUILD_DIR/"
cp -r build "$BUILD_DIR/"
cp -r assets "$BUILD_DIR/"

echo -e "${GREEN}✓ Files copied${NC}\n"

# Step 4: Clean up development files from build
echo -e "${YELLOW}[4/6] Cleaning development files...${NC}"

# Remove any unwanted files
find "$BUILD_DIR" -name ".DS_Store" -delete
find "$BUILD_DIR" -name "*.log" -delete
find "$BUILD_DIR" -name ".gitkeep" -delete

echo -e "${GREEN}✓ Cleanup complete${NC}\n"

# Step 5: Create ZIP archive
echo -e "${YELLOW}[5/6] Creating ZIP archive...${NC}"

cd "$DIST_DIR"
ZIP_FILE="${PLUGIN_SLUG}-${VERSION}.zip"

# Check if zip command exists
if ! command -v zip &> /dev/null; then
    echo -e "${RED}Error: zip command not found${NC}"
    exit 1
fi

zip -r "$ZIP_FILE" "$PLUGIN_SLUG" -q

if [ -f "$ZIP_FILE" ]; then
    ZIP_SIZE=$(du -h "$ZIP_FILE" | cut -f1)
    echo -e "${GREEN}✓ Created: $ZIP_FILE ($ZIP_SIZE)${NC}\n"
else
    echo -e "${RED}Error: Failed to create ZIP file${NC}"
    exit 1
fi

cd "$PLUGIN_DIR"

# Step 6: Display summary
echo -e "${YELLOW}[6/6] Build Summary${NC}"
echo -e "${YELLOW}================================================${NC}"
echo -e "Plugin:         ${GREEN}$PLUGIN_SLUG${NC}"
echo -e "Version:        ${GREEN}$VERSION${NC}"
echo -e "Build Location: ${GREEN}$BUILD_DIR${NC}"
echo -e "ZIP File:       ${GREEN}$DIST_DIR/$ZIP_FILE${NC}"
echo -e "File Size:      ${GREEN}$ZIP_SIZE${NC}"
echo -e "${YELLOW}================================================${NC}\n"

# Display file structure
echo -e "${YELLOW}Package contents:${NC}"
tree -L 2 "$BUILD_DIR" 2>/dev/null || ls -la "$BUILD_DIR"

echo ""
echo -e "${GREEN}✓ Build complete!${NC}\n"

# Next steps
echo -e "${YELLOW}Next Steps:${NC}"
echo "• Test the plugin by uploading: $DIST_DIR/$ZIP_FILE"
echo "• For WordPress.org: Copy contents from $BUILD_DIR to SVN repo"
echo "• For GitHub: Create release with $DIST_DIR/$ZIP_FILE"
echo ""
