<?php
// --- ITN (Instant Transaction Notification) Script ---

// --- Function to log data for debugging ---
function pflog($msg) {
    // In production, you would use a more robust logging system.
    // For this example, we'll just append to a file in the same directory.
    $logFile = __DIR__ . '/itn_log.txt';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

pflog("ITN call received from PayFast.");

// --- Configuration ---
// Use environment variables for sensitive data in production.
$passphrase = getenv('PAYFAST_PASSPHRASE') ?: 'Work_2000100'; // Must match the one used in the payment form.

// PayFast validation URL. Set $isSandbox to false for production.
$isSandbox = true;
$pfHost = $isSandbox ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';

// --- Data Validation ---
// 1. Get the raw POST data from PayFast. The stripslashes function is not needed
// as magic_quotes is deprecated and removed in modern PHP.
$pfData = $_POST;

if (empty($pfData) || !isset($pfData['payment_status'])) {
    pflog("Error: Invalid POST data received.");
    header("HTTP/1.0 400 Bad Request");
    exit();
}

pflog("Received POST data: " . print_r($pfData, true));

// 2. Generate signature from received data (excluding the received signature)
function generateSignature($data, $passphrase = '') {
    $output = '';
    foreach ($data as $key => $val) {
        if ($key !== 'signature') {
            $output .= $key . '=' . urlencode(trim($val)) . '&';
        }
    }

    $getString = substr($output, 0, -1);
    if (!empty($passphrase)) {
        $getString .= '&passphrase=' . urlencode(trim($passphrase));
    }

    return md5($getString);
}

$generatedSignature = generateSignature($pfData, $passphrase);
$receivedSignature = $pfData['signature'];

if ($generatedSignature !== $receivedSignature) {
    pflog("Error: Signatures do not match. Generated: $generatedSignature, Received: $receivedSignature");
    header("HTTP/1.0 400 Bad Request");
    exit();
}

pflog("Signatures match. Proceeding to server-to-server validation.");

// --- Server-to-Server Validation ---
$pfDataStr = http_build_query($pfData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://{$pfHost}/eng/query/validate");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $pfDataStr);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$response = curl_exec($ch);

if ($response === false) {
    pflog("cURL Error during validation: " . curl_error($ch));
}

curl_close($ch);

pflog("Validation response from PayFast: " . ($response ?: 'false'));

// --- Process the Transaction ---
if ($response === 'VALID') {
    pflog("Transaction is VALID. Processing order.");

    $paymentStatus = $pfData['payment_status'];
    $orderId = $pfData['m_payment_id'];
    $amount = $pfData['amount_gross'];

    // IMPORTANT: Here you would update your database.
    // e.g., UPDATE orders SET status = 'paid' WHERE id = '$orderId'
    switch ($paymentStatus) {
        case 'COMPLETE':
            pflog("Order #{$orderId} is COMPLETE. Amount: {$amount}. Fulfilling order.");
            // Fulfill the order (e.g., grant access, ship product)
            break;
        case 'FAILED':
            pflog("Order #{$orderId} FAILED. Updating order status.");
            break;
        // Add other cases as needed (PENDING, etc.)
    }

} else if ($response === 'INVALID') {
    pflog("Error: Transaction is INVALID. Security risk. Investigate immediately.");
} else {
    pflog("Error: Validation response was not 'VALID' or 'INVALID'. Response: " . $response);
}

// Respond with a 200 OK to let PayFast know we've received the ITN.
header("HTTP/1.0 200 OK");
flush();