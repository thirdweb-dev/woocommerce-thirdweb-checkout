#!/bin/bash

# Test Setup Script for WooCommerce thirdweb Checkout Plugin
# This script helps you set up a local test environment

echo "🧪 WooCommerce thirdweb Checkout - Local Test Setup"
echo ""

# Check if build exists
if [ ! -d "build" ] || [ ! -f "build/index.tsx.js" ]; then
    echo "❌ Build files not found. Running build..."
    pnpm run build
    if [ $? -ne 0 ]; then
        echo "❌ Build failed. Please fix errors and try again."
        exit 1
    fi
fi

echo "✅ Build files exist"
echo ""

# Check PHP syntax
echo "🔍 Checking PHP syntax..."
find . -name "*.php" -not -path "./node_modules/*" -not -path "./vendor/*" | while read file; do
    php -l "$file" > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        echo "❌ Syntax error in: $file"
        php -l "$file"
        exit 1
    fi
done

echo "✅ PHP syntax OK"
echo ""

# List WordPress installation paths
echo "📂 Common WordPress installation paths:"
echo "   - LocalWP: ~/Local Sites/[site-name]/app/public/wp-content/plugins/"
echo "   - MAMP: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/"
echo "   - Docker: Check your docker-compose volumes"
echo ""

echo "📋 Installation options:"
echo ""
echo "1. Symlink (recommended for development):"
echo "   ln -s $(pwd) ~/Local\\ Sites/YOUR_SITE/app/public/wp-content/plugins/thirdweb-woocommerce-checkout"
echo ""
echo "2. Copy (for testing):"
echo "   cp -r $(pwd) ~/Local\\ Sites/YOUR_SITE/app/public/wp-content/plugins/thirdweb-woocommerce-checkout"
echo ""
echo "3. Package as ZIP:"
echo "   pnpm run package"
echo "   Upload: dist/thirdweb-woocommerce-checkout.zip via WordPress Admin"
echo ""

# Check for .env file
if [ -f ".env" ]; then
    echo "✅ .env file found"
    if grep -q "THIRDWEB_CLIENT_ID=" .env; then
        CLIENT_ID=$(grep "THIRDWEB_CLIENT_ID=" .env | cut -d '=' -f 2 | tr -d '"' | tr -d "'")
        if [ -n "$CLIENT_ID" ] && [ "$CLIENT_ID" != "your_client_id_here" ]; then
            echo "   Client ID configured: ${CLIENT_ID:0:20}..."
        else
            echo "⚠️  Client ID not set in .env file"
        fi
    fi
else
    echo "⚠️  No .env file found (optional for development)"
    echo "   Create one with: cp .env.example .env"
fi

echo ""
# Check for test app
if [ -d "test-app" ]; then
    echo "✅ test-app/ directory found for standalone testing"
    if [ -f "test-app/.env" ]; then
        echo "   Test app configured"
    else
        echo "⚠️  No .env file in test-app/ (needed for testing)"
        echo "   Create one with: cd test-app && cp .env.example .env"
    fi
else
    echo "⚠️  No test-app/ directory found"
fi

echo ""
echo "🎯 Next steps:"
echo ""
echo "Option 1: Test CheckoutWidget standalone (recommended first step)"
echo "  cd test-app && pnpm install && cp .env.example .env"
echo "  Edit test-app/.env with your credentials"
echo "  pnpm dev"
echo ""
echo "Option 2: Test with WordPress"
echo "1. Install WordPress locally (LocalWP, MAMP, or Docker)"
echo "2. Install WooCommerce plugin"
echo "3. Link/copy this plugin to wp-content/plugins/"
echo "4. Activate plugin in WordPress Admin"
echo "5. Configure settings: WooCommerce → Settings → Payments"
echo "6. Create test product and try checkout"
echo ""
echo "📖 For Base Sepolia testnet:"
echo "   Chain ID: 84532"
echo "   Get testnet ETH: https://www.alchemy.com/faucets/base-sepolia"
echo ""
echo "✅ Setup check complete!"
