<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "mysql.railway.internal";
$db_user = "root";
$db_pass = "ODdiTdUeXiJkRZraKoObScKhLybYReLV";
$db_name = "railway";
$db_port = 31364;

$conn = new mysqli(
    $host,
    $db_user,
    $db_pass,
    $db_name,
    $db_port
);

if ($conn->connect_error) {

    die(
        "Database connection failed: " .
        $conn->connect_error
    );

}

$conn->set_charset("utf8mb4");

?>
