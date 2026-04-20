<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

function response($data) {
    echo json_encode($data);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $res = $conn->query("SELECT * FROM categories");

    if (!$res) {
        response(["error" => $conn->error]);
    }

    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }

    response($data);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        $data = $_POST;
    }

    if (!$data || count($data) === 0) {
        response(["error" => "No data received"]);
    }

    if (empty($data['name'])) {
        response(["error" => "Name is required"]);
    }

    $name = $conn->real_escape_string(trim($data['name']));

   
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
    $slug = $conn->real_escape_string($slug);

  
    $imgPath = "";

    if (!empty($_FILES['image']['name'])) {

        $uploadDir = "uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imgPath = $conn->real_escape_string($fileName);
        } else {
            response(["error" => "Image upload failed"]);
        }
    }

    $sql = "INSERT INTO categories (name, slug, image) 
            VALUES ('$name', '$slug', '$imgPath')";

    if (!$conn->query($sql)) {
        response(["error" => $conn->error]);
    }

    response([
        "message" => "Category inserted",
        "id" => $conn->insert_id,
        "image" => $imgPath
    ]);
}



if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    if (!isset($_GET['id'])) {
        response(["error" => "ID required"]);
    }

    $id = intval($_GET['id']);

   
    $res = $conn->query("SELECT image FROM categories WHERE id = $id");

    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['image'])) {
            $filePath = "uploads/" . $row['image'];

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

   
    $sql = "DELETE FROM categories WHERE id = $id";

    if (!$conn->query($sql)) {
        response(["error" => $conn->error]);
    }

    response(["message" => "Category deleted"]);
}