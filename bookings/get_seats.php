<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "../auth/db.php";

$res = $conn->query("SELECT seat_id, status FROM seats");

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $data
]);