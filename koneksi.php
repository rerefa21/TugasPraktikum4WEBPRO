<?php

$host     = "localhost";
$username = "root";
$password = "";
$database = "smartwaste";


$conn = new mysqli(
    hostname: $host,
    username: $username,
    password: $password,
    database: $database
);


if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
