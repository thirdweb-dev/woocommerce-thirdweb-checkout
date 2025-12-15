/**
 * ThirdwebCheckout Component
 * 
 * Wraps thirdweb's CheckoutWidget for WooCommerce checkout integration
 */

import React, { useEffect, useCallback, useState } from 'react';
import { ThirdwebProvider } from 'thirdweb/react';
import { CheckoutWidget } from 'thirdweb/react';
import { createThirdwebClient } from 'thirdweb';
import { defineChain } from 'thirdweb/chains';

interface ThirdwebCheckoutProps {
    settings: {
        clientId: string;
        seller: string;
        chainId: number;
        tokenAddress?: string;
        description?: string;
        theme?: 'light' | 'dark';
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

    // Create thirdweb client
    const client = createThirdwebClient({
        clientId: settings.clientId,
    });

    // Define the chain
    const chain = defineChain(settings.chainId);

    // Calculate amount in token units (assuming 6 decimals for USDC/USDT)
    // WooCommerce sends total in smallest currency unit (cents for USD)
    const cartTotalInDollars = billing.cartTotal.value / 100;

    // For stablecoins, amount is typically 1:1 with USD
    // The CheckoutWidget expects amount as a string
    const amount = cartTotalInDollars.toFixed(2);

    /**
     * Handle successful payment
     * @param result - Status result(s) from CheckoutWidget (can be array or single object)
     */
    const handleSuccess = useCallback((result: any) => {
        console.log('Payment success, result:', result);

        // Handle different response formats
        let completedStatus = null;

        if (Array.isArray(result)) {
            // If result is an array, find the completed status
            completedStatus = result.find(
                (status) => status.status === 'completed' && status.transactionHash
            );
        } else if (result && typeof result === 'object') {
            // If result is a single object, check if it has transactionHash
            if (result.transactionHash) {
                completedStatus = result;
            }
        }

        if (completedStatus && completedStatus.transactionHash) {
            setTxHash(completedStatus.transactionHash);
            setPaymentComplete(true);
            console.log('Transaction hash captured:', completedStatus.transactionHash);
        } else {
            // Fallback: mark as complete even without tx hash (will use RPC verification)
            setPaymentComplete(true);
            console.warn('Payment complete but no transaction hash found in result');
        }
    }, []);

    /**
     * Handle payment error
     */
    const handleError = useCallback((err: Error) => {
        console.error('Payment error:', err);
        setError(err.message);
    }, []);

    /**
     * Handle payment cancellation
     */
    const handleCancel = useCallback(() => {
        setError('Payment cancelled');
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
        <ThirdwebProvider>
            <div className="thirdweb-checkout-container">
                {/* Description */}
                {settings.description && (
                    <p className="thirdweb-checkout-description">
                        {settings.description}
                    </p>
                )}

                {/* CheckoutWidget */}
                <div className="thirdweb-widget-wrapper">
                    <CheckoutWidget
                        client={client}
                        chain={chain}
                        amount={amount}
                        seller={settings.seller as `0x${string}`}

                        // Optional: specific token (USDC address)
                        // Only pass if it's a valid address, not empty string
                        {...(settings.tokenAddress && settings.tokenAddress.startsWith('0x')
                            ? { tokenAddress: settings.tokenAddress as `0x${string}` }
                            : {})}

                        // Payment methods
                        paymentMethods={['crypto', 'card']}

                        // Callbacks
                        onSuccess={handleSuccess}
                        onError={handleError}
                        onCancel={handleCancel}

                        // Theming
                        theme={settings.theme || 'light'}
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
                        min-height: 400px;
                        display: flex;
                        justify-content: center;
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
        </ThirdwebProvider>
    );
};
