<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'database.php';
require 'vendor/autoload.php';  // PHPMailer autoload

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method."]);
    exit;
}

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (!$email) {
    echo json_encode(["error" => "Email is required."]);
    exit;
}

// Prepare and execute select query
$stmt = $mysqli->prepare("SELECT id FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    echo json_encode(["error" => "Email not found."]);
    exit;
}

$stmt->bind_result($admin_id);
$stmt->fetch();

// Generate token and expiry
$token = bin2hex(random_bytes(32));
$expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

// Update token and expiry in DB
$updateStmt = $mysqli->prepare("UPDATE admin SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
$updateStmt->bind_param("ssi", $token, $expiry, $admin_id);
$updateStmt->execute();

$resetLink = "http://127.0.0.1:5501/ResetPassword.html?token=$token";


// Send email with PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = EMAIL_USER;
    $mail->Password   = EMAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom(EMAIL_USER, 'Attendance System');
    $mail->addAddress($email);

    $mail->isHTML(false);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "Click the link to reset your password: $resetLink";

    $mail->send();

    echo json_encode(["message" => "Password reset link sent to your email."]);
} catch (Exception $e) {
    echo json_encode(["error" => "Mailer Error: {$mail->ErrorInfo}"]);
}

// Close statements and connection
$stmt->close();
$updateStmt->close();
$mysqli->close();
