<?php

$host="sql206.infinityfree.com";

$user="if0_40458841";

$password="PoweR135";

$database="if0_40458841_users_db";



$conn=new mysqli($host,$user,$password,$database);



if ($conn->connect_error) {

    die("Connection failed: ". $conn->connect_error);



}



