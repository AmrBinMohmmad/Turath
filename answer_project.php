<?php
require "config2.php";
session_start();

$user_id    = $_SESSION['user_id'] ?? 1;
$project_id = intval($_GET['id'] ?? 0);
$q_index    = intval($_GET['q'] ?? 0);

// جلب أسئلة الكرت
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
        WHERE cq.card_id = $project_id 
        ORDER BY cq.id ASC";

$qs = $conn->query($sql);
if(!$qs){
    die("Query Failed: " . $conn->error);
}

$total_q = $qs->num_rows;
if($total_q == 0){
    die("<div class='container'><div class='question-card'><p>لا توجد أسئلة في هذا الاختبار.</p></div></div>");
}

// حدود رقم السؤال
if($q_index < 0)        $q_index = 0;
if($q_index >= $total_q){ header("Location: user_page.php"); exit; }

// نجيب السؤال الحالي
$qs->data_seek($q_index);
$current = $qs->fetch_assoc();

if (empty($current['question_data'])) {
    $parsed = ['question' => 'حدث خطأ في جلب نص السؤال.', 'options' => [], 'correct' => ''];
} else {
    $parsed = parseQuestionRow($current['question_data']);
}

// معالجة الإرسال
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $ans = $conn->real_escape_string($_POST['answer'] ?? '');

    $check = $conn->query("
        SELECT id 
        FROM annotations 
        WHERE user_id = $user_id 
          AND project_id = $project_id 
          AND question_id = {$current['real_q_id']}
    ");

    if($check->num_rows == 0){
        $conn->query("
            INSERT INTO annotations (project_id, user_id, question_id, answer) 
            VALUES ($project_id, $user_id, {$current['real_q_id']}, '$ans')
        ");
    } else {
        $row_id = (int)$check->fetch_assoc()['id'];
        $conn->query("
            UPDATE annotations 
            SET answer = '$ans' 
            WHERE id = $row_id
        ");
    }

    header("Location: answer_project.php?id=$project_id&q=" . ($q_index + 1));
    exit;
}

// التقدّم
$answered_count = (int)$conn->query("
    SELECT COUNT(*) AS c 
    FROM annotations 
    WHERE user_id = $user_id 
      AND project_id = $project_id
")->fetch_assoc()['c'];

$progress = $total_q > 0 ? round(($answered_count / $total_q) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>لهجتنا | حل الاختبار</title>
  <link rel="icon" type="image/png" href="Favicon.png">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<main class="quiz-page">
  <section class="question-card">
    <header class="question-header">
      <div class="question-top-row">
        <div class="question-number">
          السؤال <span><?= $q_index + 1 ?></span> من <span><?= $total_q ?></span>
        </div>
        <div class="question-progress-text">
          مستوى التقدم: <span><?= $progress ?>%</span>
        </div>
      </div>

      <div class="question-progress-bar">
        <div class="progress-bar">
          <div class="progress-fill" style="width:<?= $progress ?>%;"></div>
        </div>
      </div>
    </header>

    <form method="post" class="question-form">
      <h3 class="question-text-main"><?= e($parsed['question']) ?></h3>

      <div class="quiz-options">
        <?php if(!empty($parsed['options'])): ?>
          <?php foreach($parsed['options'] as $opt): ?>
            <label class="quiz-option">
              <input type="radio" name="answer" value="<?= e($opt) ?>" required>
              <span class="option-text"><?= e($opt) ?></span>
            </label>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="no-options-hint">لا توجد اختيارات لهذا السؤال، أدخل إجابتك يدوياً:</p>
          <input type="text" name="answer" required class="manual-answer-input">
        <?php endif; ?>
      </div>

      <div class="quiz-actions-row">
        <button class="quiz-button" type="submit">
          <?= ($q_index + 1 < $total_q) ? 'إرسال والانتقال للسؤال التالي' : 'إرسال وإنهاء الاختبار' ?>
        </button>

        <a class="quiz-exit-btn" href="user_page.php">إنهاء الاختبار والعودة</a>
      </div>
    </form>
  </section>
</main>

<footer>
  <p>© 2025 لهجتنا</p>
</footer>

</body>
</html>
