/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_THIRDWEB_CLIENT_ID: string;
  readonly VITE_SELLER_WALLET: string;
  readonly VITE_CHAIN_ID: string;
  readonly VITE_TOKEN_ADDRESS: string;
  readonly VITE_AMOUNT: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
