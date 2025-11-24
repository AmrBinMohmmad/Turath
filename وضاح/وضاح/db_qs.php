<?php
$host="sql206.infinityfree.com";
$user="if0_40458841";
$password="PoweR135";
$database="if0_40458841_questions_db";

$conn_qs = new mysqli($host, $user, $password, $database);

if ($conn_qs->connect_error) {
    die("Connection failed: ". $conn_qs->connect_error);
}

$conn_qs->set_charset("utf8mb4");
?>
