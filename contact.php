<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

echo json_encode([
  "success" => true,
  "message" => "PHP API running on Render 🚀",
  "data" => $data
]);
?>