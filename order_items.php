<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

header("Content-Type: application/json");


if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $res = $conn->query("SELECT * FROM order_items ORDER BY id DESC");
    $data = [];

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        echo json_encode(["error" => $conn->error]);
    }

    exit;
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["error" => "Invalid JSON"]);
        exit;
    }

    if (!isset($data['order_id']) || !isset($data['product_id'])) {
        echo json_encode(["error" => "order_id and product_id required"]);
        exit;
    }

    $order_id = (int)$data['order_id'];
    $product_id = (int)$data['product_id'];
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

    
    $checkOrder = $conn->query("SELECT id FROM orders WHERE id = $order_id");
    if (!$checkOrder || $checkOrder->num_rows == 0) {
        echo json_encode(["error" => "Invalid order_id"]);
        exit;
    }

  
    $checkProduct = $conn->query("SELECT price FROM products WHERE id = $product_id");
    if (!$checkProduct || $checkProduct->num_rows == 0) {
        echo json_encode(["error" => "Invalid product_id"]);
        exit;
    }

    $product = $checkProduct->fetch_assoc();
    $price = (float)$product['price'];

    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES ($order_id, $product_id, $quantity, $price)";

    if ($conn->query($sql)) {
        echo json_encode([
            "message" => "Inserted successfully",
            "id" => $conn->insert_id
        ]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }

    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    if (!isset($_GET['id'])) {
        echo json_encode(["error" => "ID required"]);
        exit;
    }

    $id = (int)$_GET['id'];

    $sql = "DELETE FROM order_items WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["message" => "Deleted successfully"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }

    exit;
}
?>