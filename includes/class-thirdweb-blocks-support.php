<?php
/**
 * WooCommerce Blocks Support for thirdweb Payment Gateway
 * 
 * This enables the payment method to work with the new 
 * React-based WooCommerce Checkout Blocks
 */

defined('ABSPATH') || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Register the block payment method type
 */
add_action('woocommerce_blocks_payment_method_type_registration', function($payment_method_registry) {
    $payment_method_registry->register(new WC_Thirdweb_Blocks_Support());
});

/**
 * Block support class
 */
class WC_Thirdweb_Blocks_Support extends AbstractPaymentMethodType {
    
    /**
     * Payment method name/id
     */
    protected $name = 'thirdweb_stablecoin';

    /**
     * Gateway instance
     */
    private $gateway;

    /**
     * Initialize
     */
    public function initialize() {
        $this->settings = get_option('woocommerce_thirdweb_stablecoin_settings', []);
        
        // Get gateway instance
        $gateways = WC()->payment_gateways->payment_gateways();
        $this->gateway = $gateways[$this->name] ?? null;
    }

    /**
     * Check if payment method is active
     */
    public function is_active() {
        return $this->gateway && $this->gateway->is_available();
    }

    /**
     * Register scripts for checkout block
     */
    public function get_payment_method_script_handles() {
        $asset_path = THIRDWEB_WC_PLUGIN_DIR . 'build/checkout-block.asset.php';
        $asset      = file_exists($asset_path) 
            ? require($asset_path) 
            : ['dependencies' => [], 'version' => THIRDWEB_WC_VERSION];

        wp_register_script(
            'thirdweb-wc-checkout-block',
            THIRDWEB_WC_PLUGIN_URL . 'build/checkout-block.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        // Pass PHP config to JavaScript
        wp_localize_script(
            'thirdweb-wc-checkout-block',
            'thirdwebWCConfig',
            $this->get_payment_method_data()
        );

        return ['thirdweb-wc-checkout-block'];
    }

    /**
     * Data passed to the React component via getSetting()
     */
    public function get_payment_method_data() {
        return [
            'title'        => $this->get_setting('title'),
            'description'  => $this->get_setting('description'),
            'supports'     => $this->get_supported_features(),
            
            // thirdweb configuration
            'clientId'     => $this->get_setting('client_id'),
            'seller'       => $this->get_setting('seller_wallet'),
            'chainId'      => (int) $this->get_setting('chain_id'),
            'tokenAddress' => $this->get_setting('token_address'),
            
            // Icons/branding
            'icon'         => THIRDWEB_WC_PLUGIN_URL . 'assets/icon.svg',
            
            // Supported stablecoins display
            'supportedTokens' => $this->get_supported_tokens(),
        ];
    }

    /**
     * Get supported features
     */
    public function get_supported_features() {
        return $this->gateway ? $this->gateway->supports : [];
    }

    /**
     * Get list of supported stablecoins for display
     */
    private function get_supported_tokens() {
        $chain_id = (int) $this->get_setting('chain_id');
        
        // Common stablecoin addresses by chain
        $tokens = [
            8453 => [ // Base
                ['symbol' => 'USDC', 'address' => '0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913'],
                ['symbol' => 'USDbC', 'address' => '0xd9aAEc86B65D86f6A7B5B1b0c42FFA531710b6CA'],
            ],
            1 => [ // Ethereum
                ['symbol' => 'USDC', 'address' => '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48'],
                ['symbol' => 'USDT', 'address' => '0xdAC17F958D2ee523a2206206994597C13D831ec7'],
            ],
            42161 => [ // Arbitrum
                ['symbol' => 'USDC', 'address' => '0xaf88d065e77c8cC2239327C5EDb3A432268e5831'],
                ['symbol' => 'USDT', 'address' => '0xFd086bC7CD5C481DCC9C85ebE478A1C0b69FCbb9'],
            ],
            137 => [ // Polygon
                ['symbol' => 'USDC', 'address' => '0x3c499c542cEF5E3811e1192ce70d8cC03d5c3359'],
                ['symbol' => 'USDT', 'address' => '0xc2132D05D31c914a87C6611C10748AEb04B58e8F'],
            ],
        ];
        
        return $tokens[$chain_id] ?? [];
    }
}
