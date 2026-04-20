<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $res = $conn->query("SELECT * FROM cart_items");
    $data = [];

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
    } else {
        echo json_encode(["error" => $conn->error]);
        exit;
    }

    echo json_encode($data);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["error" => "Invalid or empty JSON"]);
        exit;
    }

    
    if (!isset($data['user_id']) || !isset($data['product_id'])) {
        echo json_encode(["error" => "user_id and product_id are required"]);
        exit;
    }

    
    $user_id = (int)$data['user_id'];
    $product_id = (int)$data['product_id'];
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

   
    $checkUser = $conn->query("SELECT id FROM users WHERE id = $user_id");
    if (!$checkUser || $checkUser->num_rows == 0) {
        echo json_encode(["error" => "Invalid user_id"]);
        exit;
    }

    $checkProduct = $conn->query("SELECT id FROM products WHERE id = $product_id");
    if (!$checkProduct || $checkProduct->num_rows == 0) {
        echo json_encode(["error" => "Invalid product_id"]);
        exit;
    }

    $sql = "INSERT INTO cart_items (user_id, product_id, quantity)
            VALUES (
                $user_id,
                $product_id,
                $quantity
            )";

    if ($conn->query($sql)) {
        echo json_encode([
            "message" => "cart item inserted successfully",
            "user_id" => $user_id,
            "product_id" => $product_id,
            "quantity" => $quantity
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

    $sql = "DELETE FROM cart_items WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["message" => "cart item deleted"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }

    exit;
}
?>