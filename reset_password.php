<?php
require 'database.php';

// Allow requests from your frontend origin
header("Access-Control-Allow-Origin: http://127.0.0.1:5501");

// Allow these HTTP methods for CORS preflight requests
header("Access-Control-Allow-Methods: POST, OPTIONS");

// Allow these headers from the client
header("Access-Control-Allow-Headers: Content-Type");

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method."]);
    exit;
}

// Get raw JSON input and decode
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? '';
$new_password = $input['newPassword'] ?? '';

if (!$token || !$new_password) {
    echo json_encode(["error" => "Token and new password are required."]);
    exit;
}

// Find admin by token
$stmt = $mysqli->prepare("SELECT id, reset_token_expiry FROM admin WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    echo json_encode(["error" => "Invalid or expired token."]);
    exit;
}

$stmt->bind_result($admin_id, $reset_token_expiry);
$stmt->fetch();

if (strtotime($reset_token_expiry) < time()) {
    echo json_encode(["error" => "Token expired."]);
    exit;
}

// Hash the new password securely
$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

// Update password and clear the reset token and expiry
$updateStmt = $mysqli->prepare("UPDATE admin SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
$updateStmt->bind_param("si", $hashedPassword, $admin_id);
$updateStmt->execute();

echo json_encode(["message" => "Password has been reset successfully."]);

// Close statements and connection
$stmt->close();
$updateStmt->close();
$mysqli->close();
