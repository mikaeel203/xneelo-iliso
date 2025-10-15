<?php
// auth.php

// Removed redundant CORS headers. These should be handled by the main entry point (e.g., user.php).
// header("Access-Control-Allow-Origin: http://127.0.0.1:5501"); // <-- REMOVED
// header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // <-- REMOVED
// header("Access-Control-Allow-Headers: Content-Type, Authorization"); // <-- REMOVED
// header("Access-Control-Allow-Credentials: true"); // <-- REMOVED
// header("Content-Type: application/json"); // <-- REMOVED

require_once 'config.php'; // To access MAIN_ADMIN_PASSWORD (ensure this constant is defined here!)
require_once 'vendor/autoload.php'; // Firebase\JWT (ensure you have composer installed and ran 'composer install')

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Used in admin.php to quickly validate the main admin password
function adminSignUpAuth($providedPassword) {
    // This function will rely on MAIN_ADMIN_PASSWORD from config.php
    return $providedPassword === MAIN_ADMIN_PASSWORD;
}

// Full validation function for signup, including:
// - main password
// - @lifechoices.co.za email
// - strong password format
// - local SA phone number validation
function validateAdminSignup($data) {
    // --- Start Debugging mainAdminPassword ---
    error_log("DEBUG: validateAdminSignup - Received data: " . print_r($data, true));

    $providedMainAdminPassword = $data['mainAdminPassword'] ?? 'NOT_SET';
    error_log("DEBUG: validateAdminSignup - Provided mainAdminPassword: " . $providedMainAdminPassword);
    
    // Ensure MAIN_ADMIN_PASSWORD constant is defined in config.php.
    // If it's not defined, this will cause a PHP error.
    if (!defined('MAIN_ADMIN_PASSWORD')) {
        error_log("ERROR: validateAdminSignup - MAIN_ADMIN_PASSWORD is not defined in config.php!");
        http_response_code(500); // Internal Server Error
        echo json_encode(['message' => 'Server Configuration Error: Main admin password not set.']);
        exit;
    }
    error_log("DEBUG: validateAdminSignup - Expected MAIN_ADMIN_PASSWORD: " . MAIN_ADMIN_PASSWORD);
    // --- End Debugging mainAdminPassword ---

    // Check if the provided main admin password matches the one in config.php
    if (!isset($data['mainAdminPassword']) || $data['mainAdminPassword'] !== MAIN_ADMIN_PASSWORD) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized: Invalid main admin password.']);
        exit; // Stop execution here if unauthorized
    }

    // Enforce email domain policy
    if (!isset($data['email']) || !str_ends_with($data['email'], "@lifechoices.co.za")) {
        http_response_code(403); // Forbidden
        echo json_encode(['message' => 'Only admin emails from @lifechoices.co.za are allowed.']);
        exit;
    }

    // Phone number validation (strict South African local format: 10 digits, starts with 0)
    if (!isset($data['phone_number']) || !preg_match('/^0\d{9}$/', $data['phone_number'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Phone number must be a valid South African number (e.g., 0601234567).']);
        exit;
    }

    // Password strength check
    $password = $data['password'] ?? ''; // Use null coalescing to safely get password
    // Regex: at least 8 chars, 1 lowercase, 1 uppercase, 1 digit, 1 special char
    $isStrong = preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
    if (!$isStrong) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'message' => 'Password must be at least 8 characters long and include a capital letter, small letter, number, and special symbol.'
        ]);
        exit;
    }
}

// ---
## Login Authentication & JWT Functions
// ---

function authenticateToken() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (!$token) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized: No token provided']);
        exit;
    }

    try {
        // Ensure JWT_SECRET is defined in config.php
        if (!defined('JWT_SECRET')) {
            error_log("ERROR: authenticateToken - JWT_SECRET is not defined in config.php!");
            throw new Exception('JWT_SECRET not configured.');
        }
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        http_response_code(403);
        echo json_encode(['message' => 'Invalid token', 'error' => $e->getMessage()]);
        exit;
    }
}


