<?php
// user_page.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config2.php";
session_start();

// المستخدم الحالي
$user_id   = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['name']    ?? 'مستخدم';

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

  <!-- نفس هيدر الموقع -->
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
      <span class="nav-username">مرحباً، <?= htmlspecialchars($user_name) ?></span>
    </nav>
  </header>

  <main class="user-main">

    <!-- الشريط العلوي الأخضر مثل الصورة -->
    <div class="user-topbar">
      <div class="user-topbar-left">
        <span class="user-icon">⇦</span>
        <span>إجاباتك: <strong>عرض الإحصائيات</strong></span>
      </div>
      <div class="user-topbar-right">
        <span>منصة <strong>لهجتنا</strong></span>
      </div>
    </div>

    <!-- العنوان والوصف -->
    <section class="user-intro">
      <h2>اختر فئة للبدء</h2>
      <p>
        البيانات مقسّمة حسب نوع البطاقة. اختر بطاقة للدخول إلى أسئلة اللهجات 
        والثقافة الخاصة بكل مجموعة.
      </p>
    </section>

    <!-- كروت البطاقات بنفس فكرة التصميم الأخضر -->
    <section class="cards-grid">

      <?php if ($projects && $projects->num_rows > 0): ?>
        <?php while ($p = $projects->fetch_assoc()): ?>

          <?php
            $total_q = (int)$p['number_of_question'];

            // عدد الأسئلة التي أجاب عليها هذا المستخدم في هذه البطاقة
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

            $progress = ($total_q > 0) ? round(($answered / $total_q) * 100) : 0;
          ?>

          <article class="category-card">
            <div class="category-card-header">
              <div class="card-icon-circle">
                <span class="card-icon">🗂</span>
              </div>
              <div class="card-title-block">
                <h3><?= htmlspecialchars($p['card_name']) ?></h3>
                <p class="card-subtitle">
                  تتضمّن أسئلة ثقافية ولهجية من هذه الفئة.
                </p>
              </div>
            </div>

            <div class="card-meta">
              <span>عدد الأسئلة في البطاقة: <?= $total_q ?></span>
              <span>عدد المشاركين: <?= (int)$p['completed_users'] ?> / <?= (int)$p['number_of_users'] ?></span>
            </div>

            <div class="card-footer">
              <div class="card-count-pill">
                <?= $answered ?> / <?= $total_q ?>
              </div>
              <div class="card-footer-label">
                المهام المكتملة
              </div>
              <a class="card-main-button" href="answer_project.php?id=<?= (int)$p['id'] ?>">
                <?= ($progress > 0 && $progress < 100) ? 'متابعة' : 'ابدأ الآن' ?>
              </a>
            </div>
          </article>

        <?php endwhile; ?>
      <?php else: ?>
        <p>لا توجد بطاقات متاحة حالياً.</p>
      <?php endif; ?>

    </section>

  </main>
  
  <footer>
    <p>© 2025 لهجتنا</p>
  </footer>

</body>
</html>
