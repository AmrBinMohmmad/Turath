<?php
session_start();

$host = "sql206.infinityfree.com";
$db_user = "if0_40458841";
$db_password = "PoweR135";
$database = "if0_40458841_projects";

$conn = new mysqli($host, $db_user, $db_password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
