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
     */
    useEffect(() => {
        const handleMessage = (event: MessageEvent) => {
            // Verify that message is from thirdweb checkout widget iframe
            if (
                event.origin === 'https://thirdweb.com' &&
                event.data.source === 'checkout-widget'
            ) {
                if (event.data.type === 'success') {
                    console.log('Purchase successful!', event.data);
                    
                    // Extract transaction hash if available
                    const transactionHash = event.data.transactionHash || event.data.txHash || null;
                    if (transactionHash) {
                        setTxHash(transactionHash);
                    }
                    
                    setPaymentComplete(true);
                    setError(null);
                }

                if (event.data.type === 'error') {
                    console.error('Purchase failed with error:', event.data.message);
                    setError(event.data.message || 'Payment failed. Please try again.');
                    setPaymentComplete(false);
                }
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
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            thirdweb_tx_hash: txHash || '',
                            thirdweb_chain_id: settings.chainId,
                        },
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
