<?php
// user_page.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config2.php";
session_start();

// المستخدم (من السيشن، أو افتراضي للتجربة)
$user_id   = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['name']    ?? 'ضيف';

// جلب البطاقات (المشاريع)
$projects = $conn->query("
    SELECT c.*, 
    (SELECT COUNT(DISTINCT a.user_id) FROM annotations a WHERE a.project_id = c.id) AS completed_users
    FROM cards c
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>لوحة المستخدم | لهجتنا</title>
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
      <a href="index.html">الرئيسية</a>
      <a href="signup.php">تسجيل / دخول</a>
      <a href="about.html">عن الموقع</a>
      <a href="contact.html">تواصل معنا</a>
      <span style="margin-right:1rem;">مرحباً، <?= htmlspecialchars($user_name) ?></span>
    </nav>
  </header>

  <main class="hero">
    <div class="hero-content">
      <h2>لوحة المستخدم</h2>
      <p>
        هنا تقدر تشوف البطاقات المتاحة، وتبدأ أو تكمّل إجاباتك على أسئلة لهجات 
        وتراث مناطق المملكة.
      </p>
    </div>
  </main>

  <section class="features">
    <h3>البطاقات المتاحة</h3>

    <?php if ($projects && $projects->num_rows > 0): ?>
      <?php while ($p = $projects->fetch_assoc()): ?>

        <?php
          // عدد الأسئلة في البطاقة
          $total_q = (int)$p['number_of_question'];

          // كم سؤال جاوب هذا المستخدم في هذه البطاقة
          $answered = 0;
          $answered_query = $conn->query("
            SELECT COUNT(*) AS c 
            FROM annotations 
            WHERE user_id = {$user_id} 
              AND project_id = {$p['id']}
          ");
          if ($answered_query) {
              $answered = (int)$answered_query->fetch_assoc()['c'];
          }

          // نسبة التقدّم
          $progress = ($total_q > 0) ? round(($answered / $total_q) * 100) : 0;
        ?>

        <div class="feature" style="border:1px solid #eee; border-radius:10px; margin-bottom:1rem;">
          <h4 style="margin-top:0;"><?= htmlspecialchars($p['card_name']) ?></h4>
          <p style="margin:0 0 0.5rem 0;">
            عدد الأسئلة في هذه البطاقة: <?= $total_q ?><br>
            عدد المشاركين: <?= (int)$p['completed_users'] ?> / <?= (int)$p['number_of_users'] ?>
          </p>

          <p class="small" style="margin:0 0 0.5rem 0;">
            تقدّمك: <?= $progress ?>% (<?= $answered ?> / <?= $total_q ?> سؤال)
          </p>

          <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
          </div>

          <div style="margin-top:0.8rem;">
            <a class="btn" href="answer_project.php?id=<?= (int)$p['id'] ?>">
              <?= ($progress > 0 && $progress < 100) ? 'متابعة' : 'ابدأ الآن' ?>
            </a>
          </div>
        </div>

      <?php endwhile; ?>
    <?php else: ?>
      <p>لا توجد بطاقات متاحة حالياً.</p>
    <?php endif; ?>

  </section>
  
  <footer>
    <p>© 2025 لهجتنا</p>
  </footer>

</body>
</html>
