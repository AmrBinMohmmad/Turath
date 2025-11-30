<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once __DIR__ . '/../../auth/auth.php';
require_admin();

$host = "sql204.infinityfree.com";
$user = "if0_40419506";
$password = "Abmw123456789";

// Database names
$projects_db_name = "if0_40419506_projects";
$users_db_name = "if0_40419506_users_db";
$questions_db_name = "if0_40419506_questions_db";

// Establish connections
$conn_projects = new mysqli($host, $user, $password, $projects_db_name);
$conn_users = new mysqli($host, $user, $password, $users_db_name);
$conn_questions = new mysqli($host, $user, $password, $questions_db_name);

$conn_projects->set_charset("utf8mb4");
$conn_users->set_charset("utf8mb4");
$conn_questions->set_charset("utf8mb4");

if ($conn_projects->connect_error || $conn_users->connect_error || $conn_questions->connect_error) {
    die("DB Connection Error");
}

$project_id = intval($_GET['id'] ?? 0);
$user_id = intval($_GET['user_id'] ?? 0);

// 1. Fetch Card and User Info
$card_name_query = $conn_projects->query("SELECT card_name FROM cards WHERE id = $project_id");
$card_name = $card_name_query->fetch_assoc()['card_name'] ?? 'Unknown Test';

$user_name_query = $conn_users->query("SELECT name FROM users WHERE id = $user_id");
$user_name = $user_name_query->fetch_assoc()['name'] ?? 'Unknown User';

// 2. Fetch all questions for the card (same logic as answer_card.php)
$table_map = [1 => "words_db", 2 => "phrases_db", 3 => "proverbs_db"];
$question_columns = ["Location_Recognition_question", "Cultural_Interpretation_question", "Contextual_Usage_question", "Fill_in_Blank_question", "True_False_question", "Meaning_question"];

$q_ids_result = $conn_projects->query("SELECT number_of_q, type_of_q FROM cards_questions WHERE card_id = $project_id AND number_of_q IS NOT NULL");
$all_questions_data = [];
$question_index = 0;

while ($row = $q_ids_result->fetch_assoc()) {
    $qid = intval($row['number_of_q']);
    $type = intval($row['type_of_q']);
    $table = $table_map[$type] ?? null;

    if ($table) {
        $col_index = $question_index % count($question_columns);
        $selected_column = $question_columns[$col_index];
        
        $q = $conn_questions->query("SELECT $selected_column FROM $table WHERE id = $qid LIMIT 1");
        if ($q && $q->num_rows > 0) {
            $data = $q->fetch_assoc();
            if (!empty($data[$selected_column])) {
                $all_questions_data[] = parse_question_text($data[$selected_column]);
            }
        }
    }
    $question_index++;
}

// 3. Fetch user's answers, score, and timestamp
$annotations_query = $conn_projects->query("SELECT question_id, answer, score, created_at FROM annotations WHERE project_id = $project_id AND user_id = $user_id");
$user_answers = [];
$total_score = 0;
while ($row = $annotations_query->fetch_assoc()) {
    $user_answers[] = [
        'answer' => $row['answer'],
        'score' => $row['score'],
        'timestamp' => $row['created_at']
    ];
    $total_score += intval($row['score']);
}

function parse_question_text($text) {
    // Same parse_question_text function from answer_card.php
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
            $answer_part = trim(str_replace("الإجابة الصحيحة:", "", $line));
            if (preg_match('/^([A-Za-zأ-ي])/', $answer_part, $matches)) {
                $answer = $matches[1];
            } else {
                $parts = explode(' ', $answer_part, 2);
                $answer = trim($parts[0]);
                $answer = rtrim($answer, '):');
            }
        } else {
            if ($current === "options" && !empty($line)) {
                $parts = explode(")", $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim(trim($parts[1]));
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

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>تفاصيل إجابات: <?= htmlspecialchars($user_name) ?></title>
  <link rel="icon" type="image/png" href="../../assets/images/Favicon.png">
  <link rel="stylesheet" href="../../css/style.css" />
  <style>
    .ans-card {
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      margin-bottom: 10px;
      background: #fff;
    }
    .wrong-ans {
      color: red;
      text-decoration: line-through;
    }
    .score-badge {
      float: left; /* Changed to left for RTL */
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 0.9em;
      color: white;
    }
    .bg-green {
      background-color: #10b981;
    }
    .bg-red {
      background-color: #ef4444;
    }
    .score-summary {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
    }
    .score-summary span {
        color: #10b981;
    }
  </style>
</head>
<body>
  <header class="navbar">
    <a href="admin_page.php" class="logo" style="text-decoration: none;">
      <img src="../../assets/images/Favicon.png" alt="شعار لهجتنا">
    </a>
    <nav>
      <a href="admin_project_answers.php?id=<?= $project_id ?>">العودة لإجابات الاختبار</a>
    </nav>
  </header>

  <main class="user-dashboard">
    <section class="user-panel">
      <header class="user-panel-header">
        <h2>تفاصيل إجابات "<?= htmlspecialchars($user_name) ?>"</h2>
        <p>في اختبار: "<?= htmlspecialchars($card_name) ?>"</p>
      </header>

      <div class="score-summary">
        النتيجة النهائية: <span><?= $total_score ?> / <?= count($all_questions_data) ?></span>
      </div>
      
      <div id="answers-box">
        <?php foreach ($all_questions_data as $index => $question_data): ?>
            <?php 
                $user_answer_data = $user_answers[$index] ?? ['answer' => '-', 'score' => 0, 'timestamp' => null];
                $is_correct = ($user_answer_data['score'] == 1);
                $score_text = $is_correct ? 'Correct (+1)' : 'Incorrect (0)';
                $badge_color = $is_correct ? 'bg-green' : 'bg-red';
            ?>
            <div class="ans-card">
                <span class="score-badge <?= $badge_color ?>">
                    <?= $score_text ?>
                </span>
                <div style="margin-bottom: 8px;">
                    <strong>السؤال:</strong>
                    <?= htmlspecialchars($question_data['question']) ?>
                </div>
    
                <div>
                    <strong>اجابتك:</strong>
                    <span class="<?= !$is_correct ? 'wrong-ans' : '' ?>">
                        <?= htmlspecialchars($user_answer_data['answer']) ?>
                    </span>
                </div>
    
                <?php if (!$is_correct): ?>
                <div style="margin-top:5px; color: #059669; font-size: 0.9em;">
                    الإجابة الصحيحة هي:
                    <?= htmlspecialchars($question_data['answer']) ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <footer>
    <p>© 2025 لهجتنا</p>
  </footer>
</body>

</html>



