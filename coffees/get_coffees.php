<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "../auth/db.php";

$result = $conn->query("SELECT * FROM coffees ORDER BY created_at DESC");

$coffees = [];

while ($row = $result->fetch_assoc()) {
    $coffees[] = [
        "id" => $row["id"],
        "name" => $row["name"],
        "description" => $row["description"],
        "price" => (int)$row["price"],
        "image" => $row["image"],
        "category" => $row["category"]
    ];
}

echo json_encode($coffees);
