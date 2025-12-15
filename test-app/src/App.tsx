import { useState } from 'react';
import { createThirdwebClient } from 'thirdweb';
import { defineChain } from 'thirdweb/chains';
import { ThirdwebProvider } from 'thirdweb/react';
import { CheckoutWidget } from 'thirdweb/react';

// Load configuration from environment variables
const CONFIG = {
  clientId: import.meta.env.VITE_THIRDWEB_CLIENT_ID || '',
  seller: import.meta.env.VITE_SELLER_WALLET || '',
  chainId: parseInt(import.meta.env.VITE_CHAIN_ID || '8453'),
  tokenAddress: import.meta.env.VITE_TOKEN_ADDRESS || '',
  amount: import.meta.env.VITE_AMOUNT || '10.00',
};

// Create thirdweb client
const client = createThirdwebClient({
  clientId: CONFIG.clientId,
});

function App() {
  const [txHash, setTxHash] = useState<string>('');
  const [status, setStatus] = useState<{
    type: 'info' | 'success' | 'error';
    title: string;
    message: string;
  } | null>(null);

  // Check for configuration
  if (!CONFIG.clientId || CONFIG.clientId === '') {
    return (
      <div style={{ padding: '40px', maxWidth: '600px', margin: '0 auto' }}>
        <h1>⚠️ Configuration Required</h1>
        <p>Please create a <code>.env</code> file in the test-app directory with your credentials:</p>
        <pre style={{ background: '#f5f5f5', padding: '15px', borderRadius: '8px' }}>
{`VITE_THIRDWEB_CLIENT_ID=your_client_id_here
VITE_SELLER_WALLET=0xYourWalletAddress
VITE_CHAIN_ID=8453
VITE_TOKEN_ADDRESS=0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913
VITE_AMOUNT=10.00`}
        </pre>
        <p>
          Get your Client ID from{' '}
          <a href="https://thirdweb.com/dashboard" target="_blank" rel="noopener noreferrer">
            thirdweb.com/dashboard
          </a>
        </p>
      </div>
    );
  }

  if (!CONFIG.seller || CONFIG.seller === '') {
    return (
      <div style={{ padding: '40px', maxWidth: '600px', margin: '0 auto' }}>
        <h1>⚠️ Configuration Required</h1>
        <p>Please add your seller wallet address to the <code>.env</code> file.</p>
      </div>
    );
  }

  const chain = defineChain(CONFIG.chainId);

  // Get block explorer URL
  const getExplorerUrl = (hash: string) => {
    const explorers: Record<number, string> = {
      8453: `https://basescan.org/tx/${hash}`,
      1: `https://etherscan.io/tx/${hash}`,
      137: `https://polygonscan.com/tx/${hash}`,
      42161: `https://arbiscan.io/tx/${hash}`,
      10: `https://optimistic.etherscan.io/tx/${hash}`,
    };
    return explorers[CONFIG.chainId] || `https://blockscan.com/tx/${hash}`;
  };

  const handleSuccess = (result: any) => {
    console.log('✅ Payment Success!', result);

    // Handle different response formats
    let completedStatus = null;

    if (Array.isArray(result)) {
      // If result is an array, find the completed status
      completedStatus = result.find(
        (s) => s.status === 'completed' && s.transactionHash
      );
    } else if (result && typeof result === 'object') {
      // If result is a single object, check if it has transactionHash
      if (result.transactionHash) {
        completedStatus = result;
      }
    }

    if (completedStatus && completedStatus.transactionHash) {
      setTxHash(completedStatus.transactionHash);
      setStatus({
        type: 'success',
        title: '✅ Payment Successful!',
        message: `Transaction completed successfully!`,
      });
    } else {
      setStatus({
        type: 'success',
        title: '✅ Payment Successful!',
        message: 'Payment completed but no transaction hash found.',
      });
    }
  };

  const handleError = (error: Error) => {
    console.error('❌ Payment Error:', error);
    setStatus({
      type: 'error',
      title: '❌ Payment Failed',
      message: error.message || 'Unknown error occurred.',
    });
  };

  const handleCancel = () => {
    console.log('❌ Payment Cancelled');
    setStatus({
      type: 'error',
      title: '❌ Payment Cancelled',
      message: 'The payment was cancelled by the user.',
    });
  };

  return (
    <ThirdwebProvider>
      <div style={{ minHeight: '100vh', background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', display: 'flex', justifyContent: 'center', alignItems: 'center', padding: '20px' }}>
        <div style={{ background: 'white', borderRadius: '16px', padding: '40px', maxWidth: '600px', width: '100%', boxShadow: '0 20px 60px rgba(0, 0, 0, 0.3)' }}>
          <h1 style={{ margin: '0 0 10px 0', fontSize: '24px', color: '#333' }}>🧪 CheckoutWidget Test</h1>
          <p style={{ margin: '0 0 30px 0', color: '#666', fontSize: '14px' }}>Test thirdweb CheckoutWidget before WordPress integration</p>

          <div style={{ background: '#f8f9fa', borderRadius: '8px', padding: '20px', marginBottom: '30px' }}>
            <h2 style={{ fontSize: '16px', marginBottom: '15px', color: '#333' }}>⚙️ Configuration</h2>
            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderBottom: '1px solid #e0e0e0' }}>
              <span style={{ fontWeight: 500, color: '#555', fontSize: '13px' }}>Client ID:</span>
              <span style={{ color: '#666', fontFamily: 'Monaco, monospace', fontSize: '12px' }}>
                {CONFIG.clientId.substring(0, 20)}...
              </span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderBottom: '1px solid #e0e0e0' }}>
              <span style={{ fontWeight: 500, color: '#555', fontSize: '13px' }}>Seller Wallet:</span>
              <span style={{ color: '#666', fontFamily: 'Monaco, monospace', fontSize: '12px' }}>
                {CONFIG.seller.substring(0, 10)}...{CONFIG.seller.substring(CONFIG.seller.length - 8)}
              </span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderBottom: '1px solid #e0e0e0' }}>
              <span style={{ fontWeight: 500, color: '#555', fontSize: '13px' }}>Chain ID:</span>
              <span style={{ color: '#666', fontFamily: 'Monaco, monospace', fontSize: '12px' }}>
                {CONFIG.chainId} {CONFIG.chainId === 8453 && '(Base Mainnet)'}
                {CONFIG.chainId === 1 && '(Ethereum Mainnet)'}
              </span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderBottom: '1px solid #e0e0e0' }}>
              <span style={{ fontWeight: 500, color: '#555', fontSize: '13px' }}>Token Address:</span>
              <span style={{ color: '#666', fontFamily: 'Monaco, monospace', fontSize: '12px' }}>
                {CONFIG.tokenAddress || 'Native Token (ETH)'}
              </span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0' }}>
              <span style={{ fontWeight: 500, color: '#555', fontSize: '13px' }}>Amount:</span>
              <span style={{ color: '#666', fontFamily: 'Monaco, monospace', fontSize: '12px' }}>
                ${CONFIG.amount}
              </span>
            </div>
          </div>

          {status && (
            <div
              style={{
                background: status.type === 'success' ? '#f0fdf4' : status.type === 'error' ? '#fef2f2' : '#f0f9ff',
                borderLeft: `4px solid ${status.type === 'success' ? '#22c55e' : status.type === 'error' ? '#ef4444' : '#3b82f6'}`,
                padding: '15px',
                borderRadius: '4px',
                marginBottom: '20px',
              }}
            >
              <div style={{ fontWeight: 600, marginBottom: '8px', fontSize: '14px' }}>{status.title}</div>
              <div style={{ fontSize: '13px', color: '#666' }}>{status.message}</div>
              {txHash && (
                <div style={{ marginTop: '8px' }}>
                  <div style={{ background: 'white', padding: '10px', borderRadius: '4px', fontFamily: 'Monaco, monospace', fontSize: '12px', wordBreak: 'break-all' }}>
                    Tx Hash: {txHash}
                  </div>
                  <a
                    href={getExplorerUrl(txHash)}
                    target="_blank"
                    rel="noopener noreferrer"
                    style={{ color: '#667eea', textDecoration: 'none', fontSize: '13px' }}
                  >
                    View on Block Explorer →
                  </a>
                </div>
              )}
            </div>
          )}

          <div style={{ minHeight: '400px', border: '2px dashed #e0e0e0', borderRadius: '8px', padding: '20px', background: '#fafafa' }}>
            <CheckoutWidget
              client={client}
              chain={chain}
              amount={CONFIG.amount}
              seller={CONFIG.seller}
              tokenAddress={CONFIG.tokenAddress || undefined}
              paymentMethods={['crypto', 'card']}
              theme="light"
              onSuccess={handleSuccess}
              onError={handleError}
              onCancel={handleCancel}
            />
          </div>

          <div style={{ textAlign: 'center', color: '#999', fontSize: '12px', marginTop: '20px' }}>
            Edit configuration in <code>.env</code> •{' '}
            <a href="https://portal.thirdweb.com/connect/checkout" target="_blank" rel="noopener noreferrer" style={{ color: '#667eea', textDecoration: 'none' }}>
              CheckoutWidget Docs
            </a>
          </div>
        </div>
      </div>
    </ThirdwebProvider>
  );
}

export default App;
