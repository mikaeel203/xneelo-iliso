<?php
require_once 'config.php';
require_once 'database.php'; // $conn connection

// --- CORS Setup ---
$allowed_origins = [
    'http://127.0.0.1:5500',
    'http://localhost:5500',
    'http://127.0.0.1:5501',
    'http://localhost:5501'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method. Only POST allowed."]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? '';
$new_password = $input['newPassword'] ?? '';

if (!$token || !$new_password) {
    http_response_code(400);
    echo json_encode(["error" => "Token and new password are required."]);
    exit;
}

global $conn;
$stmt = $conn->prepare("SELECT id, reset_token_expiry FROM admin WHERE reset_token = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: failed to prepare statement."]);
    exit;
}

$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or expired token."]);
    $stmt->close();
    exit;
}

$stmt->bind_result($admin_id, $reset_token_expiry);
$stmt->fetch();

if (strtotime($reset_token_expiry) < time()) {
    http_response_code(400);
    echo json_encode(["error" => "Token expired."]);
    $stmt->close();
    exit;
}
$stmt->close();

$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

$updateStmt = $conn->prepare("UPDATE admin SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
if (!$updateStmt) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: failed to prepare update statement."]);
    exit;
}

$updateStmt->bind_param("si", $hashedPassword, $admin_id);
$success = $updateStmt->execute();
$updateStmt->close();

if ($success) {
    http_response_code(200);
    echo json_encode(["message" => "Password has been reset successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update password."]);
}

$conn->close();
