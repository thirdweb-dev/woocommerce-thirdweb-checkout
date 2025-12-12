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
 * Register webhook endpoint for payment verification
 */
add_action('rest_api_init', function() {
    register_rest_route('thirdweb-wc/v1', '/webhook', [
        'methods' => 'POST',
        'callback' => 'thirdweb_wc_handle_webhook',
        'permission_callback' => '__return_true', // Webhook verification happens in callback
    ]);
});

/**
 * Handle incoming webhooks from thirdweb
 */
function thirdweb_wc_handle_webhook(WP_REST_Request $request) {
    $payload = $request->get_json_params();
    
    // Verify webhook signature (implement based on thirdweb webhook docs)
    $signature = $request->get_header('x-thirdweb-signature');
    $gateway = new WC_Thirdweb_Payment_Gateway();
    
    if (!$gateway->verify_webhook_signature($payload, $signature)) {
        return new WP_REST_Response(['error' => 'Invalid signature'], 401);
    }

    // Process the payment confirmation
    if (isset($payload['event']) && $payload['event'] === 'payment.completed') {
        $order_id = $payload['purchaseData']['orderId'] ?? null;
        $tx_hash = $payload['transactionHash'] ?? null;

        if ($order_id && $tx_hash) {
            $order = wc_get_order($order_id);
            if ($order && $order->get_status() === 'pending') {
                $order->payment_complete($tx_hash);
                $order->add_order_note(
                    sprintf('Payment completed via thirdweb. Transaction: %s', $tx_hash)
                );
            }
        }
    }

    return new WP_REST_Response(['success' => true], 200);
}
