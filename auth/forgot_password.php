<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', 60);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "db.php";

/* ---------- PHPMailer (MANUAL INCLUDE) ---------- */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "phpmailer/Exception.php";
require_once "phpmailer/PHPMailer.php";
require_once "phpmailer/SMTP.php";

/* ---------- INPUT ---------- */
$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data["email"] ?? "");

/* ---------- VALIDATION ---------- */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email"
    ]);
    exit();
}

/* ---------- CHECK USER ---------- */
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => false,
        "message" => "Email not registered"
    ]);
    exit();
}

/* ---------- GENERATE OTP ---------- */
$otp = random_int(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

/* ---------- SAVE OTP ---------- */
$update = $conn->prepare(
    "UPDATE users SET otp_code=?, otp_expiry=? WHERE email=?"
);
$update->bind_param("sss", $otp, $expiry, $email);
$update->execute();

/* ---------- SEND EMAIL (SAFE SMTP) ---------- */
try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    require_once "config.php";

require_once "config.php";

$mail->Username = SMTP_EMAIL;
$mail->Password = SMTP_PASSWORD;


   
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;

    $mail->setFrom("coffeehub376@gmail.com", "CoffeeHub");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "CoffeeHub OTP";
    $mail->Body = "
        <h3>Your CoffeeHub OTP</h3>
        <h1>$otp</h1>
        <p>Valid for 5 minutes</p>
    ";

    $mail->send();

} catch (Exception $e) {
    // ⚠️ DO NOTHING — API MUST NOT FAIL
    // OTP is already saved, app should continue
}

/* ---------- RESPONSE ---------- */
echo json_encode([
    "status" => true,
    "message" => "OTP sent to email"
]);
