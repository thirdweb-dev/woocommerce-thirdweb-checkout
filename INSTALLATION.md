# Installation Guide

Complete guide for installing and configuring the thirdweb Stablecoin Checkout plugin for WooCommerce.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation Methods](#installation-methods)
  - [Method 1: WordPress.org (Recommended)](#method-1-wordpressorg-recommended)
  - [Method 2: Manual Upload](#method-2-manual-upload)
  - [Method 3: GitHub Download](#method-3-github-download)
  - [Method 4: Development Install](#method-4-development-install)
- [Configuration](#configuration)
- [Testing Your Setup](#testing-your-setup)
- [Troubleshooting](#troubleshooting)
- [Next Steps](#next-steps)

---

## Prerequisites

Before installing the plugin, ensure you have:

### WordPress Requirements
- ✅ WordPress 6.0 or higher
- ✅ WooCommerce 8.0 or higher installed and activated
- ✅ PHP 7.4 or higher
- ✅ Admin access to your WordPress site

### thirdweb Requirements
- ✅ A free thirdweb account ([sign up here](https://thirdweb.com/dashboard))
- ✅ A thirdweb Client ID (obtained from dashboard)
- ✅ A blockchain wallet address to receive payments

### Optional (For Testing)
- A wallet with testnet tokens (MetaMask, Coinbase Wallet, etc.)
- Access to testnets like Base Sepolia or Ethereum Goerli

---

## Installation Methods

### Method 1: WordPress.org (Recommended)

**Best for:** Most users, automatic updates

1. **Log into WordPress Admin**
   - Navigate to your WordPress admin panel
   - URL format: `https://yoursite.com/wp-admin`

2. **Access Plugin Installer**
   - Go to **Plugins → Add New**
   - In the search box, type: `thirdweb stablecoin checkout`

3. **Install the Plugin**
   - Locate the plugin in search results
   - Click **Install Now**
   - Wait for installation to complete

4. **Activate the Plugin**
   - Click **Activate Plugin**
   - You'll see a success notification with setup instructions

5. **Proceed to [Configuration](#configuration)**

---

### Method 2: Manual Upload

**Best for:** Users who downloaded the plugin ZIP file

1. **Download the Plugin**
   - Download the latest release from [GitHub Releases](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/releases)
   - Save the `.zip` file to your computer

2. **Upload to WordPress**
   - Go to **Plugins → Add New**
   - Click **Upload Plugin** button at the top
   - Click **Choose File** and select the downloaded ZIP
   - Click **Install Now**

3. **Activate the Plugin**
   - After upload completes, click **Activate Plugin**

4. **Proceed to [Configuration](#configuration)**

---

### Method 3: GitHub Download

**Best for:** Advanced users who want the latest development version

1. **Clone or Download**
   ```bash
   # Clone the repository
   git clone https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout.git

   # Or download ZIP from GitHub
   # https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/archive/refs/heads/main.zip
   ```

2. **Build the Plugin** (if cloning from source)
   ```bash
   cd woocommerce-thirdweb-checkout

   # Install dependencies
   pnpm install

   # Build frontend assets
   pnpm run build
   ```

3. **Upload to WordPress**
   - Compress the plugin folder to ZIP (or use `./package.sh`)
   - Follow steps in [Method 2](#method-2-manual-upload)

---

### Method 4: Development Install

**Best for:** Developers working on the plugin

1. **Access Your WordPress Installation**
   ```bash
   cd /path/to/wordpress/wp-content/plugins/
   ```

2. **Clone the Repository**
   ```bash
   git clone https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout.git
   cd woocommerce-thirdweb-checkout
   ```

3. **Install Dependencies**
   ```bash
   pnpm install
   ```

4. **Configure Environment** (optional)
   ```bash
   cp .env.example .env
   # Edit .env and add your Client ID
   ```

5. **Build Frontend**
   ```bash
   # Production build
   pnpm run build

   # Or development watch mode
   pnpm run start
   ```

6. **Activate in WordPress**
   - Go to **Plugins** in WordPress admin
   - Find "thirdweb Stablecoin Checkout for WooCommerce"
   - Click **Activate**

---

## Configuration

After installation and activation, configure the plugin to accept payments.

### Step 1: Get Your thirdweb Client ID

1. **Visit thirdweb Dashboard**
   - Go to [thirdweb.com/dashboard](https://thirdweb.com/dashboard)
   - Sign up or log in to your account

2. **Create a Project** (if you don't have one)
   - Click **Create Project**
   - Give it a name (e.g., "My WooCommerce Store")
   - Click **Create**

3. **Copy Your Client ID**
   - Open your project
   - Go to **Settings** tab
   - Find the **Client ID** section
   - Click **Copy** to copy your Client ID
   - **Keep this secure** - you'll need it in the next step

### Step 2: Configure Plugin Settings

1. **Access Payment Settings**
   - In WordPress admin, go to **WooCommerce → Settings**
   - Click the **Payments** tab
   - Find **Stablecoin Payment** in the list
   - Click **Manage** (or toggle to enable first)

2. **Fill in Required Settings**

   | Setting | Description | Example |
   |---------|-------------|---------|
   | **Enable/Disable** | Toggle to enable the payment method | ☑️ Enabled |
   | **Title** | Name shown to customers at checkout | `Pay with Stablecoin` |
   | **Description** | Message shown to customers | `Pay securely with USDC, USDT, or other stablecoins` |
   | **thirdweb Client ID** | Your Client ID from thirdweb dashboard | `abc123def456...` |
   | **Seller Wallet Address** | Wallet where you receive payments | `0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb` |
   | **Chain ID** | Blockchain network for payments | `8453` (Base), `1` (Ethereum), etc. |
   | **Token Address** | Specific token address (optional) | Leave empty to accept any stablecoin |

3. **Save Changes**
   - Click **Save changes** at the bottom
   - You should see a success message

### Step 3: Choose Your Blockchain Network

Common chain IDs:

| Network | Chain ID | Recommended For | Gas Costs |
|---------|----------|-----------------|-----------|
| **Base** | `8453` | 🌟 Best for most stores | Very Low |
| **Polygon** | `137` | High volume transactions | Very Low |
| **Arbitrum** | `42161` | Lower fees than Ethereum | Low |
| **Optimism** | `10` | Fast transactions | Low |
| **Ethereum** | `1` | Maximum security | High |

**💡 Recommendation:** Use **Base (8453)** for the best balance of low fees and transaction speed.

### Step 4: Get Your Seller Wallet Address

Your seller wallet is where you'll receive customer payments.

**Using MetaMask:**
1. Open MetaMask extension
2. Click on your account name at the top
3. Your address is displayed (starts with `0x`)
4. Click to copy
5. Paste into the "Seller Wallet Address" setting

**Using Coinbase Wallet:**
1. Open Coinbase Wallet app
2. Tap **Receive**
3. Select **Ethereum** (or the network you're using)
4. Copy your wallet address
5. Paste into the plugin settings

**⚠️ Important:**
- Ensure you have access to this wallet and its private keys
- Never share your private keys with anyone
- Test with a small amount first
- Make sure your wallet supports the blockchain network you selected

### Step 5: Optional - Specify Token Address

By default, the checkout widget will accept any stablecoin the customer has in their wallet.

To accept only a specific token (e.g., USDC), add its contract address:

**Common Stablecoin Addresses:**

| Token | Base (8453) | Ethereum (1) | Polygon (137) | Arbitrum (42161) |
|-------|-------------|--------------|---------------|------------------|
| **USDC** | `0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913` | `0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48` | `0x3c499c542cEF5E3811e1192ce70d8cC03d5c3359` | `0xaf88d065e77c8cC2239327C5EDb3A432268e5831` |
| **USDT** | - | `0xdAC17F958D2ee523a2206206994597C13D831ec7` | `0xc2132D05D31c914a87C6611C10748AEb04B58e8F` | `0xFd086bC7CD5C481DCC9C85ebE478A1C0b69FCbb9` |

**💡 Tip:** Leave this field empty to maximize payment options for your customers.

---

## Testing Your Setup

### Test with a Real Purchase

1. **Add Product to Cart**
   - Visit your store as a customer
   - Add any product to cart
   - Proceed to checkout

2. **Select Payment Method**
   - At checkout, select **Pay with Stablecoin** (or your custom title)
   - You should see the thirdweb checkout widget load

3. **Connect Wallet**
   - Click **Connect Wallet**
   - Choose your wallet (MetaMask, Coinbase Wallet, etc.)
   - Approve the connection

4. **Complete Payment**
   - The widget will show available tokens in your wallet
   - Select a token and amount
   - Confirm the transaction in your wallet
   - Wait for confirmation (usually 1-10 seconds)

5. **Verify Order**
   - You should see "Payment complete" message
   - Click **Place Order** to complete checkout
   - Check WooCommerce admin → Orders
   - Order should be marked as "Processing" or "Completed"
   - Order notes will include the transaction hash

### Test on Testnet (Development)

For testing without real money:

1. **Use a Testnet Chain ID**
   - Base Sepolia: `84532`
   - Ethereum Goerli: `5`
   - Polygon Mumbai: `80001`

2. **Get Testnet Tokens**
   - Base Sepolia faucet: [docs.base.org/tools/testnet-faucets](https://docs.base.org/tools/testnet-faucets)
   - Ethereum Goerli faucet: [goerlifaucet.com](https://goerlifaucet.com)

3. **Configure Testnet Token Address**
   - Use testnet USDC addresses (search "[network] testnet USDC")

4. **Test the Flow**
   - Follow the same steps as real purchase
   - Transactions will be free (only testnet gas)

---

## Troubleshooting

### Widget Not Appearing at Checkout

**Problem:** Payment method shows but widget doesn't load

**Solutions:**
1. Clear browser cache and hard refresh (Ctrl+Shift+R / Cmd+Shift+R)
2. Check browser console for errors (F12 → Console tab)
3. Verify WooCommerce is using block-based checkout (not classic)
4. Ensure plugin is activated and settings are saved
5. Check that `build/index.tsx.js` file exists in plugin directory

### "No Available Tokens Found"

**Problem:** Widget loads but says no tokens available

**Solutions:**
1. Ensure your wallet has tokens on the correct network
2. Check that you're connected to the right network in your wallet
3. If using Token Address setting, verify it's correct for your network
4. Try leaving Token Address empty to accept any stablecoin
5. Ensure you have sufficient balance (order total + gas fees)

### "Unable to Connect Wallet"

**Problem:** Wallet connection fails

**Solutions:**
1. Check that your wallet extension is installed and unlocked
2. Try a different wallet (MetaMask, Coinbase Wallet, WalletConnect)
3. Clear wallet cache and reconnect
4. Ensure your Client ID is correct
5. Check browser console for specific error messages

### Order Not Completing

**Problem:** Payment succeeds but order stays "Pending"

**Solutions:**
1. Check WooCommerce order notes for transaction hash
2. Verify transaction on block explorer (Etherscan, Basescan, etc.)
3. Check that seller wallet address matches your actual wallet
4. Ensure funds arrived at your wallet address
5. Manually mark order as complete if payment verified

### Settings Page Not Accessible

**Problem:** "Sorry, you are not allowed to access this page" error

**Solutions:**
1. Ensure WooCommerce is installed and activated
2. Log in as an Administrator
3. Clear WordPress object cache if using caching plugins
4. Deactivate and reactivate the plugin

### Build Errors (Development)

**Problem:** `pnpm run build` fails

**Solutions:**
```bash
# Clear dependencies and rebuild
rm -rf node_modules pnpm-lock.yaml
pnpm install
pnpm run build

# Check Node version (needs 16+)
node --version

# Try with npm if pnpm fails
npm install
npm run build
```

### Getting Help

If you're still experiencing issues:

1. **Check Documentation**
   - [thirdweb Checkout Docs](https://portal.thirdweb.com/connect/checkout)
   - [GitHub Issues](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/issues)

2. **Community Support**
   - [thirdweb Discord](https://discord.gg/thirdweb)
   - #support channel for payment issues

3. **Open an Issue**
   - [GitHub Issues Page](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/issues/new)
   - Include: WordPress version, WooCommerce version, error messages, browser console logs

---

## Next Steps

### For Store Owners

✅ **Production Checklist:**
- [ ] Test with small amounts first
- [ ] Verify transactions arrive at your wallet
- [ ] Set up wallet security (hardware wallet recommended for large volumes)
- [ ] Add payment method to store policies
- [ ] Train support team on crypto payments
- [ ] Monitor transactions regularly
- [ ] Consider setting up Etherscan/Basescan alerts for your wallet

### For Developers

✅ **Customization Options:**
- [ ] Customize widget theme colors
- [ ] Add custom order metadata
- [ ] Implement multi-currency support
- [ ] Create custom email templates with transaction links
- [ ] Add analytics tracking
- [ ] Build admin dashboard for crypto payments

### Advanced Configuration

**Environment Variables** (optional, for development):
```bash
# Create .env file in plugin directory
THIRDWEB_CLIENT_ID=your_client_id_here
```

**Custom Checkout Widget Styling:**
Edit `src/checkout-block/ThirdwebCheckout.tsx` to customize the widget appearance.

**Webhook Integration** (future feature):
Currently, payment verification happens client-side. Webhook support for server-side verification is planned for future releases.

---

## Security Best Practices

1. **Wallet Security**
   - Use hardware wallet for high-volume stores
   - Never share private keys
   - Enable 2FA on wallet accounts
   - Regularly move funds to cold storage

2. **Client ID Security**
   - Don't commit `.env` files to Git (use `.env.example`)
   - Rotate Client IDs if exposed
   - Use different Client IDs for dev/staging/production

3. **WordPress Security**
   - Keep WordPress, WooCommerce, and plugins updated
   - Use strong admin passwords
   - Enable HTTPS (SSL certificate required)
   - Regular backups

---

## Resources

- 📚 [thirdweb Documentation](https://portal.thirdweb.com)
- 💬 [thirdweb Discord Community](https://discord.gg/thirdweb)
- 🐙 [GitHub Repository](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout)
- 🔍 [Base Block Explorer](https://basescan.org)
- 🔍 [Ethereum Block Explorer](https://etherscan.io)
- 📖 [WooCommerce Blocks Documentation](https://github.com/woocommerce/woocommerce-blocks)

---

**Need Help?** Join our [Discord community](https://discord.gg/thirdweb) or open an issue on [GitHub](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/issues).
