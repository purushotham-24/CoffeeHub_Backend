<?php
header("Content-Type: application/json");
require_once "../auth/db.php";


$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data["user_id"] ?? 0);
$name  = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");

/* ✅ FIX HERE */
$phone = trim($data["phone"] ?? null);
$dob   = trim($data["dob"] ?? null);

if ($phone === "") $phone = null;
if ($dob === "") $dob = null;

if ($user_id <= 0 || empty($email)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid input"
    ]);
    exit;
}

/* ✅ COALESCE keeps old value if NULL */
$stmt = $conn->prepare(
    "UPDATE users 
     SET 
        name=?, 
        email=?, 
        phone = COALESCE(?, phone), 
        dob   = COALESCE(?, dob)
     WHERE id=?"
);

$stmt->bind_param(
    "ssssi",
    $name,
    $email,
    $phone,
    $dob,
    $user_id
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "Profile updated"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Update failed"
    ]);
}
