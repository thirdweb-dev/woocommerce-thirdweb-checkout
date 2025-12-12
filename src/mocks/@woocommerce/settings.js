/**
 * Mock implementation of @woocommerce/settings for build purposes
 * 
 * This is a stub that allows the build to complete.
 * The actual implementation is provided by WooCommerce core at runtime.
 */

export function getSetting(name, defaultValue) {
    // Stub implementation - actual settings come from WooCommerce
    if (typeof window !== 'undefined' && window.console) {
        console.warn(`getSetting called during build for "${name}" - this is expected`);
    }
    return defaultValue;
}

export function setSetting(name, value) {
    // Stub implementation
    if (typeof window !== 'undefined' && window.console) {
        console.warn(`setSetting called during build for "${name}" - this is expected`);
    }
}

