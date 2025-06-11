<?php
require_once 'config.php';
require_once 'database.php';
require_once 'auth.php';

// Show all PHP errors (good for debugging in dev)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// ✅ Create new admin
function signUpAdmin($data) {
    global $mysqli;

    validateAdminSignup($data); // this should be defined in auth.php

    $username = $data['username'];
    $email = $data['email'];
    $phone_number = $data['phone_number'];
    $password = $data['password'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("INSERT INTO admin (username, password_hash, email, phone_number) VALUES (?, ?, ?, ?)");
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
        echo json_encode(['error' => 'Failed to sign up admin']);
    }

    $stmt->close();
}

// ✅ Get all admins
function getAllAdmins() {
    global $mysqli;

    $result = $mysqli->query("SELECT id, username, email, phone_number FROM admin");

    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }

    echo json_encode(['admins' => $admins]);
}

function getAdminById($id) {
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT id, username, email, phone_number FROM admin WHERE id = ?");
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

// ✅ Get one admin by username
function getAdminByUsername($username) {
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT id, username, email, phone_number FROM admin WHERE username = ?");
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

// ✅ Update admin info
function updateAdminByUsername($username, $data) {
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT * FROM admin WHERE username = ?");
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

    $stmt = $mysqli->prepare("UPDATE admin SET username = ?, email = ?, phone_number = ? WHERE username = ?");
    $stmt->bind_param("ssss", $newUsername, $email, $phone, $username);
    $stmt->execute();

    echo json_encode(['message' => 'Admin updated successfully.']);
    $stmt->close();
}

function updateAdminById($id, $data) {
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT * FROM admin WHERE id = ?");
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

    $stmt = $mysqli->prepare("UPDATE admin SET username = ?, email = ?, phone_number = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $phone, $id);
    $stmt->execute();

    echo json_encode(['message' => 'Admin updated successfully by ID.']);
    $stmt->close();
}

// ✅ Delete admin
function deleteAdminByUsername($username) {
    global $mysqli;

    $stmt = $mysqli->prepare("DELETE FROM admin WHERE username = ?");
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
    global $mysqli;

    $stmt = $mysqli->prepare("DELETE FROM admin WHERE id = ?");
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

// ✅ Entry point logic
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json');

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

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
    $input = json_decode(file_get_contents('php://input'), true);

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
