# WooCommerce Stablecoin Checkout with thirdweb CheckoutWidget

A WordPress/WooCommerce plugin that adds stablecoin payment support using thirdweb's CheckoutWidget.

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
├── .env.example                         # Environment variables template
├── .env                                 # Your environment variables (create from .env.example)
├── package.json
├── tsconfig.json
└── webpack.config.js
```

## Installation & Setup

### 1. Install Dependencies

```bash
pnpm install
```

### 2. Configure Environment Variables

Copy the example environment file and add your thirdweb Client ID:

```bash
cp .env.example .env
```

Edit `.env` and add your thirdweb Client ID:

```env
THIRDWEB_CLIENT_ID=your_client_id_here
```

You can get your Client ID from the [thirdweb Dashboard](https://thirdweb.com/dashboard).

### 3. Build the Plugin

```bash
pnpm run build
```

### 4. Configure in WooCommerce

1. Go to **WooCommerce → Settings → Payments**
2. Find **Stablecoin Payment** and click **Manage**
3. Enable the payment method
4. Enter your configuration (Client ID will be pre-filled from `.env` if set)
5. Save changes

## How It Works

1. **Customer selects "Pay with Stablecoin"** at checkout
2. **CheckoutWidget renders** with order total and merchant wallet
3. **Customer connects wallet** (or pays with card via thirdweb)
4. **Payment completes on-chain** → thirdweb webhook fires
5. **PHP verifies transaction** and marks order complete
