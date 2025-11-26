<?php
// user_page.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config2.php";
session_start();

// لو ما فيه جلسة، نعطي قيم افتراضية (تجريبية)
$user_id   = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
$user_name = $_SESSION['name'] ?? 'ضيف';

// جلب البطاقات / المشاريع
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
  <meta charset="utf-8">
  <title>لوحة المستخدم | لهجتنا</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" type="image/png" href="assets/Favicon.png">
</head>
<body>

<header class="navbar">
  <a href="index.html" class="logo" style="text-decoration:none;">
    <img src="assets/Favicon.png" alt="شعار لهجتنا">
    <div class="logo-text">
      <h1 class="site-title">لهجتنا</h1>
      <p class="site-tagline">اختبر معرفتك بثقافة وتراث مناطق المملكة</p>
    </div>
  </a>
  <nav>
    <a href="index.html">الرئيسية</a>
    <a href="auth/signup.php">تسجيل / دخول</a>
    <a href="pages/about.html">عن الموقع</a>
    <a href="pages/contact.html">تواصل معنا</a>
    <span class="nav-user">مرحباً، <?= htmlspecialchars($user_name) ?></span>
    <a href="logout.php" class="btn-link">تسجيل خروج</a>
  </nav>
</header>

<main class="container" style="margin-top: 2rem; margin-bottom:2rem;">
  <div class="card">
    <h2>لوحة المستخدم</h2>
    <p style="margin-bottom:1.5rem;">
      اختر البطاقة التي تود الإجابة على أسئلتها، وتابع تقدّمك في كل بطاقة.
    </p>

    <h3>البطاقات المتاحة</h3>

    <?php if ($projects && $projects->num_rows > 0): ?>
        <?php while ($p = $projects->fetch_assoc()): ?>

          <?php
            $total_q = (int)$p['number_of_question'];

            // عدد الأسئلة التي أجاب عليها هذا المستخدم في هذا المشروع
            $answered_query = $conn->query(
              "SELECT COUNT(*) AS c 
               FROM annotations 
               WHERE user_id = {$user_id} 
               AND project_id = {$p['id']}"
            );

            $answered = 0;
            if ($answered_query) {
                $answered = (int)$answered_query->fetch_assoc()['c'];
            }

            $progress = ($total_q > 0) ? round(($answered / $total_q) * 100) : 0;
          ?>

          <div class="card" style="margin-bottom:12px; border:1px solid #eee;">
            <div class="project-row" style="display:flex; justify-content:space-between; align-items:center; gap:1rem;">
              <div>
                <strong style="font-size:1.1em;"><?= htmlspecialchars($p['card_name']) ?></strong><br>
                <span class="small" style="color:#666;">
                    عدد الأسئلة: <?= $total_q ?> |
                    عدد المشاركين: <?= (int)$p['completed_users'] ?> / <?= (int)$p['number_of_users'] ?>
                </span>
              </div>
              <div>
                <a class="button" href="answer_project.php?id=<?= (int)$p['id'] ?>">
                  <?= ($progress > 0 && $progress < 100) ? 'متابعة' : 'ابدأ الآن' ?>
                </a>
              </div>
            </div>

            <div style="margin-top:12px;">
              <div class="small">
                تقدّمك في هذه البطاقة: <?= $progress ?>% (<?= $answered ?> / <?= $total_q ?>)
              </div>
              <div class="progress-bar">
                <div class="progress-fill" style="width:<?= $progress ?>%;"></div>
              </div>
            </div>
          </div>

        <?php endwhile; ?>
    <?php else: ?>
        <p>لا توجد بطاقات متاحة حالياً.</p>
    <?php endif; ?>

  </div>
</main>

<footer>
  <p>© 2025 لهجتنا</p>
</footer>

</body>
</html>
