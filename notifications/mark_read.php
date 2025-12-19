<?php
header("Content-Type: application/json");
require_once "../auth/db.php";

$data = json_decode(file_get_contents("php://input"), true);
$notif_id = intval($data["notification_id"] ?? 0);

if ($notif_id <= 0) {
    echo json_encode(["status" => false, "message" => "Invalid notification"]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE notifications SET is_read = 1 WHERE id = ?"
);
$stmt->bind_param("i", $notif_id);

$stmt->execute();

echo json_encode([
    "status" => true,
    "message" => "Marked as read"
]);
