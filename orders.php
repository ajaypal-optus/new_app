<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

header("Content-Type: application/json");


if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $res = $conn->query("SELECT * FROM orders ORDER BY id DESC");
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

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        echo json_encode(["error" => "Invalid JSON"]);
        exit;
    }

    
    if (!isset($input['user_id']) || !isset($input['total_amount'])) {
        echo json_encode(["error" => "user_id and total_amount are required"]);
        exit;
    }

    $user_id = (int)$input['user_id'];
    $total_amount = (float)$input['total_amount'];
    $status = isset($input['status']) ? $conn->real_escape_string($input['status']) : 'pending';

    $checkUser = $conn->query("SELECT id FROM users WHERE id = $user_id");
    if (!$checkUser || $checkUser->num_rows == 0) {
        echo json_encode(["error" => "Invalid user_id"]);
        exit;
    }

    $sql = "INSERT INTO orders (user_id, total_amount, status)
            VALUES ($user_id, $total_amount, '$status')";

    if ($conn->query($sql)) {
        echo json_encode([
            "message" => "Order created successfully",
            "order_id" => $conn->insert_id
        ]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }

    exit;
}



if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    if (!isset($_GET['id'])) {
        echo json_encode(["error" => "Order ID required"]);
        exit;
    }

    $id = (int)$_GET['id'];

    
    $check = $conn->query("SELECT id FROM orders WHERE id = $id");
    if (!$check || $check->num_rows == 0) {
        echo json_encode(["error" => "Invalid order_id"]);
        exit;
    }

    $sql = "DELETE FROM orders WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["message" => "Order deleted successfully"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }

    exit;
}
?>