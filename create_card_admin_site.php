<?php

session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "projects";

$conn = new mysqli($host, $user, $password, $database);
$conn_qs_bd = new mysqli("localhost", "root", "", "questions_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST["create_card"])) {
    $card_name = $_POST["card_name"];
    $num_of_user = $_POST["number_of_users"];
    $num_of_qst = $_POST["number_of_question"];

    $conn->query("INSERT INTO cards (number_of_users,card_name,number_of_question) VALUES ('$num_of_user','$card_name','$num_of_qst') ");
    $card_id = $conn->insert_id;

    $result = getThreePositiveNumbers($num_of_qst);
    
    $a=$result[0];
    $b=$result[1];
    $c=$result[2];
    
    print_r($a);
    echo "<br/>";
    print_r($b);
     echo "<br/>";
    print_r($c);
    echo "<br/>";

    // الداتا بيس الاولى 
    $words_db = $conn_qs_bd->query("SELECT id,type_of_questions FROM words_db order by RAND() LIMIT $a");

    //داتا بيس ثانية 
    //$conn_qs_bd->query("SELECT id,type_of_questions FROM words_db order by RAND() LIMIT $b");

    //داتا بيس ثالثة
    //$conn_qs_bd->query("SELECT id,type_of_questions FROM words_db order by RAND() LIMIT $c");

    while ($row = $words_db->fetch_assoc()) {
        $row_t_q=$row['type_of_questions'];
        $row_id_q=$row['id'];
        $conn->query("INSERT INTO cards_questions VALUES ('$card_id','$row_t_q','$row_id_q')");
        
    }   
}

function getThreePositiveNumbers($total)
{ 
    if ($total < 3) {
        // إذا الرقم أقل من 3، ما نقدر نوزعه على ثلاثة أرقام موجبة
        return null;
    }

    // رقم عشوائي أول من 1 إلى total-2
    $a = rand(1, $total - 2);

    // رقم عشوائي ثاني من 1 إلى total - a - 1
    $b = rand(1, $total - $a - 1);

    // الرقم الثالث يكمل المجموع
    $c = $total - $a - $b;

    return [$a, $b, $c];
}






