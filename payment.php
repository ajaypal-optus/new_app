<?php
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['order_id'])) {
    echo json_encode(["error" => "order_id required"]);
    exit;
}

$order_id = (int)$data['order_id'];

$conn->query("UPDATE orders SET status='paid' WHERE id=$order_id");

echo json_encode([
    "message" => "Payment successful",
    "order_id" => $order_id
]);
?>