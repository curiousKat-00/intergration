import React, { useState, useEffect } from 'react';
import './App.css';

function PaymentForm() {
  const [payfastData, setPayfastData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      setError(null);
      try {
        const response = await fetch('/api/payfast.php');
        // Always read the response as text first to avoid parsing errors on non-JSON responses.
        const responseText = await response.text();

        if (!response.ok) {
          throw new Error(`Network error: ${response.status} ${response.statusText}. Response: ${responseText}`);
        }

        // Now, safely try to parse the text as JSON.
        const data = JSON.parse(responseText);
        setPayfastData(data);

      } catch (err) {
        // This will catch both network errors and JSON parsing errors.
        console.error("Failed to fetch or parse PayFast data:", err);
        setError('Failed to load payment form. The server may be misconfigured. Please check the console.');
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  if (loading) {
    return <p>Loading Payment Gateway...</p>;
  }

  if (error || !payfastData) {
    return <p style={{ color: 'red' }}>{error || 'An unknown error occurred.'}</p>;
  }

  const { payfastUrl, formData } = payfastData;

  return (
    <>
      <h1>Complete Your Payment</h1>
      <p>You are about to pay <strong>R{formData.amount}</strong> for "{formData.item_name}".</p>
      <p>Click the button below to proceed to the secure PayFast payment page.</p>
      
      <form action={payfastUrl} method="post">
        {Object.entries(formData).map(([key, value]) => (
          <input key={key} type="hidden" name={key} value={String(value)} />
        ))}
        <button type="submit" className="App-link" style={{fontSize: '1.2rem', padding: '10px 20px'}}>Pay Now</button>
      </form>
    </>
  );
}

export default PaymentForm;