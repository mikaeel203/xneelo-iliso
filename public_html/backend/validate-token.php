<?php
// validate-token.php

// Enable CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

// Allow OPTIONS method for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';       // Your config file, contains JWT_SECRET
require_once 'vendor/autoload.php';  // Composer autoload for Firebase JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function authenticateToken() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized: No Authorization header']);
        exit;
    }

    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized: Malformed Authorization header']);
        exit;
    }

    $token = $matches[1];

    try {
        if (!defined('JWT_SECRET')) {
            throw new Exception('JWT_SECRET not defined');
        }
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));

        if ($decoded->exp < time()) {
        http_response_code(403);
        echo json_encode(['message' => 'Token expired']);
        exit;
    }
        return $decoded;
        
    } catch (Exception $e) {
        http_response_code(403);
        echo json_encode(['message' => 'Invalid token', 'error' => $e->getMessage()]);
        exit;
    }
}

$decodedToken = authenticateToken();

echo json_encode([
    'message' => 'Token is valid',
    'userId' => $decodedToken->id ?? null,
    'exp' => $decodedToken->exp ?? null
]);
