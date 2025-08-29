import React, { useState, useEffect } from 'react';
import './App.css';

function PaymentForm() {
  const [payfastData, setPayfastData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // This relative path will be proxied in development and work directly in production.
    fetch('/api/payfast.php')
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok. Is the PHP server running?');
        }
        return response.json();
      })
      .then(data => {
        setPayfastData(data);
        setLoading(false);
      })
      .catch(err => {
        console.error("Failed to fetch PayFast data:", err);
        setError('Failed to load payment form. Please check the console for details.');
        setLoading(false);
      });
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