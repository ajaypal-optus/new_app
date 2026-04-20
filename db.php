<?php
$conn = new mysqli("localhost", "root", "", "foodwebsite");

if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed"]));
}

header("Content-Type: application/json");
?>
