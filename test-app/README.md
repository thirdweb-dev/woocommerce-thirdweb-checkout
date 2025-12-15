# thirdweb CheckoutWidget Test App

A standalone React application for testing the thirdweb CheckoutWidget before WordPress integration.

## Quick Start

1. **Install dependencies:**
   ```bash
   cd test-app
   pnpm install
   ```

2. **Configure your credentials:**
   ```bash
   cp .env.example .env
   ```

   Edit `.env` with your thirdweb credentials:
   - `VITE_THIRDWEB_CLIENT_ID` - Get from [thirdweb.com/dashboard](https://thirdweb.com/dashboard)
   - `VITE_SELLER_WALLET` - Your wallet address to receive payments
   - `VITE_CHAIN_ID` - Blockchain network (8453 for Base, 1 for Ethereum, etc.)
   - `VITE_TOKEN_ADDRESS` - Token contract address (optional, leave empty for native ETH)
   - `VITE_AMOUNT` - Test amount in USD

3. **Run the development server:**
   ```bash
   pnpm dev
   ```

   The app will automatically open at `http://localhost:3000`

## What This Tests

- thirdweb CheckoutWidget integration
- Payment flow with crypto wallets (MetaMask, Coinbase, WalletConnect)
- Payment flow with credit/debit cards
- Success/error/cancel callbacks
- Transaction hash capture
- Block explorer integration

## Configuration Examples

### Base Mainnet with USDC
```env
VITE_THIRDWEB_CLIENT_ID=your_client_id
VITE_SELLER_WALLET=0xYourWalletAddress
VITE_CHAIN_ID=8453
VITE_TOKEN_ADDRESS=0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913
VITE_AMOUNT=10.00
```

### Ethereum Mainnet with Native ETH
```env
VITE_THIRDWEB_CLIENT_ID=your_client_id
VITE_SELLER_WALLET=0xYourWalletAddress
VITE_CHAIN_ID=1
VITE_TOKEN_ADDRESS=
VITE_AMOUNT=10.00
```

## Supported Networks

| Chain | Chain ID | USDC Token Address |
|-------|----------|-------------------|
| Base | 8453 | `0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913` |
| Ethereum | 1 | `0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48` |
| Arbitrum | 42161 | `0xaf88d065e77c8cC2239327C5EDb3A432268e5831` |
| Polygon | 137 | `0x3c499c542cEF5E3811e1192ce70d8cC03d5c3359` |
| Optimism | 10 | `0x0b2C639c533813f4Aa9D7837CAf62653d097Ff85` |

## Building for Production

```bash
pnpm build
pnpm preview
```

## Notes

- The `.env` file is gitignored to keep your credentials safe
- This is for testing only - use the WordPress plugin for production
- Transaction hashes are captured and can be viewed on block explorers
