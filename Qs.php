<?php 
$jsonString = file_get_contents("Qs.json");
$json = json_decode($jsonString, true);
$questions = $json['data'];

?>