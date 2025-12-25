<?php
// test_api_call.php

$url = 'http://localhost/HandleFacturation/assets/api/document_api.php';
$data = [
    'client_id' => 3, // Use a valid ID
    'items' => [
        ['id' => 1, 'qty' => 1, 'price' => 1000]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "CURL Error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response:\n$response\n";
}

curl_close($ch);
?>
