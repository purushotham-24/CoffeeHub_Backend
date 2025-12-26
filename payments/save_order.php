<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../auth/db.php";


if (!$conn) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode([
        "error" => "No JSON received",
        "raw" => $raw
    ]);
    exit;
}

$user_id  = intval($data["user_id"] ?? 0);
$order_id = trim($data["order_id"] ?? "");
$items    = intval($data["items"] ?? 0);
$total    = floatval($data["total"] ?? 0);
$status   = $data["status"] ?? "Completed";

if ($user_id <= 0 || empty($order_id) || $total <= 0) {
    echo json_encode([
        "error" => "Invalid values",
        "data" => $data
    ]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO payments (user_id, order_id, items, total, status)
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode([
        "error" => "Prepare failed",
        "sql_error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param("isids", $user_id, $order_id, $items, $total, $status);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "Payment saved successfully"
    ]);
} else {
    echo json_encode([
        "error" => "Execute failed",
        "sql_error" => $stmt->error
    ]);
}
