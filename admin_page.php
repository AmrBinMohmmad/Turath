<?php
require_once 'auth.php';
require_admin();
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
      <a href="create_project.html" class="button">إنشاء إختبار</a>
      <a href="logout.php">تسجيل الخروج</a>
    </nav>
  </header>

  <main class="types-wrapper">
    <div class="container">
      <div class="card">

        <h2>جميع الاختبارات</h2>

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

        <div id="cards-box"></div>
      </div>
    </div>
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
