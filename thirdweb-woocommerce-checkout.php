<?php
/**
 * Plugin Name: thirdweb Stablecoin Checkout for WooCommerce
 * Description: Accept stablecoin payments (USDC, USDT) via thirdweb CheckoutWidget
 * Version: 1.0.0
 * Author: Your Name
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 */

defined('ABSPATH') || exit;

define('THIRDWEB_WC_VERSION', '1.0.0');
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
                <strong>Create a thirdweb account:</strong>
                Go to <a href="https://thirdweb.com/dashboard" target="_blank">thirdweb.com/dashboard</a>
                to sign up or log in
            </li>
            <li>
                <strong>Create a new project:</strong>
                In the dashboard, create a new project and copy your <strong>Client ID</strong>
            </li>
            <li>
                <strong>Get your project wallet address:</strong>
                From your project, copy the <strong>wallet address</strong> that will receive funds
            </li>
            <li>
                <strong>Configure the plugin:</strong>
                Go to <a href="<?php echo esc_url($settings_url); ?>">WooCommerce → Settings → Payments → Stablecoin Payment</a>
                and enter your Client ID and wallet address
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
