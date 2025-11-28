<?php
require "config2.php";
session_start();

// demo admin
$admin = ['id'=>1,'name'=>'Admin Demo'];

// جلب كل الاختبارات
$projects = $conn->query("SELECT * FROM cards ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>لهجتنا | لوحة تحكم المسؤول</title>
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
      <span>مرحباً أيها المسؤول</span>
      <a href="logout.php">تسجيل الخروج</a>
    </nav>
  </header>

   <main class="user-dashboard">
    <section class="user-panel">
      <header class="user-panel-header">
        <h2>الاختبارات المتاحة</h2>
        <p>هنا تستطيع إدارة جميع الاختبارات ومراجعة إجابات المستخدمين.</p>



          <!-- زر إنشاء اختبار بنفس ستايل الأزرار -->
        <a href="create_project.php" class="quiz-button" style="margin-top:10px;">
          إنشاء اختبار جديد
        </a>
      </header>
      
        <input type="text" id="search" placeholder="ابحث باسم الاختبار..." oninput="loadCards()"
          style="width: 100%; padding: 10px; margin: 10px 0;">
        <?php if ($projects && $projects->num_rows > 0): ?>
        <!-- نفس grid المستخدم في صفحة user_page -->
        <div class="cards-grid">
          <?php while($p = $projects->fetch_assoc()): ?>
            <article class="quiz-card">
              <div class="quiz-card-header">
                <h3 class="quiz-title"><?= e($p['card_name']) ?></h3>

                <span class="quiz-users">
                  عدد المستخدمين المسموح: 
                  <strong><?= (int)$p['number_of_users'] ?></strong>
                </span>
              </div>

              <div class="quiz-meta">
                <span>مجموع الأسئلة: <strong><?= (int)$p['number_of_question'] ?></strong></span>
                <span>معرّف الاختبار: <strong>#<?= (int)$p['id'] ?></strong></span>
              </div>

              <div class="quiz-actions">
                <a class="quiz-button" href="admin_project_answers.php?id=<?= (int)$p['id'] ?>">
                  مشاهدة الأجوبة
                </a>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <p class="no-quizzes">لا توجد اختبارات حتى الآن.</p>
      <?php endif; ?>
    
       <div id="cards-box"></div>
      </div>
    </section>
  </main>

  <footer>
    <p>© 2025 لهجتنا</p>
  </footer>

  <script>
    function loadCards() {
      let search = document.getElementById("search").value;
      let region = document.getElementById("filter-region").value;

      fetch("cards_data_admin.php?search="
        + encodeURIComponent(search)
        + "&region="
        + encodeURIComponent(region))
        .then(res => res.text())
        .then(html => {
          document.getElementById("cards-box").innerHTML = html;
        });
    }

    loadCards();
    setInterval(loadCards, 2000);
  </script>

</body>

</html>
