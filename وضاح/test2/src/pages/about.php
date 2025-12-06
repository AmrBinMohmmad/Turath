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
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&family=Outfit:wght@300;400;700&display=swap"
    rel="stylesheet">
  <style>
    /* (نفس ستايل الصفحة الرئيسية تماماً - انسخه من الكود أعلاه للصق هنا لضمان التوحيد) */
    /* سأضع الإضافات الخاصة بصفحة About فقط هنا */
    :root {
      --bg-main: #020617;
      --bg-secondary: #0f172a;
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --glass-bg: rgba(255, 255, 255, 0.03);
      --glass-border: rgba(255, 255, 255, 0.05);
      --saudi-green: #006C35;
      --gold-light: #F2D06B;
      --gold-dark: #C69320;
      --h1: clamp(36px, 5vw, 64px);
      --h2: clamp(28px, 4vw, 48px);
      --p: clamp(15px, 2vw, 18px);
    }

    body.light-mode {
      --bg-main: #fcfbf9;
      --bg-secondary: #f1f5f9;
      --text-main: #1e293b;
      --text-muted: #475569;
      --glass-bg: rgba(255, 255, 255, 0.8);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      transition: all 0.3s ease;
    }

    body {
      font-family: 'Cairo', sans-serif;
      background-color: var(--bg-main);
      color: var(--text-main);
      overflow-x: hidden;
    }

    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 5%;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
      background: rgba(var(--bg-main), 0.9);
      backdrop-filter: blur(15px);
      border-bottom: 1px solid var(--glass-border);
    }

    .logo {
      font-size: 26px;
      font-weight: 800;
      color: var(--text-main);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .icon-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 1px solid var(--glass-border);
      background: var(--glass-bg);
      color: var(--text-main);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .btn-gold {
      background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
      color: #000;
      padding: 10px 25px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: bold;
      font-size: 14px;
    }

    .about-header {
      padding: 150px 5% 80px;
      text-align: center;
      background: radial-gradient(circle at 50% 0%, rgba(0, 108, 53, 0.1), transparent 60%);
    }

    .about-header h1 {
      font-size: var(--h1);
      font-weight: 900;
      margin-bottom: 20px;
    }

    .about-header h1 span {
      color: var(--saudi-green);
    }

    .about-header p {
      max-width: 800px;
      margin: 0 auto;
      color: var(--text-muted);
      font-size: var(--p);
      line-height: 1.8;
    }

    .section {
      padding: 80px 5%;
    }

    .grid-2 {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 40px;
    }

    .text-box h2 {
      font-size: var(--h2);
      margin-bottom: 20px;
    }

    .text-box p {
      color: var(--text-muted);
      line-height: 1.8;
      margin-bottom: 20px;
      font-size: 16px;
    }

    .values-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-top: 40px;
    }

    .value-card {
      background: var(--glass-bg);
      padding: 30px;
      border-radius: 15px;
      border: 1px solid var(--glass-border);
      text-align: center;
    }

    .value-card i {
      font-size: 40px;
      color: var(--gold-dark);
      margin-bottom: 15px;
    }

    .value-card h3 {
      font-size: 20px;
      margin-bottom: 10px;
    }

    .team-grid {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 30px;
      margin-top: 50px;
    }

    .team-member {
      background: var(--glass-bg);
      padding: 30px;
      border-radius: 20px;
      width: 280px;
      border: 1px solid var(--glass-border);
      text-align: center;
      transition: 0.3s;
    }

    .team-member:hover {
      transform: translateY(-10px);
      border-color: var(--saudi-green);
    }

    .member-avatar {
      width: 100px;
      height: 100px;
      background: #334155;
      border-radius: 50%;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      font-weight: bold;
      color: white;
      border: 3px solid var(--gold-dark);
    }

    footer {
      background: #02040a;
      padding: 40px;
      text-align: center;
      border-top: 1px solid var(--glass-border);
      color: #64748b;
    }

    @media (max-width: 768px) {
      .navbar {
        padding: 15px 20px;
      }
    }
  </style>
</head>

<body>

  <nav class="navbar">
    <a href="../../index.php" class="logo"><i class='bx bxl-flutter' style="color:var(--saudi-green)"></i> <span
        id="nav-logo">تراث المملكة</span></a>
    <div class="nav-actions">
      <a href="../../index.php" class="icon-btn"><i class='bx bx-home-alt'></i></a>
      <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
      <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
    </div>
  </nav>

  <section class="about-header">
    <h1 id="ab-title">قصة <span>شغف</span> وتراث</h1>
    <p id="ab-desc">في عصر التكنولوجيا المتسارعة، لاحظنا فجوة تتسع بين الأجيال الجديدة ولغتهم الأم، وتحديداً اللهجات
      المحلية الغنية. من هنا ولدت فكرة "تراث المملكة"، لتكون الجسر الرقمي الذي يربط الماضي بالحاضر.</p>
  </section>

  <section class="section">
    <div class="grid-2">
      <div class="text-box">
        <h2 id="meth-title">منهجية <span>عملنا</span></h2>
        <p id="meth-p1">نحن لا نجمع الكلمات عشوائياً. تمر كل معلومة في منصتنا بمراحل تدقيق صارمة:</p>
        <ul style="list-style:none; color:var(--text-muted);">
          <li style="margin-bottom:10px;"><i class='bx bxs-check-circle' style="color:var(--saudi-green)"></i> <span
              id="m1">جمع البيانات من المصادر الموثوقة وكبار السن.</span></li>
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
      <p id="team-desc">فريق سعودي شغوف يجمع بين الخبرة التقنية والبحث الثقافي.</p>
    </div>
    <div class="team-grid">
      <div class="team-member">
        <div class="member-avatar">S</div>
        <h3>سعود محمد</h3>
        <span style="color:var(--saudi-green); font-size:14px;" id="r1">مؤسس ومطور</span>
      </div>
      <div class="team-member">
        <div class="member-avatar">A</div>
        <h3>عبدالله علي</h3>
        <span style="color:var(--saudi-green); font-size:14px;" id="r2">باحث تراثي</span>
      </div>
      <div class="team-member">
        <div class="member-avatar">F</div>
        <h3>فهد ناصر</h3>
        <span style="color:var(--saudi-green); font-size:14px;" id="r3">مصمم تجربة المستخدم</span>
      </div>
    </div>
  </section>

  <footer>
    <p id="copy">&copy; 2024 جميع الحقوق محفوظة لمنصة تراث المملكة.</p>
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
        teamTitle: "العقول خلف <span>المنصة</span>", teamDesc: "فريق سعودي شغوف.",
        r1: "مؤسس ومطور", r2: "باحث تراثي", r3: "مصمم واجهات",
        copy: "&copy; 2024 جميع الحقوق محفوظة."
      },
      en: {
        navLogo: "Torath Platform", abTitle: "A Story of <span>Passion</span>",
        abDesc: "In the tech era, we noticed a gap between generations and their heritage. Torath is the bridge.",
        methTitle: "Our <span>Methodology</span>", methP1: "We verify every piece of info:",
        m1: "Data collection from trusted sources.", m2: "Linguistic review.", m3: "Accurate geographical classification.",
        valTitle: "Our <span>Values</span>", v1t: "Accuracy", v1d: "Reliable info.", v2t: "Innovation", v2d: "Tech for culture.", v3t: "Inclusivity", v3d: "All regions.",
        teamTitle: "Minds Behind <span>It</span>", teamDesc: "Passionate Saudi team.",
        r1: "Founder & Dev", r2: "Researcher", r3: "UI/UX Designer",
        copy: "&copy; 2024 All rights reserved."
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