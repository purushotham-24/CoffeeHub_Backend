<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once "../auth/db.php";

$id = $_GET["id"] ?? "";

if ($id == "") {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Missing ID"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM coffees WHERE id=?");
$stmt->bind_param("s", $id);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Coffee deleted"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Delete failed"]);
}
