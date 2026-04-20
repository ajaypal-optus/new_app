<?php
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$order_id = (int)$data['order_id'];
$status = $data['status'];

$conn->query("UPDATE orders SET status='$status' WHERE id=$order_id");

echo json_encode(["message" => "Status updated"]);
?>