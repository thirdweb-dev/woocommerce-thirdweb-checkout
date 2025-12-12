/**
 * Webpack configuration for WooCommerce Blocks extension
 * 
 * Extends @wordpress/scripts default config to handle WooCommerce packages
 * that are provided by WooCommerce core at runtime.
 */

const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            ...defaultConfig.resolve?.alias,
            '@woocommerce/blocks-registry': require.resolve('./src/mocks/@woocommerce/blocks-registry.js'),
            '@woocommerce/settings': require.resolve('./src/mocks/@woocommerce/settings.js'),
        },
    },
};

