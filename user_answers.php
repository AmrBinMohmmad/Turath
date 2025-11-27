<?php
// user_answers.php
header('Content-Type: text/html; charset=utf-8'); // دعم العربية
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config2.php";
session_start();

$user_id = $_SESSION['user_id'] ?? 1;

// جلب المشاريع التي أجاب عليها المستخدم
$projects = $conn->query("SELECT DISTINCT p.id, p.card_name 
                          FROM annotations a 
                          JOIN cards p ON p.id=a.project_id 
                          WHERE a.user_id=$user_id 
                          ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>لهجتنا | إجاباتي </title>
<link rel="icon" type="image/png" href="Favicon.png">
<link rel="stylesheet" href="style.css" />
</head>
  
<body>
  <header class="navbar">
    <a href="index.html" class="logo" style="text-decoration: none;">
      <img src="Favicon.png" alt="شعار لهجتنا">
      <div class="logo-text">
        <h1 class="site-title">لهجتنا</h1>
        <p class="site-tagline">اختبر معرفتك بثقافة وتراث مناطق المملكة</p>
      </div>
    </a>
    <nav>
      <a href="user_page.php" class="button">العودة إلى لوحة التحكم</a>
    </nav>
  </header>

  <main class="types-wrapper">
    <div class="answers-page">
      <div class="answers-header">
        <h2>الإجابات التي قدمتها</h2>
        <p>هنا يمكنك مراجعة إجاباتك لكل اختبار قمت بحله، وحالة تصحيح كل إجابة.</p>
      </div>

      <?php if($projects->num_rows == 0): ?>
        <p class="no-answers">لم تقم بالإجابة على أي اختبار بعد.</p>
      <?php endif; ?>

      <?php while($p = $projects->fetch_assoc()): ?>
        <section class="project-block">
          <h3 class="project-title">
            <span>اختبار:</span>
            <?= e($p['card_name']) ?>
          </h3>

          <?php
          $sql = "SELECT a.answer, a.score,
                  COALESCE(
                      NULLIF(w.Meaning_question, ''),
                      NULLIF(w.Location_Recognition_question, ''),
                      NULLIF(w.Cultural_Interpretation_question, ''),
                      NULLIF(w.Contextual_Usage_question, ''),
                      NULLIF(w.Fill_in_Blank_question, ''),
                      NULLIF(w.True_False_question, '')
                  ) as question_data 
                  FROM annotations a 
                  JOIN if0_40419506_questions_db.words_db w ON w.id=a.question_id 
                  WHERE a.project_id={$p['id']} AND a.user_id=$user_id 
                  ORDER BY a.id ASC";

          $ans = $conn->query($sql);

          while($r = $ans->fetch_assoc()):
              $parsed      = parseQuestionRow($r['question_data']);
              $student_ans = trim($r['answer']);
              $correct_ans = trim($parsed['correct']);

              $is_correct = false;
              if ($r['score'] == 1) {
                  $is_correct = true;
              } elseif ($r['score'] === null) {
                  if (!empty($correct_ans) && mb_strpos($student_ans, $correct_ans) === 0) {
                      $is_correct = true;
                  }
              }

              $score_text  = ($r['score'] !== null)
                              ? ($r['score'] == 1 ? 'إجابة صحيحة (+1)' : 'إجابة خاطئة (0)')
                              : 'بانتظار المراجعة';
              $badge_color = ($r['score'] !== null)
                              ? ($r['score'] == 1 ? 'bg-green' : 'bg-red')
                              : 'bg-gray';
          ?>
            <div class="ans-card">
              <div class="ans-top-row">
                <div class="question-text">
                  <strong>السؤال:</strong>
                  <?= e($parsed['question']) ?>
                </div>
                <span class="score-badge <?= $badge_color ?>"><?= $score_text ?></span>
              </div>

              <div class="answer-text">
                <strong>إجابتك:</strong>
                <span class="<?= ($r['score']!==null && $r['score']==0) ? 'wrong-ans' : '' ?>">
                  <?= e($student_ans) ?>
                </span>
              </div>

              <?php if($r['score'] !== null && $r['score'] == 0 && !empty($correct_ans)): ?>
                <div class="correct-ans">
                  الإجابة الصحيحة: <?= e($correct_ans) ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        </section>
      <?php endwhile; ?>

    </div>
  </main>

  <footer>
    <p>© 2025 لهجتنا</p>
  </footer>
</body>
</html>
