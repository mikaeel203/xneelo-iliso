<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Remove in production

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$db_host = 'sql17.cpt2.host-h.net';
$db_user = 'lcstufzyrz_20';
$db_password = 'iJ5E5HW9GhSfvnhZNti8';
$db_name = 'lcstufzyrz_db20';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$sql = "SELECT 
            Staff.tag_id,
            Staff.name,
            Staff.email,
            Staff.department,
            onsite.timestamp AS check_in_time,
            onsite.Active AS status,
            onsite.sign_out_date
        FROM Staff
        LEFT JOIN onsite ON Staff.tag_id = onsite.tag_id";

$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
$conn->close();
?>
