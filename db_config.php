<?php
$host = "sql305.infinityfree.com";
$db_user = "if0_42260330";
$db_pass = "NRBKqP6J1yq";
$db_name = "if0_42260330_matrongroceryDb1";
$db_port = 3306;

$conn = new mysqli(
    $host,
    $db_user,
    $db_pass,
    $db_name,
    $db_port
);

if ($conn->connect_error) {

    header("Content-Type: application/json");

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);

    exit;
}

$conn->set_charset("utf8");

?>
