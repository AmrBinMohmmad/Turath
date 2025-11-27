<?php
// user_page.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config2.php";
session_start();

// لو ما في جلسة، استخدم قيم افتراضية
$user_id   = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['name']    ?? 'ضيف';

// جلب البطاقات (المشاريع)
$projects = $conn->query("
    SELECT c.*, 
    (SELECT COUNT(DISTINCT a.user_id) 
     FROM annotations a 
     WHERE a.project_id = c.id) AS completed_users
    FROM cards c
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>لهجتنا | لوحة تحكم المستخدم</title>
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
      <span>مرحباً بك، <?= htmlspecialchars($user_name) ?></span>
      <a href="user_answers.php" class="button">إجاباتي</a>
      <a href="logout.php">تسجيل الخروج</a>
    </nav>
  </header>

  <main class="user-dashboard">
    <section class="user-panel">
      <header class="user-panel-header">
        <h2>الاختبارات المتاحة</h2>
        <p>اختر اختبارًا لبدء أو إكمال إجاباتك على لهجات ومحتوى مناطق المملكة.</p>
      </header>

      <?php if ($projects && $projects->num_rows > 0): ?>
        <div class="cards-grid">
          <?php while($p = $projects->fetch_assoc()): 
              // عدد الأسئلة
              $total_q = (int)$p['number_of_question'];

              // عدد الأسئلة التي أجاب عنها هذا المستخدم في هذا المشروع
              $answered_query = $conn->query("
                  SELECT COUNT(*) AS c 
                  FROM annotations 
                  WHERE user_id = $user_id 
                    AND project_id = {$p['id']}
              ");
              $answered = 0;
              if ($answered_query) {
                  $answered = (int)$answered_query->fetch_assoc()['c'];
              }

              // نسبة التقدم
              $progress = ($total_q > 0) ? round(($answered / $total_q) * 100) : 0;

              // نص زر
              $btn_label = ($progress > 0 && $progress < 100) ? 'متابعة' : 'ابدأ الآن';
          ?>
            <article class="quiz-card">
              <div class="quiz-card-header">
                <h3 class="quiz-title"><?= htmlspecialchars($p['card_name']) ?></h3>
                <span class="quiz-users">
                  عدد المستخدمين: 
                  <strong><?= (int)$p['completed_users'] ?></strong> / <?= (int)$p['number_of_users'] ?>
                </span>
              </div>

              <div class="quiz-meta">
                <span>مجموع الأسئلة: <strong><?= $total_q ?></strong></span>
                <span>إجاباتك: <strong><?= $answered ?></strong> / <?= $total_q ?></span>
              </div>

              <div class="quiz-progress">
                <div class="quiz-progress-top">
                  <span>مستوى تقدمك</span>
                  <span class="quiz-progress-value"><?= $progress ?>%</span>
                </div>
                <div class="progress-bar">
                  <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                </div>
              </div>

              <div class="quiz-actions">
                <a class="button quiz-button" href="answer_project.php?id=<?= (int)$p['id'] ?>">
                  <?= $btn_label ?>
                </a>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <p class="no-quizzes">لا توجد اختبارات متاحة حاليًا.</p>
      <?php endif; ?>
    </section>
  </main>

  <footer>
    <p>© 2025 لهجتنا</p>
  </footer>

</body>
</html>
