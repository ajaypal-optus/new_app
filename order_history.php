<?php
include "db.php";
header("Content-Type: application/json");

$user_id = (int)$_GET['user_id'];

$sql = "SELECT o.id as order_id, o.total_amount, o.status, o.created_at,
               oi.product_id, oi.quantity, oi.price
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = $user_id
        ORDER BY o.id DESC";

$res = $conn->query($sql);

$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>