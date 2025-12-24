<?php
/**
 * thirdweb Payment Gateway for WooCommerce
 */

defined('ABSPATH') || exit;

class WC_Thirdweb_Payment_Gateway extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'thirdweb_stablecoin';
        $this->icon               = THIRDWEB_WC_PLUGIN_URL . 'assets/icon.svg';
        $this->has_fields         = true;
        $this->method_title       = __('Stablecoin Payment', 'thirdweb-wc');
        $this->method_description = __('Accept USDC, USDT and other stablecoins via thirdweb', 'thirdweb-wc');
        
        // Supports
        $this->supports = [
            'products',
            'refunds',
        ];

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        // Define user settings
        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->enabled      = $this->get_option('enabled');
        
        // thirdweb specific settings (no Client ID needed for iframe widget)
        $this->seller_wallet  = $this->get_option('seller_wallet');
        $this->chain_id       = $this->get_option('chain_id');
        $this->token_address  = $this->get_option('token_address');
        $this->theme          = $this->get_option('theme', 'dark');

        // Save settings hook
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     * Admin settings fields
     */
    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => __('Enable/Disable', 'thirdweb-wc'),
                'type'    => 'checkbox',
                'label'   => __('Enable Stablecoin Payments', 'thirdweb-wc'),
                'default' => 'no',
            ],
            'title' => [
                'title'       => __('Title', 'thirdweb-wc'),
                'type'        => 'text',
                'description' => __('Payment method title shown at checkout', 'thirdweb-wc'),
                'default'     => __('Pay with Stablecoin (USDC/USDT)', 'thirdweb-wc'),
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __('Description', 'thirdweb-wc'),
                'type'        => 'textarea',
                'description' => __('Payment method description shown at checkout', 'thirdweb-wc'),
                'default'     => __('Pay securely with USDC, USDT or other stablecoins from any wallet.', 'thirdweb-wc'),
            ],
            'seller_wallet' => [
                'title'       => __('Seller Wallet Address', 'thirdweb-wc'),
                'type'        => 'text',
                'description' => __('Your wallet address that will receive all payments. Use any Ethereum-compatible wallet (MetaMask, Coinbase Wallet, etc.).', 'thirdweb-wc'),
                'default'     => '',
                'placeholder' => __('0x...', 'thirdweb-wc'),
                'custom_attributes' => [
                    'pattern' => '^0x[a-fA-F0-9]{40}$',
                ],
            ],
            'chain_id' => [
                'title'       => __('Chain ID', 'thirdweb-wc'),
                'type'        => 'text',
                'description' => sprintf(
                    __('The blockchain network chain ID to receive payments on. Default is 8453 (Base). Common chains: 1 (Ethereum), 8453 (Base), 137 (Polygon), 42161 (Arbitrum), 10 (Optimism). See <a href="%s" target="_blank">chainlist.org</a> for more chains.', 'thirdweb-wc'),
                    'https://chainlist.org'
                ),
                'default'     => '8453', // Base
                'placeholder' => __('8453', 'thirdweb-wc'),
                'custom_attributes' => [
                    'pattern' => '[0-9]+',
                ],
            ],
            'token_address' => [
                'title'       => __('Token Address (Optional)', 'thirdweb-wc'),
                'type'        => 'text',
                'description' => __('USDC/USDT contract address for the chain above. Make sure the token address matches your selected chain. Leave empty to accept any stablecoin. Default is USDC on Base (chain 8453).', 'thirdweb-wc'),
                'default'     => '0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913', // USDC on Base
                'placeholder' => __('0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913', 'thirdweb-wc'),
            ],
            'theme' => [
                'title'       => __('Widget Theme', 'thirdweb-wc'),
                'type'        => 'select',
                'description' => __('Choose the theme for the checkout widget. Default is dark.', 'thirdweb-wc'),
                'default'     => 'dark',
                'options'     => [
                    'dark'  => __('Dark', 'thirdweb-wc'),
                    'light' => __('Light', 'thirdweb-wc'),
                ],
                'desc_tip'    => true,
            ],
        ];
    }

    /**
     * Payment fields shown at checkout (for legacy/shortcode checkout)
     */
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
        
        // Container for iframe checkout widget (legacy checkout)
        $amount = WC()->cart->get_total('edit');
        $params = [
            'chain' => $this->chain_id,
            'amount' => $amount,
            'seller' => $this->seller_wallet,
        ];
        
        // Only add tokenAddress if provided
        if (!empty($this->token_address)) {
            $params['tokenAddress'] = $this->token_address;
        }
        
        // Add theme (default is dark, but allow override)
        $params['theme'] = $this->theme;
        
        $iframe_url = 'https://thirdweb.com/bridge/checkout-widget?' . http_build_query($params);
        
        echo '<div id="thirdweb-checkout-widget">';
        echo '<iframe src="' . esc_url($iframe_url) . '" height="700px" width="100%" style="border: 0;" title="thirdweb Checkout Widget"></iframe>';
        echo '</div>';
    }

    /**
     * Validate payment fields
     * 
     * Note: For WooCommerce Blocks, validation happens in the React component.
     * This method is mainly for legacy checkout.
     */
    public function validate_fields() {
        // Always return true - validation is handled by:
        // 1. React component for Blocks (checks paymentComplete before allowing submission)
        // 2. Frontend JavaScript for legacy checkout
        return true;
    }

    /**
     * Process the payment
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        // Get chain ID from payment data (WooCommerce Blocks sends it in payment_data array)
        $chain_id = $this->chain_id;
        
        if (isset($_POST['payment_data']) && is_array($_POST['payment_data'])) {
            foreach ($_POST['payment_data'] as $data) {
                if (isset($data['key']) && $data['key'] === 'thirdweb_chain_id') {
                    $chain_id = sanitize_text_field($data['value'] ?? $this->chain_id);
                    break;
                }
            }
        }
        
        // Fallback to legacy format
        if ($chain_id === $this->chain_id && isset($_POST['thirdweb_chain_id'])) {
            $chain_id = sanitize_text_field($_POST['thirdweb_chain_id']);
        }

        // Payment was completed via checkout widget - trust thirdweb's confirmation
        $order->payment_complete();
        $order->add_order_note(
            sprintf(
                __('Stablecoin payment completed via thirdweb checkout widget. Chain: %s', 'thirdweb-wc'),
                $chain_id
            )
        );
        
        WC()->cart->empty_cart();
        
        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        ];
    }

    /**
     * Get config for frontend
     */
    public function get_frontend_config() {
        return [
            'seller'       => $this->seller_wallet,
            'chainId'      => (int) $this->chain_id,
            'tokenAddress' => $this->token_address,
            'theme'        => $this->theme,
            'title'        => $this->title,
            'description'  => $this->description,
        ];
    }
}
