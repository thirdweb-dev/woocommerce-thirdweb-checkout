/**
 * Type definitions for @woocommerce/settings
 * 
 * This package is provided by WooCommerce core at runtime.
 * These type definitions are for development purposes only.
 */

declare module '@woocommerce/settings' {
    export function getSetting<T = any>(
        name: string,
        defaultValue?: T,
        filter?: (value: T) => T
    ): T;

    export function setSetting(name: string, value: any): void;
}

