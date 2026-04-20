<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["error" => "Invalid JSON"]);
        exit;
    }


    if (!isset($data['user_id']) || !isset($data['items']) || !is_array($data['items'])) {
        echo json_encode(["error" => "user_id and items array required"]);
        exit;
    }

    $user_id = (int)$data['user_id'];
    $items = $data['items'];

    if (count($items) == 0) {
        echo json_encode(["error" => "Items cannot be empty"]);
        exit;
    }


    $checkUser = $conn->query("SELECT id FROM users WHERE id = $user_id");
    if (!$checkUser || $checkUser->num_rows == 0) {
        echo json_encode(["error" => "Invalid user_id"]);
        exit;
    }


    $conn->begin_transaction();

    try {

        $total_amount = 0;


        foreach ($items as $item) {

            if (!isset($item['product_id'])) {
                throw new Exception("product_id missing in items");
            }

            $product_id = (int)$item['product_id'];
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;

            $res = $conn->query("SELECT price FROM products WHERE id = $product_id");

            if (!$res || $res->num_rows == 0) {
                throw new Exception("Invalid product_id: $product_id");
            }

            $product = $res->fetch_assoc();
            $price = (float)$product['price'];

            $total_amount += ($price * $quantity);
        }


        $status = "pending";

        $sqlOrder = "INSERT INTO orders (user_id, total_amount, status)
                     VALUES ($user_id, $total_amount, '$status')";

        if (!$conn->query($sqlOrder)) {
            throw new Exception($conn->error);
        }

        $order_id = $conn->insert_id;


        foreach ($items as $item) {

            $product_id = (int)$item['product_id'];
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;

            $res = $conn->query("SELECT price FROM products WHERE id = $product_id");
            $product = $res->fetch_assoc();
            $price = (float)$product['price'];

            $sqlItem = "INSERT INTO order_items (order_id, product_id, quantity, price)
                        VALUES ($order_id, $product_id, $quantity, $price)";

            if (!$conn->query($sqlItem)) {
                throw new Exception($conn->error);
            }
        }


        $conn->commit();

        echo json_encode([
            "message" => "Order and items created successfully",
            "order_id" => $order_id,
            "total_amount" => $total_amount
        ]);
    } catch (Exception $e) {

        $conn->rollback();

        echo json_encode([
            "error" => $e->getMessage()
        ]);
    }

    exit;
}
