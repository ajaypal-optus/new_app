<?php
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["error" => "user_id required"]);
    exit;
}

$user_id = (int)$data['user_id'];

$res = $conn->query("SELECT * FROM cart WHERE user_id = $user_id");

if ($res->num_rows == 0) {
    echo json_encode(["error" => "Cart is empty"]);
    exit;
}

$conn->begin_transaction();

try {

    $total_amount = 0;
    $items = [];

    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
        $total_amount += $row['total'];
    }

    
    $conn->query("INSERT INTO orders (user_id, total_amount, status)
                  VALUES ($user_id, $total_amount, 'pending')");

    $order_id = $conn->insert_id;

    
    foreach ($items as $item) {

        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price)
                      VALUES (
                        $order_id,
                        {$item['product_id']},
                        {$item['quantity']},
                        {$item['price']}
                      )");
    }

   
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    $conn->commit();

    echo json_encode([
        "message" => "Order created",
        "order_id" => $order_id,
        "total_amount" => $total_amount
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode(["error" => $e->getMessage()]);
}
?>