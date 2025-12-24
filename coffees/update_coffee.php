<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once "../auth/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data["id"] ?? "";
$name = $data["name"] ?? "";
$description = $data["description"] ?? "";
$price = $data["price"] ?? 0;
$image = $data["image"] ?? "";
$category = $data["category"] ?? "";

if ($id == "") {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Missing ID"]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE coffees
     SET name=?, description=?, price=?, image=?, category=?
     WHERE id=?"
);
$stmt->bind_param("ssisss", $name, $description, $price, $image, $category, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Coffee updated"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Update failed"]);
}
