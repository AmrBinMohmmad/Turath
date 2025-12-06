<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

session_start();
require_once 'db.php';

// 1. جلب الإحصائيات
$users_count = $conn->query("SELECT COUNT(*) FROM if0_40458841_users_db.users")->fetch_row()[0] ?? 0;
$cards_count = $conn->query("SELECT COUNT(*) FROM if0_40458841_projects.cards")->fetch_row()[0] ?? 0;
$questions_count = $conn->query("SELECT COUNT(*) FROM if0_40458841_projects.cards_questions")->fetch_row()[0] ?? 0;

// 2. جلب المتصدرين
$top_users_query = "SELECT username, xp, level FROM if0_40458841_users_db.users ORDER BY xp DESC LIMIT 4";
$top_users = $conn->query($top_users_query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title">منصة تراث | الموسوعة التفاعلية للهجات السعودية</title>

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">

    <style>
        /* تعريف المتغيرات للألوان (Dark/Light) */
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
            --bg-main: #f8fafc;
            --bg-secondary: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --glass-bg: #ffffff;
            --glass-border: #e2e8f0;
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

        /* Navbar */
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

        .logo i {
            color: var(--saudi-green);
            font-size: 32px;
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
            font-size: 18px;
            text-decoration: none;
        }

        .icon-btn:hover {
            background: var(--saudi-green);
            color: white;
            border-color: var(--saudi-green);
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            color: #000;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 5px 15px rgba(198, 147, 32, 0.2);
        }

        .btn-gold:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(198, 147, 32, 0.4);
        }

        /* Hero */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 20px;
            position: relative;
            overflow: hidden;
            background: var(--bg-main);
        }

        .hero-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        /* في الوضع الفاتح نخفف الظلام لكي يظهر الفيديو بشكل أفتح قليلاً أو نبقيه كما هو للتباين مع النص الأبيض */
        body.light-mode .hero-overlay {
            background: rgba(255, 255, 255, 0.4);
        }

        body.light-mode .hero h1,
        body.light-mode .hero p {
            color: #000;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.8);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.transparenttextures.com/patterns/arabesque.png');
            opacity: 0.04;
            pointer-events: none;
            z-index: 1;
        }

        .hero h1 {
            font-size: var(--h1);
            font-weight: 900;
            line-height: 1.3;
            margin-bottom: 25px;
            color: #fff;
        }

        /* Hero text always white or handled via override */
        .hero h1 span {
            background: linear-gradient(to right, var(--gold-light), var(--gold-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: var(--p);
            color: #cbd5e1;
            max-width: 800px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }

        /* Stats */
        .stats-bar {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 30px;
            padding: 60px 5%;
            background: var(--bg-secondary);
            border-block: 1px solid var(--glass-border);
        }

        .stat-item {
            text-align: center;
            min-width: 150px;
        }

        .stat-num {
            font-size: 48px;
            font-weight: 900;
            color: var(--saudi-green);
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 600;
        }

        /* Leaderboard */
        .leaderboard-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .rank-card {
            background: var(--bg-main);
            border: 1px solid var(--glass-border);
            padding: 30px 20px;
            border-radius: 20px;
            text-align: center;
            position: relative;
            width: 250px;
            transition: 0.3s;
        }

        .rank-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .rank-card.rank-1 {
            border-color: var(--gold-light);
            box-shadow: 0 0 30px rgba(242, 208, 107, 0.15);
            background: linear-gradient(to bottom, rgba(242, 208, 107, 0.05), var(--bg-main));
        }

        .rank-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: var(--bg-secondary);
            border: 2px solid var(--glass-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 18px;
            color: var(--text-main);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }

        .rank-card.rank-1 .rank-badge {
            background: var(--gold-light);
            color: #000;
            border-color: var(--gold-light);
        }

        .u-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #1e293b, #334155);
            border-radius: 50%;
            margin: 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 900;
            color: white;
            border: 3px solid var(--glass-border);
        }

        .u-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-main);
        }

        .u-xp {
            font-size: 16px;
            color: var(--saudi-green);
            font-weight: bold;
        }

        .u-lvl {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 5px;
            display: block;
        }

        /* General Sections */
        .section {
            padding: 100px 5%;
        }

        .sec-header {
            text-align: center;
            margin-bottom: 60px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .sec-header h2 {
            font-size: var(--h2);
            margin-bottom: 15px;
        }

        .sec-header span {
            color: var(--gold-light);
        }

        .sec-header p {
            font-size: var(--p);
            color: var(--text-muted);
            line-height: 1.7;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }

        .info-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 20px;
            transition: 0.3s;
        }

        .info-card:hover {
            transform: translateY(-10px);
            border-color: var(--saudi-green);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: rgba(0, 108, 53, 0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: var(--saudi-green);
            margin-bottom: 25px;
        }

        .info-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        .info-card p {
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* Steps */
        .steps-container {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            margin-top: 50px;
        }

        .step-item {
            flex: 1;
            min-width: 250px;
            text-align: center;
        }

        .step-num {
            width: 50px;
            height: 50px;
            background: var(--gold-dark);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            margin: 0 auto 20px;
            box-shadow: 0 0 20px rgba(198, 147, 32, 0.4);
        }

        .step-item h4 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .step-item p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Footer */
        footer {
            background: #02040a;
            padding: 60px 5% 30px;
            border-top: 1px solid var(--glass-border);
            text-align: center;
        }

        body.light-mode footer {
            background: #1e293b;
            border-top-color: #334155;
        }

        body.light-mode footer p,
        body.light-mode footer .footer-logo {
            color: #f8fafc;
        }

        .footer-logo {
            font-size: 30px;
            font-weight: 900;
            color: white;
            margin-bottom: 20px;
            display: inline-block;
        }

        .footer-logo span {
            color: var(--saudi-green);
        }

        .copyright {
            color: #475569;
            font-size: 14px;
            margin-top: 40px;
            border-top: 1px solid #1e293b;
            padding-top: 20px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }

            .hero {
                padding-top: 140px;
            }

            .stats-bar {
                gap: 40px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="logo">
            <i class='bx bxl-flutter'></i>
            <span id="nav-logo">منصة تراث</span>
        </a>
        <div class="nav-actions">
            <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
            <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user_page.php" class="btn-gold" id="nav-dash">لوحة التحكم</a>
                <a href="logout.php" class="icon-btn" style="color:#ef4444; border-color:rgba(239,68,68,0.3);"><i
                        class='bx bx-log-out'></i></a>
            <?php else: ?>
                <a href="login.php" class="btn-gold" id="nav-login">تسجيل الدخول</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <video autoplay muted loop playsinline class="hero-video">
            <source src="Saudi.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 id="hero-title">جذورنا عميقة.. <br> <span>وثقافتنا حياة</span></h1>
            <p id="hero-desc">
                انطلق في رحلة رقمية غامرة لاستكشاف كنوز اللهجات السعودية. من قمم جبال السروات إلى رمال الربع الخالي.
            </p>
            <div class="cta-box">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="user_page.php" class="btn-gold" id="btn-start">ابدأ التحدي الآن</a>
                <?php else: ?>
                    <a href="signup.php" class="btn-gold" id="btn-join">أنشئ حساباً مجانياً</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-num">+<?= $questions_count ?></span>
            <span class="stat-label" id="st-q">سؤال وتحدي</span>
        </div>
        <div class="stat-item">
            <span class="stat-num">+<?= $users_count ?></span>
            <span class="stat-label" id="st-u">متعلم شغوف</span>
        </div>
        <div class="stat-item">
            <span class="stat-num">+<?= $cards_count ?></span>
            <span class="stat-label" id="st-c">مسار تعليمي</span>
        </div>
    </div>

    <section class="section" style="background: var(--bg-secondary);">
        <div class="sec-header">
            <h2 id="leader-title">فرسان <span>المنصة</span></h2>
            <p id="leader-desc">قائمة بالمتميزين الذين أثبتوا جدارتهم في إتقان لهجاتنا وجمع النقاط.</p>
        </div>

        <div class="leaderboard-container">
            <?php
            if ($top_users && $top_users->num_rows > 0):
                $rank = 1;
                while ($user = $top_users->fetch_assoc()):
                    $initial = strtoupper(mb_substr($user['username'], 0, 1, 'UTF-8'));
                    $rankClass = ($rank <= 3) ? "rank-$rank" : "";
                    ?>
                    <div class="rank-card <?= $rankClass ?>">
                        <div class="rank-badge"><?= $rank ?></div>
                        <div class="u-avatar"><?= $initial ?></div>
                        <div class="u-name"><?= htmlspecialchars($user['username']) ?></div>
                        <div class="u-xp"><i class='bx bxs-zap'></i> <?= number_format($user['xp']) ?> XP</div>
                        <span class="u-lvl"><span class="lbl-lvl">المستوى</span> <?= $user['level'] ?></span>
                    </div>
                    <?php
                    $rank++;
                endwhile;
            else:
                ?>
                <p style="color:var(--text-muted);">لا يوجد بيانات حالياً.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="section">
        <div class="sec-header">
            <h2 id="feat-title">لماذا <span>منصة تراث؟</span></h2>
            <p id="feat-desc">تجربة ثقافية متكاملة تصقل معرفتك وتربطك بجذورك.</p>
        </div>
        <div class="grid-3">
            <div class="info-card">
                <div class="icon-box"><i class='bx bxs-graduation'></i></div>
                <h3 id="c1-title">تعليم بالترفيه</h3>
                <p id="c1-desc">نستخدم أساليب التلعيب لتحويل عملية التعلم إلى لعبة ممتعة.</p>
            </div>
            <div class="info-card">
                <div class="icon-box"><i class='bx bxs-map-alt'></i></div>
                <h3 id="c2-title">تغطية شاملة</h3>
                <p id="c2-desc">محتوانا يغطي كافة مناطق المملكة من الشرق إلى الغرب.</p>
            </div>
            <div class="info-card">
                <div class="icon-box"><i class='bx bxs-analyse'></i></div>
                <h3 id="c3-title">تحليل ذكي</h3>
                <p id="c3-desc">خوارزميات تتابع تقدمك وتقترح عليك تحديات مخصصة.</p>
            </div>
        </div>
    </section>

    <section class="section" style="background: var(--bg-secondary);">
        <div class="sec-header">
            <h2 id="work-title">كيف <span>تعمل المنصة؟</span></h2>
            <p id="work-desc">خطوات بسيطة تفصلك عن الإتقان.</p>
        </div>
        <div class="steps-container">
            <div class="step-item">
                <div class="step-num">1</div>
                <h4 id="s1-t">سجل حسابك</h4>
                <p id="s1-d">أنشئ حساباً شخصياً في ثوانٍ لحفظ تقدمك.</p>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <h4 id="s2-t">اختر المنطقة</h4>
                <p id="s2-d">حدد اللهجة التي ترغب بتحدي نفسك فيها.</p>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <h4 id="s3-t">اجمع النقاط</h4>
                <p id="s3-d">أجب بشكل صحيح، اكسب الـ XP وتصدر القائمة.</p>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-logo">تراث <span>المملكة</span></div>
        <p id="foot-desc">المنصة الأولى المتخصصة في أرشفة وتعليم التراث اللفظي السعودي.</p>
        <div class="copyright">
            <p id="copy">&copy; 2024 جميع الحقوق محفوظة لمنصة تراث المملكة.</p>
        </div>
    </footer>

    <script>
        // Dictionary
        const txt = {
            ar: {
                pageTitle: "منصة تراث | الرئيسية",
                navLogo: "منصة تراث", navDash: "لوحة التحكم", navLogin: "تسجيل الدخول",
                heroTitle: "جذورنا عميقة.. <br> <span>وثقافتنا حياة</span>",
                heroDesc: "انطلق في رحلة رقمية غامرة لاستكشاف كنوز اللهجات السعودية. من قمم جبال السروات إلى رمال الربع الخالي.",
                btnStart: "ابدأ التحدي الآن", btnJoin: "أنشئ حساباً مجانياً",
                stQ: "سؤال وتحدي", stU: "متعلم شغوف", stC: "مسار تعليمي",
                leaderTitle: "فرسان <span>المنصة</span>", leaderDesc: "قائمة بالمتميزين الذين أثبتوا جدارتهم.",
                lblLvl: "المستوى",
                featTitle: "لماذا <span>منصة تراث؟</span>", featDesc: "تجربة ثقافية متكاملة تصقل معرفتك.",
                c1t: "تعليم بالترفيه", c1d: "نستخدم أساليب التلعيب لتحويل التعلم إلى متعة.",
                c2t: "تغطية شاملة", c2d: "محتوى يغطي كافة مناطق المملكة.",
                c3t: "تحليل ذكي", c3d: "خوارزميات تتابع تقدمك وتقترح تحديات.",
                workTitle: "كيف <span>تعمل المنصة؟</span>", workDesc: "خطوات بسيطة تفصلك عن الإتقان.",
                s1t: "سجل حسابك", s1d: "أنشئ حساباً في ثوانٍ.",
                s2t: "اختر المنطقة", s2d: "حدد اللهجة التي تريد تعلمها.",
                s3t: "اجمع النقاط", s3d: "أجب وتصدر القائمة.",
                footDesc: "المنصة الأولى المتخصصة في التراث اللفظي السعودي.",
                copy: "&copy; 2024 جميع الحقوق محفوظة لمنصة تراث المملكة."
            },
            en: {
                pageTitle: "Torath Platform | Home",
                navLogo: "Torath Platform", navDash: "Dashboard", navLogin: "Sign In",
                heroTitle: "Deep Roots.. <br> <span>Culture is Life</span>",
                heroDesc: "Embark on an immersive digital journey to explore Saudi dialects. From the peaks of Sarawat to the Empty Quarter.",
                btnStart: "Start Challenge", btnJoin: "Join for Free",
                stQ: "Challenges", stU: "Learners", stC: "Learning Paths",
                leaderTitle: "Top <span>Achievers</span>", leaderDesc: "Distinguished users who mastered our dialects.",
                lblLvl: "Level",
                featTitle: "Why <span>Torath?</span>", featDesc: "A complete cultural experience.",
                c1t: "Edutainment", c1d: "Using gamification to turn learning into fun.",
                c2t: "Full Coverage", c2d: "Content covering all regions.",
                c3t: "Smart Analytics", c3d: "Algorithms to track progress.",
                workTitle: "How it <span>Works?</span>", workDesc: "Simple steps to mastery.",
                s1t: "Register", s1d: "Create an account in seconds.",
                s2t: "Choose Region", s2d: "Pick the dialect you want to learn.",
                s3t: "Earn Points", s3d: "Answer correctly and rank up.",
                footDesc: "The #1 platform for Saudi verbal heritage.",
                copy: "&copy; 2024 All rights reserved to Torath Platform."
            }
        };

        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            localStorage.setItem('theme', document.body.classList.contains('light-mode') ? 'light' : 'dark');
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const icon = document.getElementById('theme-icon');
            if (document.body.classList.contains('light-mode')) icon.classList.replace('bx-sun', 'bx-moon');
            else icon.classList.replace('bx-moon', 'bx-sun');
        }

        function toggleLanguage() {
            const currentLang = localStorage.getItem('lang') || 'ar';
            const newLang = currentLang === 'ar' ? 'en' : 'ar';
            localStorage.setItem('lang', newLang);
            applyLanguage(newLang);
        }

        function applyLanguage(lang) {
            document.documentElement.lang = lang;
            document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
            document.getElementById('lang-btn').innerText = lang === 'ar' ? 'EN' : 'عربي';
            document.body.style.fontFamily = lang === 'en' ? "'Outfit', sans-serif" : "'Cairo', sans-serif";

            const t = txt[lang];

            // Updating Elements
            document.getElementById('page-title').innerText = t.pageTitle;
            document.getElementById('nav-logo').innerText = t.navLogo;
            if (document.getElementById('nav-dash')) document.getElementById('nav-dash').innerText = t.navDash;
            if (document.getElementById('nav-login')) document.getElementById('nav-login').innerText = t.navLogin;

            document.getElementById('hero-title').innerHTML = t.heroTitle;
            document.getElementById('hero-desc').innerText = t.heroDesc;
            if (document.getElementById('btn-start')) document.getElementById('btn-start').innerText = t.btnStart;
            if (document.getElementById('btn-join')) document.getElementById('btn-join').innerText = t.btnJoin;

            document.getElementById('st-q').innerText = t.stQ;
            document.getElementById('st-u').innerText = t.stU;
            document.getElementById('st-c').innerText = t.stC;

            document.getElementById('leader-title').innerHTML = t.leaderTitle;
            document.getElementById('leader-desc').innerText = t.leaderDesc;
            document.querySelectorAll('.lbl-lvl').forEach(el => el.innerText = t.lblLvl);

            document.getElementById('feat-title').innerHTML = t.featTitle;
            document.getElementById('feat-desc').innerText = t.featDesc;
            document.getElementById('c1-title').innerText = t.c1t; document.getElementById('c1-desc').innerText = t.c1d;
            document.getElementById('c2-title').innerText = t.c2t; document.getElementById('c2-desc').innerText = t.c2d;
            document.getElementById('c3-title').innerText = t.c3t; document.getElementById('c3-desc').innerText = t.c3d;

            document.getElementById('work-title').innerHTML = t.workTitle;
            document.getElementById('work-desc').innerText = t.workDesc;
            document.getElementById('s1-t').innerText = t.s1t; document.getElementById('s1-d').innerText = t.s1d;
            document.getElementById('s2-t').innerText = t.s2t; document.getElementById('s2-d').innerText = t.s2d;
            document.getElementById('s3-t').innerText = t.s3t; document.getElementById('s3-d').innerText = t.s3d;

            document.getElementById('foot-desc').innerText = t.footDesc;
            document.getElementById('copy').innerHTML = t.copy;
        }

        // Initial Load
        const storedTheme = localStorage.getItem('theme') || 'dark';
        if (storedTheme === 'light') document.body.classList.add('light-mode');
        updateThemeIcon();

        const storedLang = localStorage.getItem('lang') || 'ar';
        applyLanguage(storedLang);
    </script>
</body>

</html>