<?php
$serverName = "localhost";
$username = "root";
$password = "090080";
$db_name = "housing_db";

$conn = new mysqli($serverName, $username, $password, $db_name);

if ($conn->connect_error) {
    die("connection error" . $conn->connect_error);
}
