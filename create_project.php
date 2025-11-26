<?php
// create_project.php
require "config2.php";

if($_SERVER['REQUEST_METHOD']==='POST'){
    $card_name = $conn->real_escape_string($_POST['card_name']);
    $num_users = intval($_POST['number_of_users']);
    $num_q = intval($_POST['number_of_question']);

    // 1. ����� ������
    $conn->query("INSERT INTO cards (card_name, number_of_users, number_of_question) VALUES ('$card_name', $num_users, $num_q)");
    $card_id = $conn->insert_id;

    // 2. ��� ����� �������
    $words = $conn_qs->query("SELECT id FROM words_db ORDER BY RAND() LIMIT $num_q");
    
    if ($words) {
        while($w = $words->fetch_assoc()){
            // ��� ������ �������
            $conn->query("INSERT INTO cards_questions (card_id, question_id) VALUES ($card_id, {$w['id']})");
        }
    }

    header("Location: admin_page.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Create Project</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h2>Create New Project</h2>
<form method="post" class="card">
    <label>Project Name:<br><input type="text" name="card_name" required></label><br><br>
    <label>Number of Users:<br><input type="number" name="number_of_users" min="1" required></label><br><br>
    <label>Number of Questions:<br><input type="number" name="number_of_question" min="1" required></label><br><br>
    
    <button class="button" type="submit">Create Project</button>
    <a class="button secondary" href="admin_page.php" style="text-align:center; display:block; margin-top:10px; background:#ccc;">Cancel</a>
</form>
</div>
</body>
</html>