<?php
include "db.php";

// GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $res = $conn->query("SELECT * FROM product_images");
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
}

// POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $keys = array_keys($data);
    $values = array_values($data);

    $cols = implode(",", $keys);
    $vals = "'" . implode("','", $values) . "'";

    $conn->query("INSERT INTO product_images($cols) VALUES($vals)");
    echo json_encode(["message" => "product_images inserted"]);
}

// DELETE
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $_GET['id'];
    $conn->query("DELETE FROM product_images WHERE id=$id");
    echo json_encode(["message" => "product_images deleted"]);
}
?>
