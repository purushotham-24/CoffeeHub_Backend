<?php
header("Content-Type: application/json");
include(__DIR__ . "/db.php");

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$google_id = $data['google_id'] ?? '';

if ($email === '' || $google_id === '') {
    echo json_encode([
        "status" => false,
        "userId" => -1,
        "message" => "Invalid Google data"
    ]);
    exit;
}

// Check by google_id OR email
$stmt = $conn->prepare(
    "SELECT id FROM users WHERE google_id = ? OR email = ?"
);
$stmt->bind_param("ss", $google_id, $email);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    echo json_encode([
        "status" => true,
        "userId" => (int)$row['id']
    ]);
    exit;
}

// Insert new Google user
$stmt = $conn->prepare(
    "INSERT INTO users 
     (name, email, auth_provider, google_id, created_at)
     VALUES (?, ?, 'google', ?, NOW())"
);
$stmt->bind_param("sss", $name, $email, $google_id);
$stmt->execute();

echo json_encode([
    "status" => true,
    "userId" => (int)$conn->insert_id
]);
