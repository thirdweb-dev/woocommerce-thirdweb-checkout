<?php
/**
 * Plugin Name: thirdweb Stablecoin Checkout for WooCommerce
 * Plugin URI: https://github.com/thirdweb-dev/woocommerce-thirdweb-checkout
 * Description: Accept stablecoin payments (USDC, USDT) via thirdweb CheckoutWidget. Support for multiple blockchains including Base, Ethereum, Polygon, and Arbitrum.
 * Version: 1.1.0
 * Author: thirdweb
 * Author URI: https://thirdweb.com
 * License: Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: thirdweb-woocommerce-checkout
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 *
 * Copyright 2024 thirdweb
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

defined('ABSPATH') || exit;

define('THIRDWEB_WC_VERSION', '1.1.0');
define('THIRDWEB_WC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('THIRDWEB_WC_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load environment variables from .env file
 * 
 * @param string $key Environment variable key
 * @param mixed $default Default value if not found
 * @return mixed Environment variable value or default
 */
function thirdweb_wc_get_env($key, $default = '') {
    static $env_cache = null;
    
    // Load .env file once and cache it
    if ($env_cache === null) {
        $env_cache = [];
        $env_file = THIRDWEB_WC_PLUGIN_DIR . '.env';
        
        if (file_exists($env_file) && is_readable($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Parse KEY=VALUE format
                if (strpos($line, '=') !== false) {
                    list($env_key, $env_value) = explode('=', $line, 2);
                    $env_key = trim($env_key);
                    $env_value = trim($env_value);
                    
                    // Remove quotes if present
                    $env_value = trim($env_value, '"\'');
                    
                    $env_cache[$env_key] = $env_value;
                }
            }
        }
    }
    
    return $env_cache[$key] ?? $default;
}

/**
 * Check if WooCommerce is active
 */
function thirdweb_wc_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p><strong>thirdweb Checkout</strong> requires WooCommerce to be installed and active.</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * Initialize the plugin
 */
function thirdweb_wc_init() {
    if (!thirdweb_wc_check_woocommerce()) {
        return;
    }

    // Load the payment gateway class
    require_once THIRDWEB_WC_PLUGIN_DIR . 'includes/class-thirdweb-payment-gateway.php';

    // Register the payment gateway
    add_filter('woocommerce_payment_gateways', function($gateways) {
        $gateways[] = 'WC_Thirdweb_Payment_Gateway';
        return $gateways;
    });

    // Load block support for checkout blocks
    add_action('woocommerce_blocks_loaded', function() {
        require_once THIRDWEB_WC_PLUGIN_DIR . 'includes/class-thirdweb-blocks-support.php';

        // Register with blocks payment method registry
        add_action('woocommerce_blocks_payment_method_type_registration', function($payment_method_registry) {
            $payment_method_registry->register(new WC_Thirdweb_Blocks_Support());
        });
    });
}
add_action('plugins_loaded', 'thirdweb_wc_init');

/**
 * Declare HPOS compatibility
 */
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Activation hook - show onboarding notice
 */
register_activation_hook(__FILE__, function() {
    set_transient('thirdweb_wc_activation_notice', true, 60);
});

/**
 * Display activation notice with onboarding steps
 */
add_action('admin_notices', function() {
    if (!get_transient('thirdweb_wc_activation_notice')) {
        return;
    }

    delete_transient('thirdweb_wc_activation_notice');

    $settings_url = admin_url('admin.php?page=wc-settings&tab=checkout&section=thirdweb_stablecoin');
    ?>
    <div class="notice notice-success is-dismissible">
        <h2>🎉 thirdweb Stablecoin Checkout Activated!</h2>
        <p><strong>Follow these steps to start accepting stablecoin payments:</strong></p>
        <ol style="margin-left: 20px; line-height: 1.8;">
            <li>
                <strong>Get your wallet address:</strong>
                Use any Ethereum-compatible wallet (MetaMask, Coinbase Wallet, etc.) and copy your <strong>wallet address</strong> that will receive payments
            </li>
            <li>
                <strong>Configure the plugin:</strong>
                Go to <a href="<?php echo esc_url($settings_url); ?>">WooCommerce → Settings → Payments → Stablecoin Payment</a>
                and enter your wallet address and preferred blockchain network
            </li>
            <li>
                <strong>No thirdweb account needed!</strong>
                The plugin uses thirdweb's hosted checkout widget, so you don't need to sign up or get a Client ID.
            </li>
        </ol>
        <p>
            <a href="<?php echo esc_url($settings_url); ?>" class="button button-primary">
                Configure Settings Now
            </a>
            <a href="https://portal.thirdweb.com/connect/checkout" target="_blank" class="button">
                View Documentation
            </a>
        </p>
    </div>
    <?php
});
