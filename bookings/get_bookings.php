<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "../auth/db.php";

$user_id = intval($_GET["user_id"] ?? 0);

if ($user_id <= 0) {
    echo json_encode(["status"=>false,"data"=>[]]);
    exit;
}

$res = $conn->query(
    "SELECT * FROM bookings WHERE user_id=$user_id ORDER BY id DESC"
);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $data
]);