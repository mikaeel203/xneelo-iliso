<?php
require 'database.php';

// ✅ Dynamic CORS support
$allowed_origins = ['http://127.0.0.1:5500', 'http://localhost:5500'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

// ✅ Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method."]);
    exit;
}

// ✅ Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? '';
$new_password = $input['newPassword'] ?? '';

if (!$token || !$new_password) {
    echo json_encode(["error" => "Token and new password are required."]);
    exit;
}

// ✅ Find user by token
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

// ✅ Hash new password and update DB
$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
$updateStmt = $mysqli->prepare("UPDATE admin SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
$updateStmt->bind_param("si", $hashedPassword, $admin_id);
$updateStmt->execute();

echo json_encode(["message" => "Password has been reset successfully."]);

// ✅ Cleanup
$stmt->close();
$updateStmt->close();
$mysqli->close();
