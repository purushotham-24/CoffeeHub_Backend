<?php
header("Content-Type: application/json");
require_once "../auth/db.php";


$data = json_decode(file_get_contents("php://input"), true);
$user_id = intval($data["user_id"] ?? 0);

if ($user_id <= 0) {
    echo json_encode(["status" => false, "message" => "Invalid user"]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT name, email, phone, dob FROM users WHERE id=?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "status" => true,
        "data" => $row
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "User not found"
    ]);
}
