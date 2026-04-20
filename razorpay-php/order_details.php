<?php
include "db.php";

$order_id = (int)$_GET['order_id'];

$res = $conn->query("SELECT * FROM orders WHERE id = $order_id");

if ($res->num_rows == 0) {
    echo json_encode(["error" => "Order not found"]);
    exit;
}

echo json_encode($res->fetch_assoc());
?>