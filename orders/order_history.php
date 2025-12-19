<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "../auth/db.php";

$user_id = intval($_GET['user_id'] ?? 0);

if ($user_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "User ID required",
        "data" => []
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT 
        order_id,
        DATE_FORMAT(order_date, '%b %d, %Y') AS date,
        DATE_FORMAT(order_time, '%h:%i %p') AS time,
        items,
        total,
        status
     FROM orders
     WHERE user_id=?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $data
]);
