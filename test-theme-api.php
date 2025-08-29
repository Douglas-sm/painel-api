<?php
/**
 * Test script to verify the theme API endpoint
 *
 * This script makes HTTP requests to the theme API endpoint for each tenant
 * and displays the response.
 */

// Base URL of the API
$baseUrl = 'http://localhost:8000/api/theme/';

// List of tenants to test
$tenants = ['alfa', 'beta', 'celta'];

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

// Test each tenant
echo "Testing Theme API Endpoint\n";
echo "=========================\n\n";

foreach ($tenants as $tenant) {
    echo "Testing tenant: $tenant\n";
    $url = $baseUrl . $tenant;
    echo "URL: $url\n";

    $result = makeRequest($url);

    echo "Status: {$result['status']}\n";
    echo "Response: {$result['response']}\n\n";
}

// Test with an invalid tenant
echo "Testing with invalid tenant\n";
$url = $baseUrl . 'invalid';
echo "URL: $url\n";

$result = makeRequest($url);

echo "Status: {$result['status']}\n";
echo "Response: {$result['response']}\n\n";

echo "Test completed.\n";
