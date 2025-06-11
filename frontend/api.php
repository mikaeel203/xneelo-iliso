<?php
header('Content-Type: application/json');
require 'db.php'; // Include the database connection

// Get the request method
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($requestMethod) {
    case 'POST':
        // Get the action from the request body
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';

        switch ($action) {
            case 'login':
                handleLogin($data);
                break;
            case 'add_employee':
                handleAddEmployee($data);
                break;
            case 'add_admin':
                handleAddAdmin($data);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action.']);
                break;
        }
        break;

    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'fetch_employees') {
            handleFetchEmployees();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        break;
}

// Function to handle login
function handleLogin($data) {
    global $pdo;
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid ID or password.']);
    }
}

// Function to fetch employees
function handleFetchEmployees() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM employees");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['allEmployees' => $employees]);
}

// Function to add a new employee
function handleAddEmployee($data) {
    global $pdo;
    $name = $data['name'] ?? '';
    $department = $data['department'] ?? '';
    $email = $data['email'] ?? '';
    $status = $data['status'] ?? 'On Site';

    if ($name && $department && $email) {
        $stmt = $pdo->prepare("INSERT INTO employees (name, department, email, status) VALUES (:name, :department, :email, :status)");
        if ($stmt->execute(['name' => $name, 'department' => $department, 'email' => $email, 'status' => $status])) {
            echo json_encode(['success' => true, 'message' => 'Employee added successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add employee.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
}

// Function to add a new admin
function handleAddAdmin($data) {
    global $pdo;
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $department = $data['department'] ?? '';

    if ($email && $password && $department) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (email, password, department) VALUES (:email, :password, :department)");
        if ($stmt->execute(['email' => $email, 'password' => $hashedPassword, 'department' => $department])) {
            echo json_encode(['success' => true, 'message' => 'Admin added successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add admin.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
}
?>
