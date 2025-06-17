<?php
// TEAM A 🌟

// index.php

// Set CORS headers for the frontend origin.
// Make sure this matches the origin of your frontend (e.g., http://127.0.0.1:5501).
header("Access-Control-Allow-Origin: httpS://127.0.0.1:5501");

// Allow common HTTP methods for potential future expansion or consistency.
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Allow common headers that might be sent by the frontend.
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Allow credentials (like cookies or HTTP authentication) if your frontend needs them.
header("Access-Control-Allow-Credentials: true");

// Set the response content type to JSON.
// Removed duplicate 'Content-Type' header.
header("Content-Type: application/json");

// Explicitly set a 200 OK status code, though it's often the default for success.
http_response_code(200);

// Output the JSON response with API information.
echo json_encode([
    "message" => "Welcome to the Admin Management PHP Backend API",
    "status" => "OK",
    "available_routes" => [
        "POST /admin.php?action=signup" => "Create a new admin (requires main admin password)",
        "GET /admin.php?action=getAll" => "Fetch all admins",
        "GET /admin.php?action=get&username={username}" => "Fetch a specific admin by username",
        "GET /admin.php?action=get&id={id}" => "Fetch a specific admin by ID",
        "PUT /admin.php?action=update&username={username}" => "Update admin details by username",
        "PUT /admin.php?action=update&id={id}" => "Update admin details by ID",
        "DELETE /admin.php?action=delete&id={id}" => "Delete an admin by ID",
        "DELETE /admin.php?action=delete&username={username}" => "Delete an admin by username",
        "POST /login.php?action=login" => "Admin Login"
    ]
]);
