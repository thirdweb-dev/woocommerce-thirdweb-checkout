=== thirdweb Stablecoin Checkout for WooCommerce ===
Contributors: thirdweb
Tags: woocommerce, payment gateway, cryptocurrency, stablecoin, blockchain, web3, usdc, usdt, thirdweb
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: Apache-2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0
WC requires at least: 8.0
WC tested up to: 9.0

Accept stablecoin payments (USDC, USDT) in your WooCommerce store using thirdweb's CheckoutWidget. Support for multiple blockchains including Base, Ethereum, Polygon, and Arbitrum.

== Description ==

**Accept Crypto Payments in Your WooCommerce Store**

The thirdweb Stablecoin Checkout plugin enables your WooCommerce store to accept cryptocurrency payments using popular stablecoins like USDC and USDT across multiple blockchain networks.

Built on [thirdweb's](https://thirdweb.com) powerful Web3 infrastructure, this plugin provides a seamless checkout experience with support for:

* **Multiple Blockchains**: Base, Ethereum, Polygon, Arbitrum, Optimism, and more
* **Popular Stablecoins**: USDC, USDT, and other ERC-20 tokens
* **Flexible Payment Options**: Crypto wallets (MetaMask, Coinbase Wallet, WalletConnect) and credit cards
* **WooCommerce Blocks**: Full support for the modern block-based checkout experience
* **Secure Transactions**: All payments are processed on-chain with cryptographic verification
* **Real-time Status**: Customers see payment confirmation instantly

= Key Features =

* **Easy Setup**: Configure in minutes with just your thirdweb Client ID and wallet address
* **No Custody**: Payments go directly to your wallet - you maintain full control of your funds
* **Low Fees**: Blockchain transaction fees only, no middleman taking a cut
* **Global Payments**: Accept payments from anywhere in the world 24/7
* **Modern UX**: Beautiful, responsive checkout widget that works on all devices
* **Developer Friendly**: Built with React and TypeScript, fully customizable

= Supported Blockchains =

* **Base** (Chain ID: 8453)
* **Ethereum** (Chain ID: 1)
* **Polygon** (Chain ID: 137)
* **Arbitrum** (Chain ID: 42161)
* **Optimism** (Chain ID: 10)
* And more!

= How It Works =

1. Customer selects "Pay with Stablecoin" at checkout
2. Checkout widget displays with the order total
3. Customer connects their wallet or pays with card
4. Payment is processed on-chain
5. Order is automatically marked as complete in WooCommerce

= Requirements =

* A free [thirdweb account](https://thirdweb.com/dashboard)
* A blockchain wallet to receive payments
* WooCommerce 8.0 or higher
* WordPress 6.0 or higher
* PHP 7.4 or higher

= Privacy & Security =

This plugin does not collect or store any personal customer data. All payment processing happens through thirdweb's secure infrastructure. Transaction data is recorded on the blockchain and is publicly visible.

= Developer Resources =

* [GitHub Repository](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout)
* [thirdweb Documentation](https://portal.thirdweb.com/connect/checkout)
* [API Reference](https://portal.thirdweb.com/typescript/v5)

== Installation ==

= Automatic Installation (COMING SOON)=

1. Log in to your WordPress admin panel
2. Navigate to **Plugins → Add New**
3. Search for "thirdweb Stablecoin Checkout"
4. Click **Install Now** and then **Activate**
5. Go to **WooCommerce → Settings → Payments**
6. Enable **Stablecoin Payment** and click **Manage**
7. Enter your thirdweb Client ID and Seller Wallet address
8. Save changes

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins → Add New → Upload Plugin**
4. Choose the ZIP file and click **Install Now**
5. Click **Activate Plugin**
6. Follow steps 5-8 from Automatic Installation above

= Configuration =

After installation, you need to configure the plugin:

1. **Get thirdweb Client ID**:
   * Go to [thirdweb.com/dashboard](https://thirdweb.com/dashboard)
   * Create a free account or log in
   * Create a new project
   * Copy your **Client ID** from the project settings

2. **Seller Wallet Address**:
   * This is the blockchain wallet address where you want to receive payments
   * You can use any Ethereum-compatible wallet address (MetaMask, Coinbase Wallet, etc.)
   * Make sure you control this wallet and have access to the private keys

3. **Choose Blockchain Network**:
   * Select the blockchain where you want to receive payments
   * We recommend **Base** for low fees and fast transactions
   * Make sure your wallet is configured for the selected network

4. **Optional - Token Address**:
   * Leave empty to accept any stablecoin
   * Or specify a token address to only accept a specific token (e.g., USDC)

For detailed setup instructions, see the [Installation Guide](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/blob/main/INSTALLATION.md).

== Frequently Asked Questions ==

= Do I need a thirdweb account? =

Yes, you need a free thirdweb account to get your Client ID. Sign up at [thirdweb.com/dashboard](https://thirdweb.com/dashboard).

= Is there a monthly fee? =

No! The plugin is free to use. Developers can subscribe to the right account for their expected usage [Pricing](https://thirdweb.com/pricing)

= What tokens can I accept? =

You can accept USDC, USDT, and other stablecoins/erc20's on supported networks. The checkout widget automatically detects which tokens the customer has available in their wallet.

= Do payments go directly to my wallet? =

Yes! Payments are sent directly to your wallet address on-chain. thirdweb never holds or custodies your funds.

= What happens if a customer's payment fails? =

If a payment fails, the customer will see an error message and can try again. The WooCommerce order will remain in "Pending payment" status until successful payment.

= Can customers pay with credit cards? =

Yes! The thirdweb CheckoutWidget supports both crypto wallet payments and credit card payments (card payments are converted to crypto automatically).

= Which wallets are supported? =

The widget supports all major wallets including MetaMask, Coinbase Wallet, WalletConnect, Rainbow, and many more.

= Is this compatible with WooCommerce Blocks? =

Yes! The plugin fully supports the modern block-based checkout experience as well as the classic WooCommerce checkout.

= Can I use this on testnet for testing? =

Yes, you can configure the plugin to use testnets like Base Sepolia or Ethereum Goerli. Just enter the testnet chain ID and use testnet tokens for testing.

= How do I verify a payment was received? =

You can verify payments by:
* Checking your wallet balance on-chain
* Viewing the transaction on a block explorer (Etherscan, Basescan, etc.)
* Checking the WooCommerce order notes (transaction hash is recorded)

= What if I need help? =

* Check the [Installation Guide](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/blob/main/INSTALLATION.md)
* Visit [thirdweb Discord](https://discord.gg/thirdweb) for community support
* Review [thirdweb documentation](https://portal.thirdweb.com)
* Open an issue on [GitHub](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/issues)

== Screenshots ==

1. Checkout widget displayed at WooCommerce checkout
2. Customer connecting their wallet
3. Payment confirmation screen
4. Plugin settings page in WooCommerce admin
5. Order details showing transaction hash

== Changelog ==

= 1.0.0 - 2024-12-15 =
* Initial release
* Support for USDC and USDT payments
* Multiple blockchain support (Base, Ethereum, Polygon, Arbitrum, Optimism)
* WooCommerce Blocks integration
* Wallet and credit card payment options
* Direct on-chain payment processing
* Transaction hash recording in order notes

== Upgrade Notice ==

= 1.0.0 =
Initial release of the thirdweb Stablecoin Checkout plugin.

== Third-Party Services ==

This plugin relies on the following third-party services:

= thirdweb API =
* **Service**: thirdweb infrastructure for Web3 functionality
* **Usage**: Payment processing, wallet connections, blockchain interactions
* **Website**: [https://thirdweb.com](https://thirdweb.com)
* **Privacy Policy**: [https://thirdweb.com/privacy](https://thirdweb.com/privacy)
* **Terms of Service**: [https://thirdweb.com/tos](https://thirdweb.com/tos)

Data transmitted to thirdweb includes:
* Order amounts for payment processing
* Blockchain addresses (customer wallets and merchant wallet)
* Transaction data processed through blockchain networks

All payment data is processed on public blockchains where transactions are permanently recorded and publicly visible.

== Support ==

For support, please:

* Check the [documentation](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout)
* Join the [thirdweb Discord](https://discord.gg/thirdweb)
* Open an issue on [GitHub](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/issues)

== Contributing ==

We welcome contributions! Please visit our [GitHub repository](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout) to:

* Report bugs
* Suggest features
* Submit pull requests
* Review code

== License ==

This plugin is licensed under the Apache License 2.0. See [LICENSE](https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout/blob/main/LICENSE) for details.
