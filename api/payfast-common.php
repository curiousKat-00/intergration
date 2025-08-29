<?php

/**
 * Generates a PayFast signature for a data array.
 *
 * @param array $data The data to sign.
 * @param string|null $passphrase The passphrase to use (optional).
 * @return string The MD5 signature.
 */
function generateSignature($data, $passphrase = '') {
    // Sort the data array alphabetically by key. This is crucial for a valid signature.
    ksort($data);

    $output = http_build_query($data);
    $output = str_replace('%20', '+', $output);

    if (!empty($passphrase)) {
        $output .= '&passphrase=' . urlencode(trim($passphrase));
    }

    return md5($output);
}