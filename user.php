<?php
// user.php

require_once 'config.php';
require_once 'database.php';
require_once 'auth.php';

// ✅ Show all PHP errors (for development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Handle CORS
function cors() {
    // Define the specific allowed origin for your frontend.
    // This MUST precisely match the origin (protocol://domain:port) of your Dashboard.html.
    $allowed_frontend_origin = "http://127.0.0.1:5500"; 

    // --- Start Debugging and Direct CORS Header Setting ---
    // Log the incoming Origin header and the configured allowed origin for debugging.
    // Check your PHP error log (e.g., Apache error.log or terminal if using PHP's built-in server)
    // for these messages.
    error_log("CORS Debug: Incoming HTTP_ORIGIN: " . ($_SERVER['HTTP_ORIGIN'] ?? 'NOT SET'));
    error_log("CORS Debug: Configured ALLOWED_ORIGIN: " . $allowed_frontend_origin);

    // Directly set the Access-Control-Allow-Origin header.
    // For local development, this direct setting is often simplest.
    // In production, you might want more sophisticated checks, but this is the core.
    header("Access-Control-Allow-Origin: " . $allowed_frontend_origin);
    // --- End Debugging and Direct CORS Header Setting ---

    // Allow credentials (like cookies or HTTP authentication) to be sent.
    header("Access-Control-Allow-Credentials: true");

    // Cache the preflight request for 1 day to reduce overhead.
    header("Access-Control-Max-Age: 86400"); 

    // Handle preflight OPTIONS requests.
    // The browser sends an OPTIONS request before certain "complex" requests (like POST with JSON).
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // Allow the methods specified by the client in the preflight request.
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        }

        // Allow the headers specified by the client in the preflight request.
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }

        // Send a 204 No Content response for successful preflight and exit.
        http_response_code(204); 
        exit(0); // Terminate script execution after preflight
    }
}

// ✅ Run CORS handling first
cors();

// ✅ Default content type for all API responses to JSON.
header("Content-Type: application/json");

// ---
## Admin Management Functions
// ---

// ✅ Create new admin
function signUpAdmin($data) {
    global $conn;

    // Validate admin signup data. Assumes this function is in auth.php
    // and throws an exception or returns an error if validation fails.
    validateAdminSignup($data);

    $username = $data['username'];
    $email = $data['email'];
    $phone_number = $data['phone_number'];
    $password = $data['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admin (username, password_hash, email, phone_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashedPassword, $email, $phone_number);

    if ($stmt->execute()) {
        echo json_encode([
            'message' => 'Admin signed up successfully',
            'admin' => [
                'id' => $stmt->insert_id,
                'username' => $username,
                'email' => $email,
                'phone_number' => $phone_number,
                'role' => 'admin'
            ]
        ]);
    } else {
        http_response_code(500);
        // Provide more detailed error message from the database for debugging.
        echo json_encode(['error' => 'Failed to sign up admin: ' . $stmt->error]); 
    }

    $stmt->close();
}

// ✅ Get all admins
function getAllAdmins() {
    global $conn;

    $result = $conn->query("SELECT id, username, email, phone_number FROM admin");
    $admins = [];

    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }

    echo json_encode(['admins' => $admins]);
}

// ✅ Get admin by ID
function getAdminById($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT id, username, email, phone_number FROM admin WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Admin not found.']);
    } else {
        echo json_encode(['admin' => $result->fetch_assoc()]);
    }

    $stmt->close();
}

// ✅ Get admin by Username
function getAdminByUsername($username) {
    global $conn;

    $stmt = $conn->prepare("SELECT id, username, email, phone_number FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Admin not found.']);
    } else {
        echo json_encode(['admin' => $result->fetch_assoc()]);
    }

    $stmt->close();
}

// ✅ Update admin by Username
function updateAdminByUsername($username, $data) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existing) {
        http_response_code(404);
        echo json_encode(['message' => 'Admin not found.']);
        return;
    }

    $newUsername = $data['new_username'] ?? $existing['username'];
    $email = $data['email'] ?? $existing['email'];
    $phone = $data['phone_number'] ?? $existing['phone_number'];

    $stmt = $conn->prepare("UPDATE admin SET username = ?, email = ?, phone_number = ? WHERE username = ?");
    $stmt->bind_param("ssss", $newUsername, $email, $phone, $username);
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Admin updated successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update admin: ' . $stmt->error]);
    }
    $stmt->close();
}

// ✅ Update admin by ID
function updateAdminById($id, $data) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existing) {
        http_response_code(404);
        echo json_encode(['message' => 'Admin not found.']);
        return;
    }

    $username = $data['username'] ?? $existing['username'];
    $email = $data['email'] ?? $existing['email'];
    $phone = $data['phone_number'] ?? $existing['phone_number'];

    $stmt = $conn->prepare("UPDATE admin SET username = ?, email = ?, phone_number = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $phone, $id);
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Admin updated successfully by ID.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update admin: ' . $stmt->error]);
    }
    $stmt->close();
}

// ✅ Delete admin by Username
function deleteAdminByUsername($username) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Admin not found.']);
    } else {
        echo json_encode(['message' => 'Admin deleted successfully.']);
    }

    $stmt->close();
}

// ✅ Delete admin by ID
function deleteAdminById($id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Admin not found.']);
    } else {
        echo json_encode(['message' => 'Admin deleted successfully.']);
    }

    $stmt->close();
}

// ---
## Handle Routing Based on Method + Action
// ---
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Get the raw POST/PUT data
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    if ($action === 'signup') {
        signUpAdmin($input);
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid POST action']);
    }

} elseif ($method === 'GET') {
    if ($action === 'getAll') {
        getAllAdmins();
    } elseif ($action === 'get') {
        if (isset($_GET['username'])) {
            getAdminByUsername($_GET['username']);
        } elseif (isset($_GET['id'])) {
            getAdminById($_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Missing identifier: Provide either username or id']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid GET action']);
    }

} elseif ($method === 'PUT') {
    if ($action === 'update') {
        if (isset($_GET['username'])) {
            updateAdminByUsername($_GET['username'], $input);
        } elseif (isset($_GET['id'])) {
            updateAdminById($_GET['id'], $input);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Missing identifier: Provide either username or id']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid PUT action']);
    }

} elseif ($method === 'DELETE') {
    if ($action === 'delete') {
        if (isset($_GET['username'])) {
            deleteAdminByUsername($_GET['username']);
        } elseif (isset($_GET['id'])) {
            deleteAdminById($_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Missing identifier: Provide either username or id']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid DELETE action']);
    }

} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}

