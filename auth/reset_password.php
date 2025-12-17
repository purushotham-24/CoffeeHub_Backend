<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$otp = trim($data["otp"] ?? "");
$password = trim($data["password"] ?? "");

if (!$email || !$otp || !$password) {
    echo json_encode(["status" => false, "message" => "All fields required"]);
    exit();
}

$stmt = $conn->prepare(
    "SELECT otp_code, otp_expiry FROM users WHERE email=?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status" => false, "message" => "User not found"]);
    exit();
}

$row = $result->fetch_assoc();

if ($row["otp_code"] != $otp) {
    echo json_encode(["status" => false, "message" => "Invalid OTP"]);
    exit();
}

if (strtotime($row["otp_expiry"]) < time()) {
    echo json_encode(["status" => false, "message" => "OTP expired"]);
    exit();
}

$hashed = password_hash($password, PASSWORD_BCRYPT);

$update = $conn->prepare(
    "UPDATE users SET password=?, otp_code=NULL, otp_expiry=NULL WHERE email=?"
);
$update->bind_param("ss", $hashed, $email);
$update->execute();

echo json_encode([
    "status" => true,
    "message" => "Password reset successful"
]);
