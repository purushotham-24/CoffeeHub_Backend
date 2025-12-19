<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../auth/db.php";

/* Read JSON body */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status"=>false,"message"=>"Invalid JSON"]);
    exit;
}

$user_id  = intval($data['user_id'] ?? 0);
$order_id = trim($data['order_id'] ?? "");
$items    = intval($data['items'] ?? 0);
$total    = intval($data['total'] ?? 0);
$status   = trim($data['status'] ?? "Completed");

if ($user_id <= 0 || empty($order_id)) {
    echo json_encode(["status"=>false,"message"=>"Missing parameters"]);
    exit;
}

$date = date("Y-m-d");
$time = date("H:i:s");

/* Prevent duplicate order */
$check = $conn->prepare("SELECT id FROM orders WHERE order_id=?");
$check->bind_param("s", $order_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status"=>false,"message"=>"Order already exists"]);
    exit;
}

/* Insert order */
$stmt = $conn->prepare(
    "INSERT INTO orders
     (order_id, user_id, items, total, status, order_date, order_time)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "siiisss",   // ✅ FIXED
    $order_id,
    $user_id,
    $items,
    $total,
    $status,
    $date,
    $time
);

if ($stmt->execute()) {
    echo json_encode(["status"=>true,"message"=>"Order placed successfully"]);
} else {
    echo json_encode(["status"=>false,"message"=>$stmt->error]);
}
