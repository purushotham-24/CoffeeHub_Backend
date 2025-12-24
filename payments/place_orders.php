<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

/* ✅ HANDLE PREFLIGHT (VERY IMPORTANT FOR ANDROID) */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../auth/db.php";

/* ✅ READ JSON BODY */
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid JSON",
        "raw" => $raw
    ]);
    exit;
}

$user_id  = intval($data["user_id"] ?? 0);
$order_id = $data["order_id"] ?? "";
$items    = intval($data["items"] ?? 0);
$total    = floatval($data["total"] ?? 0);
$status   = $data["status"] ?? "";

if ($user_id <= 0 || $order_id === "" || $total <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Missing fields",
        "data" => $data
    ]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO orders (user_id, order_id, items, total, status)
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param("isids", $user_id, $order_id, $items, $total, $status);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "Order saved successfully"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => $stmt->error
    ]);
}
