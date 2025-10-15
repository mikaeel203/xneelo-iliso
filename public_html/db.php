<?php

// db.php (frontend)
$db_host = 'sql17.cpt2.host-h.net';
$db_user = 'lcstufzyrz_20';
$db_password = 'iJ5E5HW9GhSfvnhZNti8';
$db_name = 'lcstufzyrz_db20';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("<p style='color: red; font-weight: bold;'>Unable to connect to the database. Please try again later.</p>");
}
?>