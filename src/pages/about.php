<?php 
ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

session_start(); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>عن المنصة | تراث المملكة</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link
      href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
      rel="stylesheet">
    <link rel="stylesheet" href="../css/about_page.css">
    <link rel="icon" type="image/png" href="../assets/images/Favicon.png" />
    
</head>

<body>

    <nav class="navbar">
        <a href="../../index.php" class="logo">
            <img src="../assets/images/Favicon.png" alt="Logo" style="height:40px; margin-right:8px; ">
            <span id="nav-logo">منصة تراث</span>
        </a>
    <div class="nav-actions">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="user/user_page.php" class="btn-gold" id="nav-dash">لوحة التحكم</a>
        <a href="../auth/logout.php" class="icon-btn" style="color:#ef4444; border-color:rgba(239,68,68,0.3);">
          <i class='bx bx-log-out'></i>
        </a>
      <?php else: ?>
        <a href="login.php" class="btn-gold" id="nav-login">تسجيل الدخول</a>
      <?php endif; ?>
      <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
      <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
      </button>
    </div>
  </nav>

  <section class="about-header">
    <h1 id="ab-title">قصة <span>شغف</span> وتراث</h1>
    <p id="ab-desc">في عصر التكنولوجيا المتسارعة، لاحظنا فجوة تتسع بين الأجيال الجديدة ولغتهم الأم، وتحديداً اللهجات
      المحلية الغنية. من هنا ولدت فكرة "منصة تراث"، لتكون الجسر الرقمي الذي يربط الماضي بالحاضر.</p>
  </section>

  <section class="section">
    <div class="grid-2">
      <div class="text-box">
        <h2 id="meth-title">منهجية <span>عملنا</span></h2>
        <p id="meth-p1">نحن لا نجمع الكلمات عشوائياً. تمر كل معلومة في منصتنا بمراحل تدقيق صارمة:</p>
        <ul style="list-style:none; color:var(--text-muted);">
          <li style="margin-bottom:10px;"><i class='bx bxs-check-circle' style="color:var(--saudi-green)"></i> <span
              id="m1">جمع البيانات من المصادر الموثوقة</span></li>
          <li style="margin-bottom:10px;"><i class='bx bxs-check-circle' style="color:var(--saudi-green)"></i> <span
              id="m2">مراجعة لغوية للتأكد من أصل الكلمة ومعناها الدقيق.</span></li>
          <li style="margin-bottom:10px;"><i class='bx bxs-check-circle' style="color:var(--saudi-green)"></i> <span
              id="m3">تصنيفها جغرافياً لضمان نسبتها للمنطقة الصحيحة.</span></li>
        </ul>
      </div>
      <div class="text-box">
        <h2 id="val-title">قيمنا <span>الراسخة</span></h2>
        <div class="values-grid">
          <div class="value-card">
            <i class='bx bxs-badge-check'></i>
            <h3 id="v1-t">الدقة</h3>
            <p id="v1-d" style="font-size:13px; margin:0;">معلومات موثوقة 100%.</p>
          </div>
          <div class="value-card">
            <i class='bx bxs-bulb'></i>
            <h3 id="v2-t">الابتكار</h3>
            <p id="v2-d" style="font-size:13px; margin:0;">توظيف التقنية لخدمة الثقافة.</p>
          </div>
          <div class="value-card">
            <i class='bx bxs-heart'></i>
            <h3 id="v3-t">الشمولية</h3>
            <p id="v3-d" style="font-size:13px; margin:0;">نغطي كل مناطق المملكة.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="background: var(--bg-secondary);">
    <div class="sec-header" style="text-align: center;">
      <h2 id="team-title">العقول خلف <span>المنصة</span></h2>
      <p id="team-desc">طلاب شغوفين يجمعون بين الخبرة التقنية والبحث الثقافي.</p>
    </div>
    <div class="team-grid">
      <div class="team-member">
        <div class="member-avatar">ع</div>
        <h3>عمرو تنكر</h3>
        <span style="color:var(--saudi-green); font-size:14px;" id="r1">Team leader/Frontend developer</span>
      </div>
      <div class="team-member">
        <div class="member-avatar">ع</div>
        <h3>عمار الحربي</h3>
        <span style="color:var(--saudi-green); font-size:14px;" id="r2">Frontend developer</span>
      </div>
      <div class="team-member">
        <div class="member-avatar">م</div>
        <h3>محمد العامري</h3>
        <span style="color:var(--saudi-green); font-size:14px;" id="r3">Frontend developer</span>
      </div>
      <div class="team-member">
        <div class="member-avatar">ع</div>
        <h3>عبدالرزاق الغامدي</h3>
        <span style="color:var(--saudi-green); font-size:14px;" id="r3">Backend developer/Site manager</span>
      </div>
      <div class="team-member">
        <div class="member-avatar">و</div>
        <h3>وضاح شافعي</h3>
        <span style="color:var(--saudi-green); font-size:14px;" id="r3">Backend developer</span>
      </div>

      <div class="team-member">
        <div class="member-avatar">ب</div>
        <h3>براء الخثعمي</h3>
        <span style="color:var(--saudi-green); font-size:14px;" id="r3">Backend developer</span>
      </div>
    </div>
  </section>

  <footer>
    <p id="copy">&copy; 2025 جميع الحقوق محفوظة لمنصة تراث المملكة.</p>
  </footer>

  <script>
    function toggleTheme() {
      document.body.classList.toggle('light-mode');
      const icon = document.getElementById('theme-icon');
      if (document.body.classList.contains('light-mode')) {
        icon.classList.replace('bx-sun', 'bx-moon');
        localStorage.setItem('theme', 'light');
      } else {
        icon.classList.replace('bx-moon', 'bx-sun');
        localStorage.setItem('theme', 'dark');
      }
    }
    if (localStorage.getItem('theme') === 'light') {
      document.body.classList.add('light-mode');
      document.getElementById('theme-icon').classList.replace('bx-sun', 'bx-moon');
    }

    const txt = {
      ar: {
        navLogo: "منصة تراث", abTitle: "قصة <span>شغف</span> وتراث",
        abDesc: "في عصر التكنولوجيا، لاحظنا فجوة بين الأجيال ولغتهم الأم. منصة تراث هي الجسر.",
        methTitle: "منهجية <span>عملنا</span>", methP1: "نحن لا نجمع الكلمات عشوائياً:",
        m1: "جمع البيانات من المصادر الموثوقة.", m2: "مراجعة لغوية دقيقة.", m3: "تصنيف جغرافي دقيق.",
        valTitle: "قيمنا <span>الراسخة</span>", v1t: "الدقة", v1d: "معلومات موثوقة.", v2t: "الابتكار", v2d: "تقنية تخدم الثقافة.", v3t: "الشمولية", v3d: "كل المناطق.",
        teamTitle: "العقول خلف <span>المنصة</span>", teamDesc: "طلاب شغوفين مهتمين بالثقافة السعودية والتقنية.",
        r1: "Team leader/Frontend developer", r2: "Frontend developer", r3: "Frontend developer",
        copy: "&copy; 2025 جميع الحقوق محفوظة لمنصة تراث"
      },
      en: {
        navLogo: "Torath Platform", abTitle: "A Story of <span>Passion</span>",
        abDesc: "In the tech era, we noticed a gap between generations and their heritage. Torath is the bridge.",
        methTitle: "Our <span>Methodology</span>", methP1: "We verify every piece of info:",
        m1: "Data collection from trusted sources.", m2: "Linguistic review.", m3: "Accurate geographical classification.",
        valTitle: "Our <span>Values</span>", v1t: "Accuracy", v1d: "Reliable info.", v2t: "Innovation", v2d: "Tech for culture.", v3t: "Inclusivity", v3d: "All regions.",
        teamTitle: "Minds Behind <span>It</span>", teamDesc: "Passionate Saudi team.",
        r1: "Team leader/Frontend developer", r2: "Frontend developer", r3: "Frontend developer",
        copy: "&copy; 2025 All rights reserved to Torath Platform"
      }
    };

    function toggleLanguage() {
      const html = document.documentElement;
      const newLang = html.getAttribute('lang') === 'ar' ? 'en' : 'ar';
      html.setAttribute('lang', newLang);
      html.setAttribute('dir', newLang === 'ar' ? 'rtl' : 'ltr');
      document.getElementById('lang-btn').innerText = newLang === 'ar' ? 'EN' : 'عربي';
      if (newLang === 'en') document.body.style.fontFamily = "'Outfit', sans-serif";
      else document.body.style.fontFamily = "'Cairo', sans-serif";

      const t = txt[newLang];
      document.getElementById('nav-logo').innerText = t.navLogo;
      document.getElementById('ab-title').innerHTML = t.abTitle; document.getElementById('ab-desc').innerText = t.abDesc;
      document.getElementById('meth-title').innerHTML = t.methTitle; document.getElementById('meth-p1').innerText = t.methP1;
      document.getElementById('m1').innerText = t.m1; document.getElementById('m2').innerText = t.m2; document.getElementById('m3').innerText = t.m3;
      document.getElementById('val-title').innerHTML = t.valTitle;
      document.getElementById('v1-t').innerText = t.v1t; document.getElementById('v1-d').innerText = t.v1d;
      document.getElementById('v2-t').innerText = t.v2t; document.getElementById('v2-d').innerText = t.v2d;
      document.getElementById('v3-t').innerText = t.v3t; document.getElementById('v3-d').innerText = t.v3d;
      document.getElementById('team-title').innerHTML = t.teamTitle; document.getElementById('team-desc').innerText = t.teamDesc;
      document.getElementById('r1').innerText = t.r1; document.getElementById('r2').innerText = t.r2; document.getElementById('r3').innerText = t.r3;
      document.getElementById('copy').innerHTML = t.copy;

      localStorage.setItem('lang', newLang);
    }
    if (localStorage.getItem('lang') === 'en') toggleLanguage();
  </script>
</body>

</html>
