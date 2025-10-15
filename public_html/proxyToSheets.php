<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: text/plain");

// Read raw POST data
$data = file_get_contents("php://input");

// Forward it to Google Apps Script
$ch = curl_init("https://script.google.com/macros/s/AKfycbw1YXXBbXFmMxcHYUbPCdKDMak3yapKdk1RLTsVHgJ2XHgub03yVg67a3e1nqMbWx4sIg/exec");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo "Curl error: " . curl_error($ch);
} else {
    echo $response;
}

curl_close($ch);
