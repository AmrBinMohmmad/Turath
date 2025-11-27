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
  <title>لهجتنا | اختبر معرفتك بثقافة وتراث مناطق المملكة</title>
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
      <!---<span><?= e($admin['name']) ?></span>--->
      <a href="create_project.php" class="button">إنشاء إختبار</a>
      <a href="logout.php">تسجيل الخروج</a>
      <!---<a href="user_page.php">View User View</a>
      <a href="index.html">Home</a>
      <a href="about.html">About</a>
      <a href="contact.html">Contact</a>--->
    </nav>
  </header>
<main class="types-wrapper">
<div class="card">
  <h2>جميع الاختبارات</h2><br>
  <?php while($p = $projects->fetch_assoc()): ?>
    <div class="card">
        <strong><?= e($p['card_name']) ?></strong><br>
        عدد الاسئلة: <?= (int)$p['number_of_question'] ?> � المستخدمون: <?= (int)$p['number_of_users'] ?><br><br>
        <a class="button" href="admin_project_answers.php?id=<?= (int)$p['id'] ?>">مشاهدة الأجوبة</a>
    </div>
<?php endwhile; ?>
</div>
</main>
  <footer>
    <p>© 2025 لهجتنا</p>
  </footer>
</body>
</html>
