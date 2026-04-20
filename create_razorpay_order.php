<?php
include "db.php";

header("Content-Type: application/json");

$key_id = "rzp_test_SfeWnuAH9CFuJD";
$key_secret = "uAJLO6G7A57sCqkWw0ujy2Mh";

$res = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 1");

if ($res->num_rows == 0) {
    echo json_encode(["error" => "No order found"]);
    exit;
}

$order = $res->fetch_assoc();

$order_id = $order['id'];
$amount = $order['total_amount'] * 100;

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "amount" => $amount,
    "currency" => "INR",
    "receipt" => "order_" . $order_id
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);


curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["error" => curl_error($ch)]);
    exit;
}

$result = json_decode($response, true);

if (!isset($result['id'])) {
    echo json_encode(["error" => $response]);
    exit;
}

echo json_encode([
    "razorpay_order_id" => $result['id'],
    "amount" => $amount,
    "order_id" => $order_id
]);
?>