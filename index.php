<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/src/pages/db.php';

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
    <link rel="stylesheet" href="src/css/main_page.css">
    <link rel="icon" type="image/png" href="src/assets/images/Favicon.png" />
    
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="logo">
            <img src="src/assets/images/Favicon.png" alt="Logo" style="height:40px; margin-right:8px; ">
            <span id="nav-logo">منصة تراث</span>
        </a>
        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="src/pages/user/user_page.php" class="btn-gold" id="nav-dash">لوحة التحكم</a>
                <a href="src/auth/logout.php" class="icon-btn" style="color:#ef4444; border-color:rgba(239,68,68,0.3);"><i
                        class='bx bx-log-out'></i></a>
            <?php else: ?>
                <a href="src/pages/login.php" class="btn-gold" id="nav-login">تسجيل الدخول</a>
            <?php endif; ?>
            <a href="src/pages/about.php" class="icon-btn">
                <i class='bx bx-info-circle'></i>
            </a>
            <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
            <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
        </div>
    </nav>

    <header class="hero">
        <video autoplay muted loop playsinline class="hero-video">
            <source src="src/assets/videos/Saudi.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 id="hero-title">جذورنا عميقة.. <br> <span>وثقافتنا حياة</span></h1>
            <p id="hero-desc">
                انطلق في رحلة رقمية غامرة لاستكشاف كنوز اللهجات السعودية. من قمم جبال السروات إلى رمال الربع الخالي.
            </p>
            <div class="cta-box">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="src/pages/user/user_page.php" class="btn-gold" id="btn-start">ابدأ التحدي الآن</a>
                <?php else: ?>
                    <a href="src/pages/signup.php" class="btn-gold" id="btn-join">أنشئ حساباً مجانياً</a>
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
            <p id="work-desc">خطوات بسيطة تفصلك عن التعلم.</p>
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
        <div class="footer-logo">منصة <span>تراث</span></div>
        <p id="foot-desc">المنصة الأولى المتخصصة في أرشفة وتعليم التراث اللفظي السعودي.</p>
        <div class="copyright">
            <p id="copy">&copy; 2025 جميع الحقوق محفوظة لمنصة تراث المملكة.</p>
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
                workTitle: "كيف <span>تعمل المنصة؟</span>", workDesc: "خطوات بسيطة تفصلك عن التعلم.",
                s1t: "سجل حسابك", s1d: "أنشئ حساباً في ثوانٍ.",
                s2t: "اختر المنطقة", s2d: "حدد اللهجة التي تريد تعلمها.",
                s3t: "اجمع النقاط", s3d: "أجب وتصدر القائمة.",
                footDesc: "المنصة الأقضل في تعلم التراث اللفظي السعودي.",
                copy: "&copy; 2025 جميع الحقوق محفوظة لمنصة تراث."
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
                footDesc: "The best platform for learning Saudi cultural heritage.",
                copy: "&copy; 2025 All rights reserved to Torath Platform."
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
