<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../db.php";

/* ✅ READ RAW JSON */
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

/* ❌ INVALID JSON */
if (!$data) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid JSON",
        "raw" => $raw
    ]);
    exit();
}

/* ✅ EXTRACT FIELDS */
$user_id  = intval($data["user_id"] ?? 0);
$order_id = trim($data["order_id"] ?? "");
$amount   = floatval($data["amount"] ?? 0);
$method   = trim($data["method"] ?? "");
$status   = trim($data["status"] ?? "SUCCESS");

/* ❌ VALIDATION */
if ($user_id <= 0 || $order_id === "" || $amount <= 0 || $method === "") {
    echo json_encode([
        "status" => false,
        "message" => "Missing required fields",
        "data" => $data
    ]);
    exit();
}

/* ✅ INSERT */
$stmt = $conn->prepare(
    "INSERT INTO payments (user_id, order_id, amount, method, status)
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "isdss",
    $user_id,
    $order_id,
    $amount,
    $method,
    $status
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "Payment saved successfully"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "DB insert failed",
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
