<?php
require_once __DIR__ . '/../../auth/auth.php';
require_admin();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>لهجتنا | لوحة تحكم المسؤول</title>
  <link rel="icon" type="image/png" href="../../assets/images/Favicon.png">
  <link rel="stylesheet" href="../../css/style.css" />
</head>

<body>
  <header class="navbar">
    <a href="../../../index.html" class="logo" style="text-decoration: none;">
      <img src="../../assets/images/Favicon.png" alt="شعار لهجتنا">
      <div class="logo-text">
        <h1 class="site-title">لهجتنا</h1>
        <p class="site-tagline">اختبر معرفتك بثقافة وتراث مناطق المملكة</p>
      </div>
    </a>
    <nav>
      <span>مرحباً أيها المسؤول</span>
      <a href="../../auth/logout.php">تسجيل الخروج</a>
    </nav>
  </header>

  <main class="user-dashboard">
    <section class="user-panel">
      <header class="user-panel-header">
        <h2>الاختبارات المتاحة</h2>
        <p>هنا تستطيع إدارة جميع الاختبارات ومراجعة إجابات المستخدمين.</p>

        <a href="create_project.html" class="quiz-button" style="margin-top:10px;">
          إنشاء اختبار جديد
        </a>
        <input type="text" id="search" placeholder="ابحث باسم الاختبار..." oninput="loadCards()"
          style="width: 100%; padding: 10px; margin: 10px 0;">

        
        <select id="filter-region" onchange="loadCards()" style="width: 100%; padding: 10px; margin-bottom: 15px;">
          <option value="" disabled selected>-- Select Dialect --</option>
            <option value="all">Mixed (Random from all)</option>
            <option value="General">General</option>
            <option value="Southern">Southern</option>
            <option value="Central">Central</option>
            <option value="Eastern">Eastern</option>
            <option value="Northern">Northern</option>
            <option value="Western">Western</option>
        </select>
        
        <div id="cards-box" class="cards-grid"></div>
      </header>
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


