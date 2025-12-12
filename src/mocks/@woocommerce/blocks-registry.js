/**
 * Mock implementation of @woocommerce/blocks-registry for build purposes
 * 
 * This is a stub that allows the build to complete.
 * The actual implementation is provided by WooCommerce core at runtime.
 */

export function registerPaymentMethod(config) {
    // Stub implementation - actual registration happens in WooCommerce
    if (typeof window !== 'undefined' && window.console) {
        console.warn('registerPaymentMethod called during build - this is expected');
    }
}

