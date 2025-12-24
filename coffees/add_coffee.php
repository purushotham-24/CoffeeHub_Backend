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

if ($id == "" || $name == "") {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Invalid data"]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO coffees (id, name, description, price, image, category)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssiss", $id, $name, $description, $price, $image, $category);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Coffee added"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Insert failed"]);
}
