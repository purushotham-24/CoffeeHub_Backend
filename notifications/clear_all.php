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
    "DELETE FROM notifications WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode([
    "status" => true,
    "message" => "All notifications cleared"
]);
