<?php
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // --- Dynamic Origin Configuration ---
    // Use Render's built-in environment variable for the production URL.
    $renderUrl = getenv('RENDER_EXTERNAL_URL');
    $allowed_origins = ['http://localhost:3000']; // Always allow local development
    if ($renderUrl) {
        // The RENDER_EXTERNAL_URL does not include the protocol, so we must add it.
        $allowed_origins[] = 'https://' . $renderUrl;
    }

    if (in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    }
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

header('Content-Type: application/json');

// --- Configuration (using Environment Variables for Production) ---
// These will be set in the Render dashboard.
// The '?:' provides a fallback for local development using your hardcoded values.
$merchantId = getenv('PAYFAST_MERCHANT_ID') ?: '10037484';
$merchantKey = getenv('PAYFAST_MERCHANT_KEY') ?: 'gl7ggaewzav2a';
$passphrase = getenv('PAYFAST_PASSPHRASE') ?: 'Work_2000100';

// PayFast URL. Set $isSandbox to false for production.
$isSandbox = true;
$payfastUrl = $isSandbox ? 'https://sandbox.payfast.co.za/eng/process' : 'https://www.payfast.co.za/eng/process';

// The $renderUrl variable is already defined in the CORS section above.
$baseUrl = $renderUrl ? 'https://' . $renderUrl : 'http://localhost:3000'; // Use it to build the base URL.

// --- Payment Data ---
// In a real application, this data would come from your database or user session.
$paymentData = [
    'merchant_id' => $merchantId,
    'merchant_key' => $merchantKey,
    // --- DYNAMIC URLS ---
    'return_url' => $baseUrl . '/payment-success',
    'cancel_url' => $baseUrl . '/payment-cancelled',
    'notify_url' => $baseUrl . '/api/payfast-notify.php',

    // Buyer details (example)
    'name_first' => 'John',
    'name_last'  => 'Doe',
    'email_address'=> 'john.doe@example.com',

    // Transaction details (example)
    'm_payment_id' => 'ORDER_'.uniqid(), // A unique payment ID for your records
    'amount' => '249.99',
    'item_name' => 'Annual Subscription'
];

// --- Signature Generation ---
function generateSignature($data, $passphrase = '') {
    $output = '';
    foreach ($data as $key => $val) {
        if ($val !== '') {
            $output .= $key . '=' . urlencode(trim($val)) . '&';
        }
    }

    $getString = substr($output, 0, -1); // Remove last ampersand
    if (!empty($passphrase)) {
        $getString .= '&passphrase=' . urlencode(trim($passphrase));
    }

    return md5($getString);
}

$paymentData['signature'] = generateSignature($paymentData, $passphrase);

$response = [
    'payfastUrl' => $payfastUrl,
    'formData' => $paymentData
];

echo json_encode($response);
