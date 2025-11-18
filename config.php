<?php
$host="sql204.infinityfree.com";
$user="	if0_40419506";
$password="Abmw123456789";
$database="if0_40419506_users_db";

$conn=new mysqli($host,$user,$password,$database);

if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);

}
