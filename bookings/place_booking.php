<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../auth/db.php";

/* ---------- READ RAW JSON ---------- */
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

/* ---------- SAFETY ---------- */
if (!$data) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid JSON",
        "raw" => $raw
    ]);
    exit;
}

$user_id = intval($data["user_id"] ?? 0);
$type    = $data["type"] ?? "";
$title   = $data["title"] ?? "";
$date    = $data["date"] ?? "";
$time    = $data["time"] ?? "";
$seats   = $data["seats"] ?? [];

if ($user_id <= 0 || $type === "" || $date === "" || $time === "") {
    echo json_encode([
        "status" => false,
        "message" => "Missing required fields",
        "data" => $data
    ]);
    exit;
}

$conn->begin_transaction();

try {

    // 1️⃣ Insert booking
    $stmt = $conn->prepare(
        "INSERT INTO bookings (user_id, type, title, booking_date, booking_time)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $user_id, $type, $title, $date, $time);
    $stmt->execute();

    $booking_id = $stmt->insert_id;

    // 2️⃣ Seat booking
    if ($type === "seat") {
        foreach ($seats as $seat) {

            $check = $conn->query(
                "SELECT status FROM seats WHERE seat_id='$seat' FOR UPDATE"
            )->fetch_assoc();

            if ($check && $check["status"] === "occupied") {
                throw new Exception("Seat $seat already booked");
            }

            $conn->query(
                "INSERT INTO booking_seats (booking_id, seat_id)
                 VALUES ($booking_id, '$seat')"
            );

            $conn->query(
                "UPDATE seats SET status='occupied' WHERE seat_id='$seat'"
            );
        }
    }

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Booking saved",
        "booking_id" => $booking_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}