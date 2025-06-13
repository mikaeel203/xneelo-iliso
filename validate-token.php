<?php
// validate-token.php

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
require_once 'vendor/autoload.php';
require_once 'config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

// Get token from Authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (!$token) {
    http_response_code(401);
    echo json_encode(['message' => 'No token provided']);
    exit;
}

try {
    $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
    echo json_encode(['message' => 'Token valid']);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid or expired token']);
    exit;
}
