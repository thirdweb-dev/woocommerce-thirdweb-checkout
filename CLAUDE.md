# WooCommerce thirdweb Stablecoin Checkout Plugin

## Project Overview

A WordPress/WooCommerce plugin that adds stablecoin payment support (USDC, USDT) using thirdweb's CheckoutWidget React component.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                  WooCommerce Checkout Page                       │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  Payment Methods                                           │  │
│  │  ○ Credit Card                                             │  │
│  │  ○ PayPal                                                  │  │
│  │  ● Pay with Stablecoin ← Our Plugin                        │  │
│  │    └─► CheckoutWidget (React) renders here                 │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┴───────────────┐
              ▼                               ▼
      PHP Gateway                      thirdweb Webhook
   (WC_Payment_Gateway)              (payment confirmation)
```

## Key Files

| File | Purpose |
|------|---------|
| `thirdweb-woocommerce-checkout.php` | Main plugin entry, registers gateway & webhook endpoint |
| `includes/class-thirdweb-payment-gateway.php` | WC_Payment_Gateway extension with admin settings |
| `includes/class-thirdweb-blocks-support.php` | Enables React-based checkout blocks support |
| `src/checkout-block/index.tsx` | Registers payment method with `@woocommerce/blocks-registry` |
| `src/checkout-block/ThirdwebCheckout.tsx` | Wraps thirdweb CheckoutWidget with WC integration |

## Tech Stack

- **PHP 7.4+**: WordPress plugin backend
- **WooCommerce 8.0+**: Payment gateway API
- **React/TypeScript**: Frontend checkout widget
- **@wordpress/scripts**: Build tooling (Webpack)
- **thirdweb SDK v5**: CheckoutWidget component

## Payment Flow

1. Customer selects "Pay with Stablecoin" at checkout
2. `ThirdwebCheckout.tsx` renders the CheckoutWidget with order total
3. Customer connects wallet (MetaMask/Coinbase/WalletConnect) or pays with card
4. Payment completes on-chain → CheckoutWidget fires `onSuccess` callback with transaction data
5. React component captures transaction hash and passes it to WooCommerce
6. PHP verifies transaction on-chain via RPC and marks order complete

## Build Commands

```bash
pnpm install         # Install dependencies
pnpm run build       # Production build → /build/checkout-block.js
pnpm run start       # Development watch mode
```

## Environment Configuration

The plugin supports configuration via a `.env` file for development convenience:

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Add your thirdweb Client ID:
   ```env
   THIRDWEB_CLIENT_ID=your_client_id_here
   ```

The Client ID from `.env` will be used as a default value in the WooCommerce admin settings. You can override it in the admin panel if needed.

## Configuration (WooCommerce Admin)

Settings at: WooCommerce → Settings → Payments → Stablecoin Payment

- **thirdweb Client ID**: From thirdweb dashboard
- **Seller Wallet**: Address to receive payments
- **Chain ID**: Blockchain network chain ID (default: 8453 for Base). Supports any EVM chain
- **Token Address**: USDC/USDT contract for the selected chain (optional, defaults to native token)

## CheckoutWidget Props Reference

```tsx
<CheckoutWidget
  client={client}                    // thirdweb client instance
  chain={chain}                      // Target chain (e.g., Base)
  amount="149.99"                    // Order total as string
  seller="0x..."                     // Merchant wallet address
  tokenAddress="0x..."               // USDC/USDT contract (optional)
  paymentMethods={['crypto', 'card']} // Enabled payment methods
  onSuccess={handleSuccess}          // Success callback - receives Status[] with transaction data
  onError={handleError}              // Error callback
  onCancel={handleCancel}            // Cancel callback
  theme="light"                      // UI theme
  supportedTokens={{...}}            // Tokens users can pay with
/>
```

## Common Token Addresses

| Chain | USDC | USDT |
|-------|------|------|
| Base (8453) | `0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913` | - |
| Ethereum (1) | `0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48` | `0xdAC17F958D2ee523a2206206994597C13D831ec7` |
| Arbitrum (42161) | `0xaf88d065e77c8cC2239327C5EDb3A432268e5831` | `0xFd086bC7CD5C481DCC9C85ebE478A1C0b69FCbb9` |
| Polygon (137) | `0x3c499c542cEF5E3811e1192ce70d8cC03d5c3359` | `0xc2132D05D31c914a87C6611C10748AEb04B58e8F` |

## Development Notes

- WooCommerce checkout blocks use React on frontend (not PHP server-rendered)
- `@woocommerce/blocks-registry` provides `registerPaymentMethod()` API
- `getSetting('thirdweb_stablecoin_data')` retrieves PHP config in React
- Transaction hash captured from CheckoutWidget's `onSuccess` callback
- Transaction verification happens server-side via RPC for security

## TODO

- [ ] Add refund support via thirdweb API
- [ ] Add transaction receipt email with block explorer link
- [ ] Support multiple tokens per checkout
- [ ] Add test mode toggle for testnet payments
- [ ] Enhanced on-chain transaction verification (amount, recipient validation)
