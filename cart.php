<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

header("Content-Type: application/json");


if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (!isset($_GET['user_id'])) {
        echo json_encode(["error" => "user_id required"]);
        exit;
    }

    $user_id = (int)$_GET['user_id'];

    $res = $conn->query("SELECT * FROM cart WHERE user_id = $user_id");

    $data = [];
    $grand_total = 0;

    while ($row = $res->fetch_assoc()) {
        $grand_total += $row['total'];
        $data[] = $row;
    }

    echo json_encode([
        "items" => $data,
        "grand_total" => $grand_total
    ]);

    exit;
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['user_id']) || !isset($data['product_id'])) {
        echo json_encode(["error" => "user_id and product_id required"]);
        exit;
    }

    $user_id = (int)$data['user_id'];
    $product_id = (int)$data['product_id'];
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

    $res = $conn->query("SELECT price FROM products WHERE id = $product_id");

    if ($res->num_rows == 0) {
        echo json_encode(["error" => "Invalid product"]);
        exit;
    }

    $price = $res->fetch_assoc()['price'];

    $check = $conn->query("SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id");

    if ($check->num_rows > 0) {

        $row = $check->fetch_assoc();
        $new_qty = $row['quantity'] + $quantity;
        $total = $new_qty * $price;

        $conn->query("UPDATE cart SET quantity=$new_qty, total=$total WHERE id=" . $row['id']);

        echo json_encode(["message" => "Cart updated", "quantity" => $new_qty]);

    } else {

        $total = $price * $quantity;

        $conn->query("INSERT INTO cart (user_id, product_id, quantity, price, total)
                      VALUES ($user_id, $product_id, $quantity, $price, $total)");

        echo json_encode(["message" => "Added to cart"]);
    }

    exit;
}



if ($_SERVER['REQUEST_METHOD'] == 'PUT') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['cart_id']) || !isset($data['quantity'])) {
        echo json_encode(["error" => "cart_id and quantity required"]);
        exit;
    }

    $cart_id = (int)$data['cart_id'];
    $quantity = (int)$data['quantity'];

    if ($quantity <= 0) {
        echo json_encode(["error" => "Quantity must be > 0"]);
        exit;
    }

    $res = $conn->query("SELECT price FROM cart WHERE id = $cart_id");

    if ($res->num_rows == 0) {
        echo json_encode(["error" => "Invalid cart_id"]);
        exit;
    }

    $price = $res->fetch_assoc()['price'];
    $total = $price * $quantity;

    $conn->query("UPDATE cart SET quantity=$quantity, total=$total WHERE id=$cart_id");

    echo json_encode(["message" => "Quantity updated"]);

    exit;
}



if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    if (isset($_GET['id'])) {

        $id = (int)$_GET['id'];

        $conn->query("DELETE FROM cart WHERE id = $id");

        echo json_encode(["message" => "Item removed"]);
        exit;
    }

    
    if (isset($_GET['user_id'])) {

        $user_id = (int)$_GET['user_id'];

        $conn->query("DELETE FROM cart WHERE user_id = $user_id");

        echo json_encode(["message" => "Cart cleared"]);
        exit;
    }

    echo json_encode(["error" => "id or user_id required"]);
}
?>