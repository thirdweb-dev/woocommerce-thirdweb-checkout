# WooCommerce Stablecoin Checkout with thirdweb CheckoutWidget

A WordPress/WooCommerce plugin that adds stablecoin payment support using thirdweb's CheckoutWidget.

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![GitHub release](https://img.shields.io/github/v/release/thirdweb-dev/woocommerce-thirdweb-checkout)](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/releases)

## Quick Links

- 📖 [Installation Guide](INSTALLATION.md) - Detailed setup instructions
- 🤝 [Contributing](CONTRIBUTING.md) - How to contribute
- 🐛 [Report Issues](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/issues)
- 💬 [Discord Community](https://discord.gg/thirdweb)

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                     WooCommerce Checkout Page                        │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  Payment Methods                                             │    │
│  │  ○ Credit Card                                               │    │
│  │  ○ PayPal                                                    │    │
│  │  ● Pay with Stablecoin (USDC/USDT)  ← Our Plugin            │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                              │                                       │
│                              ▼                                       │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │           thirdweb CheckoutWidget (React)                    │    │
│  │  ┌─────────────────────────────────────────────────────┐    │    │
│  │  │  💳 Pay $50.00                                      │    │    │
│  │  │                                                      │    │    │
│  │  │  Connect Wallet / Pay with:                         │    │    │
│  │  │  [MetaMask] [Coinbase] [WalletConnect]              │    │    │
│  │  │                                                      │    │    │
│  │  │  Or pay with card 💳                                │    │    │
│  │  │                                                      │    │    │
│  │  │  Supported: USDC, USDT on Base, Ethereum, etc.      │    │    │
│  │  └─────────────────────────────────────────────────────┘    │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

## Plugin Structure

```
thirdweb-woocommerce-checkout/
├── thirdweb-woocommerce-checkout.php    # Main plugin file
├── includes/
│   ├── class-thirdweb-payment-gateway.php   # WC_Payment_Gateway
│   └── class-thirdweb-blocks-support.php    # Block checkout support
├── src/
│   └── checkout-block/
│       ├── index.tsx                    # React entry point
│       └── ThirdwebCheckout.tsx         # CheckoutWidget wrapper
├── build/                               # Compiled JS (gitignored)
├── test-app/                            # Standalone React test app
├── .env.example                         # Environment variables template
├── package.json
├── tsconfig.json
└── webpack.config.js
```

## Installation & Setup

### For End Users (WordPress Admin)

1. **Download the Plugin**
   - Download the latest `thirdweb-woocommerce-checkout.zip` from the releases page

2. **Install via WordPress Admin**
   - Go to **WordPress Admin → Plugins → Add New → Upload Plugin**
   - Choose the downloaded ZIP file
   - Click **Install Now**
   - Click **Activate Plugin**

3. **Create thirdweb Account & Project**
   - Go to [thirdweb.com/dashboard](https://thirdweb.com/dashboard) to create an account
   - Create a new project in your dashboard
   - Copy your **Client ID** from the project settings
   - Copy your **project wallet address** (this will receive all payments)

4. **Configure Payment Settings**
   - Navigate to **WooCommerce → Settings → Payments**
   - Find **Stablecoin Payment** and click **Manage**
   - Enable the payment method
   - Enter your **thirdweb Client ID**
   - Enter your **wallet address** to receive payments
   - Enter the **Chain ID** for your preferred blockchain (default: 8453 for Base)
   - Optionally enter the **Token Address** for USDC/USDT on that chain
   - Save changes

### For Developers

#### 1. Install Dependencies

```bash
pnpm install
```

#### 2. Configure Environment Variables (Optional)

For development convenience, you can use a `.env` file:

```bash
cp .env.example .env
```

Edit `.env` and add your thirdweb Client ID:

```env
THIRDWEB_CLIENT_ID=your_client_id_here
```

This provides a default value in WooCommerce settings but can be overridden in the admin panel.

#### 3. Build the Plugin

For development:
```bash
pnpm run start    # Watch mode
```

For production:
```bash
pnpm run build    # Build once
```

#### 4. Create Distribution Package

To create a production-ready ZIP for distribution:

```bash
# Using the package script
./package.sh

# Or specify a version
./package.sh 1.0.0
```

This creates:
- `dist/thirdweb-woocommerce-checkout/` - Plugin directory
- `dist/thirdweb-woocommerce-checkout-VERSION.zip` - Ready for WordPress installation

The package script:
- Builds frontend assets
- Copies only necessary files (excludes dev files)
- Creates a clean ZIP for distribution
- Suitable for WordPress.org and GitHub releases

## Testing the CheckoutWidget

Before integrating with WordPress, you can test the CheckoutWidget standalone using the React test app.

### Standalone Test App (No WordPress Required)

1. **Navigate to the test app**:
   ```bash
   cd test-app
   ```

2. **Install dependencies**:
   ```bash
   pnpm install
   ```

3. **Configure your credentials**:
   ```bash
   cp .env.example .env
   ```

   Edit `.env` with your credentials:
   ```env
   VITE_THIRDWEB_CLIENT_ID=your_client_id_here
   VITE_SELLER_WALLET=0xYourWalletAddress
   VITE_CHAIN_ID=8453
   VITE_TOKEN_ADDRESS=0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913
   VITE_AMOUNT=10.00
   ```

   **Get your credentials**:
   - **Client ID**: From [thirdweb.com/dashboard](https://thirdweb.com/dashboard)
   - **Wallet Address**: Your project wallet address from thirdweb dashboard
   - **Token Address** (optional): USDC on Base `0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913` or leave empty for native ETH

4. **Run the test app**:
   ```bash
   pnpm dev
   ```

   The app will automatically open at `http://localhost:3000`

5. **Test the payment flow**:
   - The CheckoutWidget loads with your configuration
   - Connect your wallet (MetaMask, Coinbase Wallet, etc.) or pay with card
   - Complete a test payment
   - Transaction hash appears on success with block explorer link
   - Verify the transaction on [BaseScan](https://basescan.org)

See `test-app/README.md` for more details.

### Local WordPress Testing

After verifying the CheckoutWidget works standalone, test the full plugin integration:

1. **Install WordPress locally** using one of these options:
   - [LocalWP](https://localwp.com/) (recommended)
   - [MAMP](https://www.mamp.info/)
   - Docker with [docker-wordpress](https://hub.docker.com/_/wordpress)

2. **Install WooCommerce**:
   ```
   WordPress Admin → Plugins → Add New → Search "WooCommerce" → Install & Activate
   ```

3. **Link the plugin** to your WordPress installation:

   **Important**: Run these commands from the plugin root directory (where `thirdweb-woocommerce-checkout.php` is located).

   **Option A: Symlink (recommended for development)**
   ```bash
   # Make sure you're in the plugin directory first
   cd /path/to/woocommerce-thirdweb-checkout

   # LocalWP example:
   ln -s $(pwd) ~/Local\ Sites/YOUR_SITE/app/public/wp-content/plugins/thirdweb-woocommerce-checkout

   # MAMP example:
   ln -s $(pwd) /Applications/MAMP/htdocs/wordpress/wp-content/plugins/thirdweb-woocommerce-checkout
   ```

   **Option B: Copy files**
   ```bash
   # From the plugin directory:
   cp -r . ~/Local\ Sites/YOUR_SITE/app/public/wp-content/plugins/thirdweb-woocommerce-checkout
   ```

4. **Activate the plugin**:
   - Go to **WordPress Admin → Plugins**
   - Find "thirdweb Stablecoin Checkout for WooCommerce"
   - Click **Activate**

5. **Configure payment settings**:
   - Navigate to **WooCommerce → Settings → Payments**
   - Click **Manage** next to "Stablecoin Payment"
   - Enter your Client ID, wallet address, chain ID, and token address
   - Save changes

6. **Create a test product and complete checkout**:
   - Create a simple product in WooCommerce
   - Add it to cart and proceed to checkout
   - Select "Pay with Stablecoin" as payment method
   - Complete the payment flow

### Common Token Addresses (Mainnet)

| Chain | Chain ID | USDC | Native Token |
|-------|----------|------|--------------|
| Base | 8453 | `0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913` | ETH |
| Ethereum | 1 | `0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48` | ETH |
| Arbitrum | 42161 | `0xaf88d065e77c8cC2239327C5EDb3A432268e5831` | ETH |
| Polygon | 137 | `0x3c499c542cEF5E3811e1192ce70d8cC03d5c3359` | MATIC |
| Optimism | 10 | `0x0b2C639c533813f4Aa9D7837CAf62653d097Ff85` | ETH |

## How It Works

1. **Customer selects "Pay with Stablecoin"** at checkout
2. **CheckoutWidget renders** with order total and merchant wallet
3. **Customer connects wallet** (or pays with card via thirdweb)
4. **Payment completes on-chain** → CheckoutWidget fires `onSuccess` callback
5. **Transaction hash captured** and passed to WooCommerce
6. **Order marked complete** with transaction hash in order notes

## Distribution & Releases

### For Users

Download the latest release from:
- **GitHub Releases**: [Latest Release](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/releases/latest)
- **WordPress.org**: Coming soon

### For Maintainers

#### Creating a Release

1. **Update version numbers**:
   - `thirdweb-woocommerce-checkout.php` - Plugin header
   - `readme.txt` - Stable tag and changelog
   - `THIRDWEB_WC_VERSION` constant

2. **Build and test**:
   ```bash
   pnpm run build
   ./package.sh VERSION
   ```

3. **Create and push tag**:
   ```bash
   git tag -a vVERSION -m "Release version VERSION"
   git push origin vVERSION
   ```

4. **GitHub Actions** automatically:
   - Builds the plugin
   - Creates GitHub release
   - Uploads distribution ZIP

5. **WordPress.org** (manual):
   - Update SVN repository
   - Deploy to WordPress.org

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed release process.

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for:
- Development setup
- Code style guidelines
- Pull request process
- Testing requirements

## Support & Community

- 📖 **Documentation**: [Installation Guide](INSTALLATION.md)
- 💬 **Discord**: [thirdweb Community](https://discord.gg/thirdweb)
- 🐛 **Issues**: [GitHub Issues](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/issues)
- 📚 **thirdweb Docs**: [portal.thirdweb.com](https://portal.thirdweb.com)

## License

Licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for details.

## Credits

Built by [thirdweb](https://thirdweb.com) - Making Web3 development simple and accessible.

Powered by:
- [thirdweb SDK](https://portal.thirdweb.com/typescript/v5)
- [WooCommerce](https://woocommerce.com)
- [WordPress](https://wordpress.org)
