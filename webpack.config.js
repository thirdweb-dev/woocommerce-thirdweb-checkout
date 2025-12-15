/**
 * Webpack configuration for WooCommerce Blocks extension
 *
 * Extends @wordpress/scripts default config to handle WooCommerce packages
 * that are provided by WooCommerce core at runtime.
 */

const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    externals: {
        ...defaultConfig.externals,
        '@woocommerce/blocks-registry': ['wc', 'wcBlocksRegistry'],
        '@woocommerce/settings': ['wc', 'wcSettings'],
    },
};

