<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../auth/db.php";

$data = json_decode(file_get_contents("php://input"), true);
$booking_id = intval($data["booking_id"] ?? 0);

if ($booking_id <= 0) {
    echo json_encode(["status"=>false,"message"=>"Invalid booking id"]);
    exit;
}

$conn->begin_transaction();

try {

    $res = $conn->query(
        "SELECT seat_id FROM booking_seats WHERE booking_id=$booking_id"
    );

    while ($row = $res->fetch_assoc()) {
        $seat = $row["seat_id"];
        $conn->query(
            "UPDATE seats SET status='available' WHERE seat_id='$seat'"
        );
    }

    $conn->query("DELETE FROM bookings WHERE id=$booking_id");

    $conn->commit();

    echo json_encode([
        "status"=>true,
        "message"=>"Booking cancelled"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "status"=>false,
        "message"=>$e->getMessage()
    ]);
}
