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
  </div> <!--from here to 97 is changable-->
      <h1 align="center">Projects Dashboard</h1>
    <div class="overall">

    </div>
    <div class="Dashboard">
        <form>
            <div class="search">
                <span class="material-symbols-outlined">search</span>
                <input class="search_bar" type="search" name="search_bar" id="search_bar">
            </div>
        </form>
        <table class="logs">
            <tr class="tableHead">
            <th>الإجابة</th>
            <th>السؤال</th>
            <th>المشروع</th>
            <th>المستخدم</th>
            </tr>
            <tbody id="info">

            </tbody>
        </table>
    </div>
    <script>
    var myArray = [
	    {'name':'Michael', 'age':'30', 'birthdate':'11/10/1989'},
	    {'name':'Mila', 'age':'32', 'birthdate':'10/1/1989'},
	    {'name':'Paul', 'age':'29', 'birthdate':'10/14/1990'},
	    {'name':'Dennis', 'age':'25', 'birthdate':'11/29/1993'},
	    {'name':'Tim', 'age':'27', 'birthdate':'3/12/1991'},
	    {'name':'Erik', 'age':'24', 'birthdate':'10/31/1995'},
	]
    function BuildTable(data) {
        var table = document.getElementById('info')
        for(var i = 0; i < data.length; i++) {  
            var row = `<tr>
                        <td>${data[i].name}</td>
                        <td>${data[i].age}</td>
                        <td>${data[i].birthdate}</td>
                       </tr>`
            table.innerHTML += row
        }
    }
    BuildTable(myArray);
</script>
</main>

<footer>
  <p>© 2025 لهجتنا</p>
</footer>

</body>
</html>
