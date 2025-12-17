<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$input = json_decode(file_get_contents("php://input"), true);

$name     = trim($input["name"] ?? "");
$email    = trim($input["email"] ?? "");
$password = $input["password"] ?? "";
$phone    = trim($input["phone"] ?? "");
$dob      = trim($input["dob"] ?? "");

/* ---------- VALIDATION ---------- */

// Name: letters + spaces
if (!preg_match("/^[A-Za-z]+( [A-Za-z]+)*$/", $name)) {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "Name must contain only letters"
    ]);
    exit;
}

// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "Invalid email format"
    ]);
    exit;
}

// Password
if (strlen($password) < 8) {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "Password must be at least 8 characters"
    ]);
    exit;
}

// Phone (optional)
if ($phone && !preg_match("/^\+?[0-9]{10,13}$/", $phone)) {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "Invalid mobile number"
    ]);
    exit;
}

// DOB (optional, must be past)
if ($dob) {
    if (strtotime($dob) >= strtotime(date("Y-m-d"))) {
        echo json_encode([
            "status" => false,
            "data" => null,
            "message" => "DOB must be a past date"
        ]);
        exit;
    }
}

/* ---------- CHECK EMAIL ---------- */

$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "Email already registered"
    ]);
    exit;
}

/* ---------- INSERT USER ---------- */

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password, phone, dob)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssss", $name, $email, $hashedPassword, $phone, $dob);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "data" => null,
        "message" => "Registration successful"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "data" => null,
        "message" => "Registration failed"
    ]);
}
