/**
 * ThirdwebCheckout Component
 * 
 * Uses thirdweb's hosted checkout widget iframe (no Client ID required)
 */

import React, { useEffect, useCallback, useState, useRef } from 'react';

interface ThirdwebCheckoutProps {
    settings: {
        seller: string;
        chainId: number;
        tokenAddress?: string;
        description?: string;
        supportedTokens?: Array<{ symbol: string; address: string }>;
    };
    billing: {
        cartTotal: {
            value: number; // Total in cents
        };
        currency: {
            code: string;
        };
    };
    onPaymentSetup: (callback: () => any) => () => void;
    emitResponse: {
        responseTypes: {
            SUCCESS: string;
            ERROR: string;
            FAIL: string;
        };
    };
}

export const ThirdwebCheckout: React.FC<ThirdwebCheckoutProps> = ({
    settings,
    billing,
    onPaymentSetup,
    emitResponse,
}) => {
    const [txHash, setTxHash] = useState<string | null>(null);
    const [paymentComplete, setPaymentComplete] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const iframeRef = useRef<HTMLIFrameElement>(null);

    // Calculate amount in token units
    // WooCommerce sends total in smallest currency unit (cents for USD)
    const cartTotalInDollars = billing.cartTotal.value / 100;
    const amount = cartTotalInDollars.toFixed(2);

    // Build iframe URL
    const buildIframeUrl = () => {
        const baseUrl = 'https://thirdweb.com/bridge/checkout-widget';
        const params = new URLSearchParams({
            chain: settings.chainId.toString(),
            amount: amount,
            seller: settings.seller,
        });

        // Add token address if provided
        if (settings.tokenAddress && settings.tokenAddress.startsWith('0x')) {
            params.append('tokenAddress', settings.tokenAddress);
        }

        return `${baseUrl}?${params.toString()}`;
    };

    /**
     * Listen for messages from the checkout widget iframe
     * 
     * thirdweb sends messages with format:
     * { source: "checkout-widget", type: "success"|"error", message: "...", ... }
     */
    useEffect(() => {
        const handleMessage = (event: MessageEvent) => {
            // Verify origin is from thirdweb.com (security check)
            const isThirdwebOrigin = event.origin === 'https://thirdweb.com' || 
                                    event.origin === 'https://www.thirdweb.com';
            
            if (!isThirdwebOrigin) {
                return;
            }

            const data = event.data;
            
            // Verify message is from checkout-widget by checking source field
            if (!data || typeof data !== 'object' || data.source !== 'checkout-widget') {
                return;
            }

            // Handle success messages
            if (data.type === 'success') {
                // Extract transaction hash if available
                const transactionHash = 
                    data.transactionHash || 
                    data.txHash || 
                    data.hash ||
                    null;
                
                if (transactionHash) {
                    setTxHash(transactionHash);
                }
                
                setPaymentComplete(true);
                setError(null);
            } 
            // Handle error messages
            else if (data.type === 'error') {
                const errorMessage = data.message || 'Payment failed. Please try again.';
                setError(errorMessage);
                setPaymentComplete(false);
            }
        };

        window.addEventListener('message', handleMessage);

        return () => {
            window.removeEventListener('message', handleMessage);
        };
    }, []);

    /**
     * Register with WooCommerce's payment processing
     */
    useEffect(() => {
        const unsubscribe = onPaymentSetup(() => {
            if (paymentComplete) {
                // Build payment method data - only include tx_hash if it exists
                const paymentMethodData: Record<string, string> = {
                    thirdweb_chain_id: String(settings.chainId),
                };
                
                // Only add tx_hash if we have one (it's optional)
                if (txHash) {
                    paymentMethodData.thirdweb_tx_hash = txHash;
                }
                
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData,
                    },
                };
            }

            if (error) {
                return {
                    type: emitResponse.responseTypes.ERROR,
                    message: error,
                };
            }

            // Payment not yet completed
            return {
                type: emitResponse.responseTypes.ERROR,
                message: 'Please complete the payment using the widget above.',
            };
        });

        return unsubscribe;
    }, [onPaymentSetup, emitResponse, paymentComplete, txHash, error, settings.chainId]);

    return (
        <div className="thirdweb-checkout-container">
            {/* Description */}
            {settings.description && (
                <p className="thirdweb-checkout-description">
                    {settings.description}
                </p>
            )}

            {/* Checkout Widget Iframe */}
            <div className="thirdweb-widget-wrapper">
                <iframe
                    ref={iframeRef}
                    src={buildIframeUrl()}
                    height="700px"
                    width="100%"
                    style={{ border: 0 }}
                    title="thirdweb Checkout Widget"
                    allow="payment"
                />
            </div>

            {/* Status messages */}
            {paymentComplete && (
                <div className="thirdweb-checkout-success">
                    ✓ Payment complete! Click "Place Order" to confirm.
                </div>
            )}

            {error && (
                <div className="thirdweb-checkout-error">
                    {error}
                </div>
            )}

            {/* Supported tokens display */}
            <div className="thirdweb-supported-tokens">
                <small>
                    Accepts: USDC, USDT, and more • Powered by thirdweb
                </small>
            </div>

            {/* Styles */}
            <style>{`
                .thirdweb-checkout-container {
                    padding: 16px 0;
                }
                
                .thirdweb-checkout-description {
                    color: #666;
                    margin-bottom: 16px;
                }
                
                .thirdweb-widget-wrapper {
                    min-height: 700px;
                    width: 100%;
                    margin: 16px 0;
                }
                
                .thirdweb-widget-wrapper iframe {
                    width: 100%;
                    border: 0;
                    border-radius: 8px;
                }
                
                .thirdweb-checkout-success {
                    background: #d4edda;
                    color: #155724;
                    padding: 12px 16px;
                    border-radius: 4px;
                    margin-top: 16px;
                }
                
                .thirdweb-checkout-error {
                    background: #f8d7da;
                    color: #721c24;
                    padding: 12px 16px;
                    border-radius: 4px;
                    margin-top: 16px;
                }
                
                .thirdweb-supported-tokens {
                    text-align: center;
                    margin-top: 16px;
                    color: #888;
                }
            `}</style>
        </div>
    );
};
