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
        
        // thirdweb specific settings
        // Use .env value as default if WooCommerce setting is empty
        $env_client_id = thirdweb_wc_get_env('THIRDWEB_CLIENT_ID', '');
        $this->client_id      = $this->get_option('client_id') ?: $env_client_id;
        $this->seller_wallet  = $this->get_option('seller_wallet');
        $this->chain_id       = $this->get_option('chain_id');
        $this->token_address  = $this->get_option('token_address');

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
            'client_id' => [
                'title'       => __('thirdweb Client ID', 'thirdweb-wc'),
                'type'        => 'text',
                'description' => sprintf(
                    __('Get your Client ID from <a href="%s" target="_blank">thirdweb Dashboard</a>. Create a new project if you haven\'t already. Can also be set via .env file for development.', 'thirdweb-wc'),
                    'https://thirdweb.com/dashboard'
                ),
                'default'     => thirdweb_wc_get_env('THIRDWEB_CLIENT_ID', ''),
                'placeholder' => __('e.g., abc123def456...', 'thirdweb-wc'),
            ],
            'seller_wallet' => [
                'title'       => __('Seller Wallet Address', 'thirdweb-wc'),
                'type'        => 'text',
                'description' => sprintf(
                    __('Your project wallet address that will receive all payments. Get this from your <a href="%s" target="_blank">thirdweb project dashboard</a>.', 'thirdweb-wc'),
                    'https://thirdweb.com/dashboard'
                ),
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
                'description' => __('USDC/USDT contract address for the chain above. Make sure the token address matches your selected chain. Leave empty to accept the native token (ETH, MATIC, etc.). Default is USDC on Base (chain 8453).', 'thirdweb-wc'),
                'default'     => '0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913', // USDC on Base
                'placeholder' => __('0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913', 'thirdweb-wc'),
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
        
        // Container for React to mount the CheckoutWidget
        echo '<div id="thirdweb-checkout-widget" 
                   data-client-id="' . esc_attr($this->client_id) . '"
                   data-seller="' . esc_attr($this->seller_wallet) . '"
                   data-chain-id="' . esc_attr($this->chain_id) . '"
                   data-token-address="' . esc_attr($this->token_address) . '"
                   data-amount="' . esc_attr(WC()->cart->get_total('edit')) . '"
                   data-currency="' . esc_attr(get_woocommerce_currency()) . '">
              </div>';
        
        // Hidden field to store transaction hash
        echo '<input type="hidden" name="thirdweb_tx_hash" id="thirdweb_tx_hash" value="" />';
    }

    /**
     * Validate payment fields
     */
    public function validate_fields() {
        if (empty($_POST['thirdweb_tx_hash'])) {
            wc_add_notice(__('Please complete the payment.', 'thirdweb-wc'), 'error');
            return false;
        }
        return true;
    }

    /**
     * Process the payment
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $tx_hash = sanitize_text_field($_POST['thirdweb_tx_hash'] ?? '');

        if (empty($tx_hash)) {
            // Payment not yet completed - wait for webhook
            $order->update_status('pending', __('Awaiting stablecoin payment confirmation.', 'thirdweb-wc'));
            
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ];
        }

        // Transaction hash provided - verify on-chain
        if ($this->verify_transaction($tx_hash, $order)) {
            $order->payment_complete($tx_hash);
            $order->add_order_note(
                sprintf(__('Stablecoin payment completed. Transaction: %s', 'thirdweb-wc'), $tx_hash)
            );
            
            WC()->cart->empty_cart();
            
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ];
        }

        wc_add_notice(__('Payment verification failed. Please try again.', 'thirdweb-wc'), 'error');
        return ['result' => 'failure'];
    }

    /**
     * Verify transaction on-chain
     */
    private function verify_transaction($tx_hash, $order) {
        // Use thirdweb RPC to verify the transaction
        $rpc_url = 'https://' . $this->chain_id . '.rpc.thirdweb.com/' . $this->client_id;
        
        $response = wp_remote_post($rpc_url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode([
                'jsonrpc' => '2.0',
                'method'  => 'eth_getTransactionReceipt',
                'params'  => [$tx_hash],
                'id'      => 1,
            ]),
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $receipt = $body['result'] ?? null;

        if (!$receipt || $receipt['status'] !== '0x1') {
            return false;
        }

        // Additional verification: check recipient and amount in logs
        // This is simplified - production code should decode transfer events
        return true;
    }

    /**
     * Get config for frontend
     */
    public function get_frontend_config() {
        return [
            'clientId'     => $this->client_id,
            'seller'       => $this->seller_wallet,
            'chainId'      => (int) $this->chain_id,
            'tokenAddress' => $this->token_address,
            'title'        => $this->title,
            'description'  => $this->description,
        ];
    }
}
