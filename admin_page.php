<?php
require "config2.php";

// demo admin
$admin = ['id'=>1,'name'=>'Admin Demo'];

// fetch all projects
$projects = $conn->query("SELECT * FROM cards ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>لوحة المسؤول | لهجتنا</title>
  <link rel="icon" type="image/png" href="Favicon.png">
  <link rel="stylesheet" href="style.css">
</head>

<body>
<header class="navbar">
  <a href="index.html" class="logo" style="text-decoration:none;">
    <img src="Favicon.png" alt="logo">
    <div class="logo-text">
      <h1 class="site-title">لهجتنا</h1>
      <p class="site-tagline">اختبر معرفتك بثقافة وتراث مناطق المملكة</p>
    </div>
  </a>

  <nav>
    <a href="logout.php">تسجيل الخروج</a>
  </nav>
</header>

<main class="types-wrapper">
  <div class="admin-wrapper">
    <h2>جميع الاختبارات</h2>

    <div class="admin-cards-grid">
      <?php while($p = $projects->fetch_assoc()): ?>
        <div class="project-card">
          <strong><?= e($p['card_name']) ?></strong><br>
          عدد الأسئلة: <?= (int)$p['number_of_question'] ?> — عدد المستخدمين: <?= (int)$p['number_of_users'] ?>
          <br>
          <a class="view-btn" href="admin_project_answers.php?id=<?= (int)$p['id'] ?>">مشاهدة الأجوبة</a>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- زر إنشاء اختبار تحت القائمة -->
    <a href="create_project.php" class="create-btn"> إنشاء اختبار جديد</a>
  </div>
</main>

<footer>
  <p>© 2025 لهجتنا</p>
</footer>

</body>
</html>
