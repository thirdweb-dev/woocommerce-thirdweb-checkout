/**
 * Type definitions for @woocommerce/blocks-registry
 * 
 * This package is provided by WooCommerce core at runtime.
 * These type definitions are for development purposes only.
 */

declare module '@woocommerce/blocks-registry' {
    export interface PaymentMethodConfig {
        name: string;
        label: React.ComponentType | string;
        content: React.ComponentType;
        edit?: React.ComponentType;
        canMakePayment?: () => boolean;
        ariaLabel?: string;
        supports?: {
            features?: string[];
        };
    }

    export function registerPaymentMethod(config: PaymentMethodConfig): void;
}

