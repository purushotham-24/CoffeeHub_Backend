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
    "SELECT id, title, message, is_read, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        "id" => $row["id"],
        "title" => $row["title"],
        "message" => $row["message"],
        "unread" => $row["is_read"] == 0,
        "time" => date("d M Y, h:i A", strtotime($row["created_at"]))
    ];
}

echo json_encode([
    "status" => true,
    "data" => $notifications
]);
