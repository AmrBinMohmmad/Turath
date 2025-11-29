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

if ($conn->connect_error) {
    die("Connection failed: " . $conn_qs_bd->connect_error);
}
 $i=1;
if (isset($_POST["create_card"])) {
    $card_name = $_POST["card_name"];
    $num_of_user = $_POST["number_of_users"];
    $num_of_qst = $_POST["number_of_question"];
    
    $dialect_type = $_POST["dialect_type"];

    $conn->query("INSERT INTO cards (number_of_users,card_name,number_of_question,Dialect_type) VALUES ('$num_of_user','$card_name','$num_of_qst','$dialect_type') ");
    $card_id = $conn->insert_id;

    $result = getThreePositiveNumbers($num_of_qst);

    $a = $result[0];
    $b = $result[1];
    $c = $result[2];

    if (!($dialect_type == "all")) {
        print_r("not all");
        // الداتا بيس الاولى 
        $words_db = $conn_qs_bd->query("SELECT id,type_of_questions FROM words_db where Dialect_type='$dialect_type' order by RAND() LIMIT $a");

        //داتا بيس ثانية 
        $proverbs_db=$conn_qs_bd->query("SELECT id,type_of_questions FROM proverbs_db where Dialect_type='$dialect_type' order by RAND() LIMIT $b");

        //داتا بيس ثالثة
        $phrases_db=$conn_qs_bd->query("SELECT id,type_of_questions FROM phrases_db where Dialect_type='$dialect_type' order by RAND() LIMIT $c");
    }else {
        print_r("is all");
        $words_db = $conn_qs_bd->query("SELECT id,type_of_questions FROM words_db order by RAND() LIMIT $a");

        //داتا بيس ثانية 
        $proverbs_db=$conn_qs_bd->query("SELECT id,type_of_questions FROM proverbs_db order by RAND() LIMIT $b");

        //داتا بيس ثالثة
        $phrases_db=$conn_qs_bd->query("SELECT id,type_of_questions FROM phrases_db order by RAND() LIMIT $c");
    }
   
    while ($row = $words_db->fetch_assoc()) {
        $row_t_q = $row['type_of_questions'];
        $row_id_q = $row['id'];

        $conn->query("INSERT INTO cards_questions VALUES ('$card_id','$row_t_q','$row_id_q','$i')");
        $i++;

    }
    while ($row = $proverbs_db->fetch_assoc()) {
        $row_t_q = $row['type_of_questions'];
        $row_id_q = $row['id'];

        $conn->query("INSERT INTO cards_questions VALUES ('$card_id','$row_t_q','$row_id_q','$i')");
        $i++;

    }
    while ($row = $phrases_db->fetch_assoc()) {
        $row_t_q = $row['type_of_questions'];
        $row_id_q = $row['id'];

        $conn->query("INSERT INTO cards_questions VALUES ('$card_id','$row_t_q','$row_id_q','$i')");
        $i++;

    }
    
    header("Location: admin_page.php");
}

function getThreePositiveNumbers($total)
{
    $MIN = 5;      // الحد الأدنى للمتغيرات
    $MAX_B = 82;   // الحد الأعلى لـ b
    $MAX_C = 477;  // الحد الأعلى لـ c

    // يجب ضمان أن total يسمح بوجود 3 أرقام كل واحد ≥ 5
    if ($total < $MIN * 3) {
        return null; // غير ممكن
    }

    // الرقم الأول: من 5 إلى الحد الأعلى الممكن
    $max_a = $total - $MIN - $MIN; 
    $a = rand($MIN, $max_a);

    // الرقم الثاني: من 5 إلى min(82, total - a - 5)
    $max_b = min($MAX_B, $total - $a - $MIN);
    $b = rand($MIN, $max_b);

    // الرقم الثالث يكمل المجموع
    $c = $total - $a - $b;

    // إذا c أقل من الحد الأدنى 5، نعدل
    if ($c < $MIN) {
        $needed = $MIN - $c;

        // نحاول نخصم من b أولاً
        if ($b - $needed >= $MIN) {
            $b -= $needed;
        } 
        // أو نحاول نخصم من a
        elseif ($a - $needed >= $MIN) {
            $a -= $needed;
        }

        $c = $total - $a - $b;
    }

    // إذا c > 477 نعدل
    if ($c > $MAX_C) {
        $excess = $c - $MAX_C;

        // نقلل b أولاً
        if ($b - $excess >= $MIN) {
            $b -= $excess;
        } 
        // ثم a
        elseif ($a - $excess >= $MIN) {
            $a -= $excess;
        }

        $c = $total - $a - $b;

        // آخر حل: قص c إلى 477
        if ($c > $MAX_C) {
            $c = $MAX_C;
            $a = $total - $b - $c;

            // ضمان الحد الأدنى لـ a
            if ($a < $MIN) {
                $a = $MIN;
                $b = $total - $a - $c;
            }
        }
    }

    return [$a, $b, $c];
}





