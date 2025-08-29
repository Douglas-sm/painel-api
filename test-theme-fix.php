<?php
/**
 * Test script to verify the fix for the theme API endpoint
 *
 * This script makes an HTTP request to the theme API endpoint for the Celta tenant
 * and displays the response.
 */

// URL of the API endpoint
$url = 'http://localhost:8000/api/theme/celta';

// Function to make an HTTP request
function makeRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'response' => $response
    ];
}

// Test the endpoint
echo "Testing Theme API Endpoint Fix\n";
echo "==============================\n\n";

echo "Testing tenant: celta\n";
echo "URL: $url\n";

$result = makeRequest($url);

echo "Status: {$result['status']}\n";
echo "Response: {$result['response']}\n\n";

echo "Test completed.\n";
