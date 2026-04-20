<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $res = $conn->query("SELECT * FROM products");
    $data = [];

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = $_POST;

   
    // print_r($data); exit;

    if (!$data) {
        echo json_encode(["error" => "No form-data received"]);
        exit;
    }

   
    $data = array_combine(
        array_map('trim', array_keys($data)),
        $data
    );

    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

        $uploadDir = "uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $data['image'] = $imageName;
        } else {
            echo json_encode(["error" => "Image upload failed"]);
            exit;
        }
    }

  
    $data['category_id'] = isset($data['category_id']) && $data['category_id'] !== ''
        ? (int)$data['category_id']
        : 0;

    $data['price'] = isset($data['price']) ? (float)$data['price'] : 0;
    $data['discount_price'] = isset($data['discount_price']) ? (float)$data['discount_price'] : 0;
    $data['stock'] = isset($data['stock']) ? (int)$data['stock'] : 0;
    $data['is_trending'] = isset($data['is_trending']) ? (int)$data['is_trending'] : 0;
    $data['is_best_seller'] = isset($data['is_best_seller']) ? (int)$data['is_best_seller'] : 0;

    
    $cols = [];
    $vals = [];

    foreach ($data as $key => $value) {
        $cols[] = $key;
        $vals[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
    }

    if (empty($cols)) {
        echo json_encode(["error" => "No valid fields"]);
        exit;
    }

    $columns = implode(",", $cols);
    $values = implode(",", $vals);

    $sql = "INSERT INTO products ($columns) VALUES ($values)";

    if ($conn->query($sql)) {
        echo json_encode([
            "message" => "Product inserted successfully",
            "category_id" => $data['category_id'],
            "image" => $data['image'] ?? null
        ]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }

    exit;
}



if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    parse_str($_SERVER['QUERY_STRING'], $params);

    if (!isset($params['id'])) {
        echo json_encode(["error" => "ID required"]);
        exit;
    }

    $id = (int)$params['id'];

    $sql = "DELETE FROM products WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["message" => "Product deleted"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }

    exit;
}
?>