/**
 * thirdweb Checkout Block for WooCommerce
 * 
 * This registers our payment method with WooCommerce's checkout blocks system
 * and renders the thirdweb CheckoutWidget
 */

import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { ThirdwebCheckout } from './ThirdwebCheckout';

// Get settings passed from PHP via get_payment_method_data()
const settings = getSetting('thirdweb_stablecoin_data', {});

const defaultLabel = 'Pay with Stablecoin';
const label = decodeEntities(settings?.title || defaultLabel);

/**
 * Label component - shown in the payment method selector
 */
const Label = () => {
    return (
        <span style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            {settings.icon && (
                <img 
                    src={settings.icon} 
                    alt="Stablecoin" 
                    style={{ width: '24px', height: '24px' }} 
                />
            )}
            <span>{label}</span>
            <span style={{ 
                fontSize: '12px', 
                color: '#666',
                marginLeft: 'auto' 
            }}>
                USDC • USDT
            </span>
        </span>
    );
};

/**
 * Content component - rendered when this payment method is selected
 */
const Content = (props) => {
    const { eventRegistration, emitResponse } = props;
    const { onPaymentSetup } = eventRegistration;

    return (
        <ThirdwebCheckout
            settings={settings}
            onPaymentSetup={onPaymentSetup}
            emitResponse={emitResponse}
            {...props}
        />
    );
};

/**
 * Edit component - shown in the block editor
 */
const Edit = () => {
    return (
        <div style={{ padding: '20px', background: '#f5f5f5', borderRadius: '8px' }}>
            <p style={{ margin: 0, fontWeight: 'bold' }}>
                thirdweb Stablecoin Checkout
            </p>
            <p style={{ margin: '8px 0 0', fontSize: '14px', color: '#666' }}>
                Customers will see the CheckoutWidget here to pay with USDC, USDT, 
                or other supported stablecoins.
            </p>
        </div>
    );
};

/**
 * Check if this payment method can be used
 */
const canMakePayment = () => {
    // Verify required settings are configured
    return !!(settings.clientId && settings.seller && settings.chainId);
};

/**
 * Register the payment method with WooCommerce Blocks
 */
registerPaymentMethod({
    name: 'thirdweb_stablecoin',
    label: <Label />,
    content: <Content />,
    edit: <Edit />,
    canMakePayment,
    ariaLabel: label,
    supports: {
        features: settings?.supports || ['products'],
    },
});
