<?php
require_once 'config.php'; // To access MAIN_ADMIN_PASSWORD

// Used in user.php to quickly validate the main admin password
function adminSignUpAuth($providedPassword) {
    return $providedPassword === MAIN_ADMIN_PASSWORD;
}

// Full validation function for signup, including:
// - main password
// - @lifechoices.co.za email
// - strong password format
// - local SA phone number validation
function validateAdminSignup($data) {
    // Check if the provided main admin password matches the one in config.php
    if (!isset($data['mainAdminPassword']) || $data['mainAdminPassword'] !== MAIN_ADMIN_PASSWORD) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized: Invalid main admin password.']);
        exit;
    }

    // Enforce email domain policy
    if (!isset($data['email']) || !str_ends_with($data['email'], "@lifechoices.co.za")) {
        http_response_code(403);
        echo json_encode(['message' => 'Only admin emails from @lifechoices.co.za are allowed.']);
        exit;
    }

    // Phone number validation (strict South African local format: 10 digits, starts with 0)
    if (!isset($data['phone_number']) || !preg_match('/^0\d{9}$/', $data['phone_number'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Phone number must be a valid South African number (e.g., 0601234567).']);
        exit;
    }

    // Password strength check
    $password = $data['password'] ?? '';
    $isStrong = preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
    if (!$isStrong) {
        http_response_code(400);
        echo json_encode([
            'message' => 'Password must be at least 8 characters long and include a capital letter, small letter, number, and special symbol.'
        ]);
        exit;
    }
}
