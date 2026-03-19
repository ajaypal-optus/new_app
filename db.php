<?php
$host = "localhost";
$user_name = "root";
$password = "";
$db_name = "your_database_name";

$con = mysqli_connect($host, $user_name, $password, $db_name);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>