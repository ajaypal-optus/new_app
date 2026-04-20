<?php
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['order_id']) || !isset($data['razorpay_payment_id'])) {
    echo json_encode(["error" => "Missing data"]);
    exit;
}

$order_id = (int)$data['order_id'];
$payment_id = $conn->real_escape_string($data['razorpay_payment_id']);


$sql = "UPDATE orders SET status='paid', payment_id='$payment_id' WHERE id=$order_id";

if ($conn->query($sql)) {
    echo json_encode([
        "message" => "Payment successful",
        "order_id" => $order_id,
        "payment_id" => $payment_id
    ]);
} else {
    echo json_encode(["error" => $conn->error]);
}
?>