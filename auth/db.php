<?php
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "coffeehub";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "Database connection failed"
    ]);
    exit;
}
?>
