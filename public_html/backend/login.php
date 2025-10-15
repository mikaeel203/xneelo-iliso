<?php
// ✅ No whitespace above this line!

// Enable CORS (must be very top)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Enable error reporting (for dev only)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Dependencies
require_once 'database.php';
require_once 'config.php';
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;

// Content type
header('Content-Type: application/json');

// DB connection
global $conn;

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['message' => 'Email and password are required']);
    exit;
}

// Query DB
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

// Generate token
$payload = [
    'id' => $admin['id'],
    'exp' => time() + (86400 * 100) // 100 days
];
$token = JWT::encode($payload, JWT_SECRET, 'HS256');

// Success response
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
