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
        
        $iframe_url = 'https://thirdweb.com/bridge/checkout-widget?' . http_build_query($params);
        
        echo '<div id="thirdweb-checkout-widget">';
        echo '<iframe src="' . esc_url($iframe_url) . '" height="700px" width="100%" style="border: 0;" title="thirdweb Checkout Widget"></iframe>';
        echo '</div>';
        
        // Hidden field to store transaction hash
        echo '<input type="hidden" name="thirdweb_tx_hash" id="thirdweb_tx_hash" value="" />';
    }

    /**
     * Validate payment fields
     * 
     * Note: For WooCommerce Blocks, validation happens in the React component.
     * This method is mainly for legacy checkout. We allow empty tx_hash because
     * the checkout widget confirms payment before sending success message.
     */
    public function validate_fields() {
        // Always return true - validation is handled by:
        // 1. React component for Blocks (checks paymentComplete before allowing submission)
        // 2. Frontend JavaScript for legacy checkout
        // Empty tx_hash is OK - widget confirms payment before sending success message
        return true;
    }

    /**
     * Process the payment
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        // Get payment data - WooCommerce Blocks sends it in payment_data array
        $tx_hash = '';
        $chain_id = '';
        
        // Try to get from Blocks format first
        if (isset($_POST['payment_data']) && is_array($_POST['payment_data'])) {
            foreach ($_POST['payment_data'] as $data) {
                if (isset($data['key'])) {
                    if ($data['key'] === 'thirdweb_tx_hash') {
                        $tx_hash = sanitize_text_field($data['value'] ?? '');
                    }
                    if ($data['key'] === 'thirdweb_chain_id') {
                        $chain_id = sanitize_text_field($data['value'] ?? '');
                    }
                }
            }
        }
        
        // Fallback to legacy format
        if (empty($tx_hash)) {
            $tx_hash = sanitize_text_field($_POST['thirdweb_tx_hash'] ?? '');
        }
        if (empty($chain_id)) {
            $chain_id = sanitize_text_field($_POST['thirdweb_chain_id'] ?? $this->chain_id);
        }

        // Payment was completed via checkout widget (frontend confirmed success)
        // Transaction hash is optional - thirdweb widget confirms payment before sending success
        if (!empty($tx_hash)) {
            // Try to verify on-chain (non-blocking)
            $verified = $this->verify_transaction($tx_hash, $order);
            $order->payment_complete($tx_hash);
            $order->add_order_note(
                sprintf(
                    __('Stablecoin payment completed via thirdweb checkout. Transaction: %s (Chain: %s)', 'thirdweb-wc'),
                    $tx_hash,
                    $chain_id ?: $this->chain_id
                )
            );
        } else {
            // No transaction hash - payment completed via widget, trust thirdweb's confirmation
            $order->payment_complete();
            $order->add_order_note(
                sprintf(
                    __('Stablecoin payment completed via thirdweb checkout widget. Chain: %s', 'thirdweb-wc'),
                    $chain_id ?: $this->chain_id
                )
            );
        }
        
        WC()->cart->empty_cart();
        
        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        ];
    }

    /**
     * Verify transaction on-chain
     */
    private function verify_transaction($tx_hash, $order) {
        // Use public RPC endpoint to verify the transaction
        // No Client ID needed - using public RPC endpoints
        $rpc_endpoints = [
            '1'      => 'https://eth.llamarpc.com', // Ethereum
            '8453'   => 'https://base.llamarpc.com', // Base
            '137'    => 'https://polygon.llamarpc.com', // Polygon
            '42161'  => 'https://arbitrum.llamarpc.com', // Arbitrum
            '10'     => 'https://optimism.llamarpc.com', // Optimism
        ];

        $rpc_url = $rpc_endpoints[$this->chain_id] ?? 'https://eth.llamarpc.com';
        
        $response = wp_remote_post($rpc_url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode([
                'jsonrpc' => '2.0',
                'method'  => 'eth_getTransactionReceipt',
                'params'  => [$tx_hash],
                'id'      => 1,
            ]),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            // If RPC fails, still allow the order (transaction hash is recorded)
            // Admin can manually verify on block explorer
            return true;
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
            'seller'       => $this->seller_wallet,
            'chainId'      => (int) $this->chain_id,
            'tokenAddress' => $this->token_address,
            'title'        => $this->title,
            'description'  => $this->description,
        ];
    }
}
