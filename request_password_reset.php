<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';
require_once 'database.php'; // $conn connection
require_once 'vendor/autoload.php';

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

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (!$email) {
    http_response_code(400);
    echo json_encode(["error" => "Email is required."]);
    exit;
}

global $conn;
$stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: failed to prepare statement."]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    http_response_code(404);
    echo json_encode(["error" => "Email not found."]);
    $stmt->close();
    exit;
}

$stmt->bind_result($admin_id);
$stmt->fetch();
$stmt->close();

$token = bin2hex(random_bytes(32));
$expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

$updateStmt = $conn->prepare("UPDATE admin SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
if (!$updateStmt) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: failed to prepare update statement."]);
    exit;
}

$updateStmt->bind_param("ssi", $token, $expiry, $admin_id);
$updateStmt->execute();
$updateStmt->close();

$resetLink = "http://127.0.0.1:5500/frontend/ResetPassword.html?token=$token&email=$email";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = EMAIL_USER;
    $mail->Password   = EMAIL_PASS;

    // Use either of these:
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Or:
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    // $mail->Port       = 465;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom(EMAIL_USER, 'Attendance System');
    $mail->addAddress($email);

    $mail->isHTML(false);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "Click the link to reset your password: $resetLink";

    $mail->send();

    http_response_code(200);
    echo json_encode(["message" => "Password reset link sent to your email."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Mailer Error: {$mail->ErrorInfo}"]);
}


$conn->close();
