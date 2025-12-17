<?php
include "db.php";

$input = json_decode(file_get_contents("php://input"), true);

$email    = trim($input["email"] ?? "");
$password = $input["password"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "Invalid email format"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, name, password FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "User not found"
    ]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password"])) {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "Incorrect password"
    ]);
    exit;
}

echo json_encode([
    "status" => true,
    "data" => [
        "user_id" => $user["id"],
        "name" => $user["name"]
    ],
    "message" => "Login successful"
]);
