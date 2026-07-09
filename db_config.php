<?php

$host = "hayabusa.proxy.rlwy.net";
$db_user = "root";
$db_pass = "ODdiTdUeXiJkRZraKoObScKhLybYReLV";
$db_name = "railway";
$db_port = 3306;


error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$conn->set_charset("utf8");

?>
