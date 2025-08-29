import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import './App.css';

import PaymentForm from './PaymentForm';
import PaymentSuccess from './PaymentSuccess';
import PaymentCancelled from './PaymentCancelled';

function App() {
  return (
    <Router>
      <div className="App">
        <header className="App-header">
          <Routes>
            <Route path="/" element={<PaymentForm />} />
            <Route path="/payment-success" element={<PaymentSuccess />} />
            <Route path="/payment-cancelled" element={<PaymentCancelled />} />
          </Routes>
        </header>
      </div>
    </Router>
  );
}

export default App;
