<?php

require "config2.php";
session_start();

$user_id = $_SESSION['user_id'] ?? 1; 
$project_id = intval($_GET['id'] ?? 0);
$q_index = intval($_GET['q'] ?? 0);


$sql = "SELECT cq.id as link_id, w.id as real_q_id, 
        COALESCE(
            NULLIF(w.Meaning_question, ''),
            NULLIF(w.Location_Recognition_question, ''),
            NULLIF(w.Cultural_Interpretation_question, ''),
            NULLIF(w.Contextual_Usage_question, ''),
            NULLIF(w.Fill_in_Blank_question, ''),
            NULLIF(w.True_False_question, '')
        ) as question_data 
        FROM cards_questions cq 
        JOIN if0_40419506_questions_db.words_db w ON w.id = cq.question_id 
        WHERE cq.card_id=$project_id 
        ORDER BY cq.id ASC";

$qs = $conn->query($sql);

if (!$qs) {
    die("Query Failed: " . $conn->error);
}

$total_q = $qs->num_rows;
if($total_q == 0) die("<div class='container'>No questions found or database is empty.</div>");

if($q_index < 0) $q_index = 0;
if($q_index >= $total_q){ header("Location: user_page.php"); exit; }

$qs->data_seek($q_index);
$current = $qs->fetch_assoc();

if (empty($current['question_data'])) {
    $parsed = ['question' => 'Error: Question data is missing for this ID.', 'options' => [], 'correct' => ''];
} else {
    $parsed = parseQuestionRow($current['question_data']);
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $ans = $conn->real_escape_string($_POST['answer'] ?? '');
    
    $check = $conn->query("SELECT id FROM annotations WHERE user_id=$user_id AND project_id=$project_id AND question_id={$current['real_q_id']}");
    
    if($check->num_rows == 0){
        $conn->query("INSERT INTO annotations (project_id, user_id, question_id, answer) VALUES ($project_id, $user_id, {$current['real_q_id']}, '$ans')");
    } else {
        $row_id = $check->fetch_assoc()['id'];
        $conn->query("UPDATE annotations SET answer='$ans' WHERE id=$row_id");
    }
    
    header("Location: answer_project.php?id=$project_id&q=".($q_index+1));
    exit;
}

$answered_count = (int)$conn->query("SELECT COUNT(*) AS c FROM annotations WHERE user_id=$user_id AND project_id=$project_id")->fetch_assoc()['c'];
$progress = $total_q > 0 ? round(($answered_count/$total_q)*100) : 0;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Answer Project</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<div class="card">
    <h3>Question <?= $q_index+1 ?> / <?= $total_q ?></h3>
    <div class="small">Progress: <?= $progress ?>%</div>
    <div class="progress-bar"><div class="progress-fill" style="width:<?= $progress ?>%;"></div></div>
    <hr>
    
    <form method="post">
        <h3 style="color:#1e3a8a; margin-bottom:15px;"><?= e($parsed['question']) ?></h3>
        
        <?php if(!empty($parsed['options'])): ?>
            <?php foreach($parsed['options'] as $opt): ?>
                <label style="display:block; margin-bottom:10px; padding:10px; background:#f9fafb; border-radius:8px; cursor:pointer;">
                    <input type="radio" name="answer" value="<?= e($opt) ?>" required> 
                    <span style="margin-left:8px;"><?= e($opt) ?></span>
                </label>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No options provided (or type answer directly):</p>
            <input type="text" name="answer" required style="width:100%; padding:10px;">
        <?php endif; ?>

        <div style="margin-top:20px;">
            <button class="button" type="submit">Submit & Next</button>
            <a class="button" href="user_page.php" style="background:#64748b; margin-top:10px;">Exit</a>
        </div>
    </form>
</div>
</div>
</body>
</html>
