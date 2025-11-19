<?php

session_start();

$host="localhost";
$user="root";
$password="";
$database="projects";

$conn=new mysqli($host,$user,$password,$database);

if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

if (isset($_POST["create_card"])) {
    $card_name = $_POST["card_name"];
    $num_of_user = $_POST["number_of_users"];
    $num_of_qst = $_POST["number_of_question"];

    $conn->query("INSERT INTO cards (number_of_users,card_name,number_of_question) VALUES ('$num_of_user','$card_name','$num_of_qst') ");

}