<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "questions_db";

$project_id = intval($_GET['id'] ?? 0);

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function parse_question_text($text)
{
    $lines = explode("\n", $text);
    $mission = $question = $answer = "";
    $options = [];
    $current = "";

    foreach ($lines as $line) {
        $line = trim($line);

        if (str_starts_with($line, "المهمة:")) {
            $current = "mission";
            $mission = trim(str_replace("المهمة:", "", $line));

        } elseif (str_starts_with($line, "السؤال:")) {
            $current = "question";
            $question = trim(str_replace("السؤال:", "", $line));

        } elseif (str_starts_with($line, "الخيارات:")) {
            $current = "options";

        } elseif (str_starts_with($line, "الإجابة الصحيحة:")) {
            $current = "answer";
            $answer = trim(str_replace("الإجابة الصحيحة:", "", $line));

        } else {
            if ($current === "options" && !empty($line)) {
                $parts = explode(")", $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $options[$key] = $value;
                }
            }
        }
    }

    return [
        "mission"  => $mission,
        "question" => $question,
        "options"  => $options,
        "answer"   => $answer
    ];
}

$tables = ["phrases_db", "words_db", "proverbs_db"];
$question_columns = [
    "Location_Recognition_question",
    "Cultural_Interpretation_question",
    "Contextual_Usage_question",
    "Fill_in_Blank_question",
    "True_False_question",
    "Meaning_question"
];

// IDs الخاصة بالأسئلة في هذا الكارد
$q_ids_result = $conn->query("
    SELECT number_of_q 
    FROM projects.cards_questions
    WHERE card_id = $project_id AND number_of_q IS NOT NULL
");

$all_questions = [];
$q_ids = [];

while ($row = $q_ids_result->fetch_assoc()) {
    $q_ids[] = intval($row['number_of_q']);
}

if (count($q_ids) == 0) {
    die("لا يوجد أسئلة مرتبطة بهذه البطاقة.");
}

// البحث عن كل سؤال في الجداول الثلاثة
foreach ($q_ids as $qid) {
    foreach ($tables as $table) {
        $q = $conn->query("SELECT * FROM $table WHERE id = $qid LIMIT 1");

        if ($q && $q->num_rows > 0) {
            $data = $q->fetch_assoc();

            foreach ($question_columns as $col) {
                if (!empty($data[$col])) {
                    $all_questions[] = parse_question_text($data[$col]);
                }
            }
        }
    }
}

$total_q = count($all_questions);
if ($total_q == 0) {
    die("لم يتم العثور على أي سؤال في الجداول الثلاثة.");
}

// إدارة الفهرس
if (!isset($_SESSION['q_index'])) {
    $_SESSION['q_index'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['q_index']++;
    if ($_SESSION['q_index'] >= $total_q) {
        $_SESSION['q_index'] = 0;
    }

    header("Location: ".$_SERVER['PHP_SELF']."?id=".$project_id);
    exit;
}

$q_index = $_SESSION['q_index'];
$parsed = $all_questions[$q_index];
$progress = round(($q_index + 1) / $total_q * 100);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Answer Project</title>
<style>
body { font-family: Tahoma, sans-serif; background:#f5f5f5; direction:rtl; }
.container { max-width:700px; margin:40px auto; }
.card { background:white; padding:25px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
.small { font-size:14px; color:#64748b; margin-bottom:5px; }
.progress-bar { width:100%; height:10px; background:#e5e7eb; border-radius:5px; margin-bottom:15px; }
.progress-fill { height:100%; background:#2563eb; border-radius:5px; width:0%; transition:width 0.3s; }
.button { background:#2563eb; color:white; padding:10px 20px; border:none; border-radius:8px; cursor:pointer; text-decoration:none; }
.button:hover { background:#1d4ed8; }
</style>
</head>
<body>
<div class="container">
<div class="card">
    <h3>السؤال <?= $q_index+1 ?> / <?= $total_q ?></h3>
    <div class="small">التقدم: <?= $progress ?>%</div>
    <div class="progress-bar"><div class="progress-fill" style="width:<?= $progress ?>%;"></div></div>
    <hr>

    <form method="post">
        <?php if(!empty($parsed['mission'])): ?>
            <p style="font-size:18px; font-weight:bold; color:#0f172a; margin-bottom:15px;">
                <?= e($parsed['mission']) ?>
            </p>
        <?php endif; ?>

        <h3 style="color:#1e3a8a; margin-bottom:15px;"><?= e($parsed['question']) ?></h3>

        <?php foreach($parsed['options'] as $key => $opt): ?>
            <label style="
                display:block; 
                margin-bottom:10px; 
                padding:10px; 
                background:#f9fafb; 
                border-radius:8px; 
                cursor:pointer;
                border:1px solid #e5e7eb;
            ">
                <input type="radio" name="answer" value="<?= e($key) ?>" required> 
                <span style="margin-right:8px;"><?= e($key) ?>) <?= e($opt) ?></span>
            </label>
        <?php endforeach; ?>

        <div style="margin-top:20px;">
            <button class="button" type="submit">التالي</button>
            <a class="button" href="user_page.php" style="background:#64748b; margin-top:10px;">خروج</a>
        </div>
    </form>
</div>
</div>
</body>
</html>
