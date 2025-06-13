<?php
// login.php

// Enable CORS
header("Access-Control-Allow-Origin: *"); // Replace * with specific origin if needed
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'database.php';
require_once 'vendor/autoload.php'; // For Firebase\JWT

use Firebase\JWT\JWT;

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['message' => 'Email and password are required']);
    exit;
}

// Fetch admin from database

$stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin || !password_verify($password, $admin['password_hash'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid email or password']);
    exit;
}

// Generate JWT token

$payload = [
    'id' => $admin['id'],
    'exp' => time() + (86400 * 100) // 100 days
];

$token = JWT::encode($payload, JWT_SECRET, 'HS256');

echo json_encode([
    'message' => 'Login successful',
    'token' => $token,
    'admin' => [
        'id' => $admin['id'],
        'username' => $admin['username'],
        'email' => $admin['email'],
        'phone_number' => $admin['phone_number']
    ]
]);



