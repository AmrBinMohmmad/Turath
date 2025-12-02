<?php

session_start();
require_once 'db.php'; 

// جلب الإحصائيات الحقيقية
$users_count = $conn->query("SELECT COUNT(*) FROM if0_40458841_users_db.users")->fetch_row()[0] ?? 0;
$cards_count = $conn->query("SELECT COUNT(*) FROM if0_40458841_projects.cards")->fetch_row()[0] ?? 0;
$questions_count = $conn->query("SELECT COUNT(*) FROM if0_40458841_projects.cards_questions")->fetch_row()[0] ?? 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة تراث | الموسوعة التفاعلية للهجات السعودية</title>
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-main: #020617; --bg-secondary: #0f172a; --text-main: #f8fafc; --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 255, 255, 0.05);
            --saudi-green: #006C35; --gold-light: #F2D06B; --gold-dark: #C69320;
            --h1: clamp(36px, 5vw, 64px); --h2: clamp(28px, 4vw, 48px); --p: clamp(15px, 2vw, 18px);
        }

        body.light-mode {
            --bg-main: #fcfbf9; --bg-secondary: #f1f5f9; --text-main: #1e293b; --text-muted: #475569;
            --glass-bg: rgba(255, 255, 255, 0.8); --glass-border: rgba(0, 0, 0, 0.05);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; transition: all 0.3s ease; }
        body { font-family: 'Cairo', sans-serif; background-color: var(--bg-main); color: var(--text-main); overflow-x: hidden; }

        /* Navbar */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; position: fixed; width: 100%; top: 0; z-index: 1000; background: rgba(var(--bg-main), 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid var(--glass-border); }
        .logo { font-size: 26px; font-weight: 800; color: var(--text-main); text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .logo i { color: var(--saudi-green); font-size: 32px; }
        .nav-actions { display: flex; align-items: center; gap: 15px; }
        
        .icon-btn { width: 40px; height: 40px; border-radius: 50%; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-main); display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; }
        .icon-btn:hover { background: var(--saudi-green); color: white; border-color: var(--saudi-green); }
        .btn-gold { background: linear-gradient(135deg, var(--gold-light), var(--gold-dark)); color: #000; padding: 10px 25px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 14px; box-shadow: 0 5px 15px rgba(198, 147, 32, 0.2); }
        .btn-gold:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(198, 147, 32, 0.4); }

        /* Hero */
        .hero { min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 120px 20px; background: radial-gradient(circle at 50% 30%, rgba(0, 108, 53, 0.15), transparent 70%); position: relative; }
        .hero::after { content: ''; position: absolute; top:0; left:0; width:100%; height:100%; background: url('https://www.transparenttextures.com/patterns/arabesque.png'); opacity: 0.04; pointer-events: none; }
        
        .hero h1 { font-size: var(--h1); font-weight: 900; line-height: 1.3; margin-bottom: 25px; }
        .hero h1 span { background: linear-gradient(to right, var(--gold-light), var(--gold-dark)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: var(--p); color: var(--text-muted); max-width: 800px; margin: 0 auto 40px; line-height: 1.8; }
        
        .cta-box { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
        .btn-outline { border: 2px solid var(--gold-dark); color: var(--gold-dark); padding: 10px 30px; border-radius: 50px; text-decoration: none; font-weight: bold; }
        .btn-outline:hover { background: var(--gold-dark); color: white; }

        /* Stats */
        .stats-bar { display: flex; justify-content: space-around; flex-wrap: wrap; gap: 30px; padding: 60px 5%; background: var(--bg-secondary); border-block: 1px solid var(--glass-border); }
        .stat-item { text-align: center; min-width: 150px; }
        .stat-num { font-size: 48px; font-weight: 900; color: var(--saudi-green); display: block; margin-bottom: 5px; }
        .stat-label { color: var(--text-muted); font-size: 16px; font-weight: 600; }

        /* Content Sections */
        .section { padding: 100px 5%; }
        .sec-header { text-align: center; margin-bottom: 60px; max-width: 800px; margin-left: auto; margin-right: auto; }
        .sec-header h2 { font-size: var(--h2); margin-bottom: 15px; }
        .sec-header span { color: var(--gold-light); }
        .sec-header p { font-size: var(--p); color: var(--text-muted); line-height: 1.7; }

        /* Cards Grid */
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; }
        
        .info-card { background: var(--glass-bg); border: 1px solid var(--glass-border); padding: 40px; border-radius: 20px; transition: 0.3s; position: relative; overflow: hidden; }
        .info-card:hover { transform: translateY(-10px); border-color: var(--saudi-green); box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .icon-box { width: 70px; height: 70px; background: rgba(0, 108, 53, 0.1); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 32px; color: var(--saudi-green); margin-bottom: 25px; }
        .info-card h3 { font-size: 22px; margin-bottom: 15px; }
        .info-card p { color: var(--text-muted); line-height: 1.7; }

        /* How it works */
        .steps-container { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; margin-top: 50px; }
        .step-item { flex: 1; min-width: 250px; text-align: center; position: relative; }
        .step-num { width: 50px; height: 50px; background: var(--gold-dark); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; margin: 0 auto 20px; box-shadow: 0 0 20px rgba(198, 147, 32, 0.4); }
        .step-item h4 { font-size: 20px; margin-bottom: 10px; }
        .step-item p { color: var(--text-muted); font-size: 14px; }

        /* Footer */
        footer { background: #02040a; padding: 60px 5% 30px; border-top: 1px solid var(--glass-border); text-align: center; }
        .footer-logo { font-size: 30px; font-weight: 900; color: white; margin-bottom: 20px; display: inline-block; }
        .footer-logo span { color: var(--saudi-green); }
        .copyright { color: #475569; font-size: 14px; margin-top: 40px; border-top: 1px solid #1e293b; padding-top: 20px; }

        @media (max-width: 768px) {
            .navbar { padding: 15px 20px; }
            .hero { padding-top: 140px; }
            .stats-bar { gap: 40px; }
            .cta-box { flex-direction: column; width: 100%; }
            .btn-gold, .btn-outline { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="logo">
            <i class='bx bxl-flutter'></i>
            <span id="nav-logo">منصة تراث</span>
        </a>
        <div class="nav-actions">
            <a href="about.php" class="icon-btn" title="About"><i class='bx bx-info-circle'></i></a>
            <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
            <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="user_page.php" class="btn-gold" id="nav-dash">لوحة التحكم</a>
                <a href="logout.php" class="icon-btn" style="color:#ef4444; border-color:rgba(239,68,68,0.3);" title="خروج"><i class='bx bx-log-out'></i></a>
            <?php else: ?>
                <a href="login.php" class="btn-gold" id="nav-login">تسجيل الدخول</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <h1 id="hero-title">جذورنا عميقة.. <br> <span>وثقافتنا حياة</span></h1>
            <p id="hero-desc">
                انطلق في رحلة رقمية غامرة لاستكشاف كنوز اللهجات السعودية. من قمم جبال السروات إلى رمال الربع الخالي، نجمع لك آلاف الكلمات والأمثال الشعبية في منصة تعليمية تفاعلية تعتمد على التحدي والمنافسة.
            </p>
            <div class="cta-box">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="user_page.php" class="btn-gold" id="btn-start">ابدأ التحدي الآن</a>
                <?php else: ?>
                    <a href="signup.php" class="btn-gold" id="btn-join">أنشئ حساباً مجانياً</a>
                    <a href="login.php" class="btn-outline" id="btn-browse">تصفح كزائر</a>
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

    <section class="section">
        <div class="sec-header">
            <h2 id="feat-title">لماذا <span>منصة تراث؟</span></h2>
            <p id="feat-desc">نحن لا نقدم مجرد معلومات، بل نقدم تجربة ثقافية متكاملة تصقل معرفتك وتربطك بجذورك.</p>
        </div>
        <div class="grid-3">
            <div class="info-card">
                <div class="icon-box"><i class='bx bxs-graduation'></i></div>
                <h3 id="c1-title">تعليم بالترفيه</h3>
                <p id="c1-desc">نستخدم أساليب التلعيب (Gamification) لتحويل عملية تعلم المصطلحات الصعبة إلى لعبة ممتعة وتنافسية.</p>
            </div>
            <div class="info-card">
                <div class="icon-box"><i class='bx bxs-map-alt'></i></div>
                <h3 id="c2-title">تغطية جغرافية شاملة</h3>
                <p id="c2-desc">محتوانا لا يقتصر على منطقة واحدة، بل يغطي اللهجات النجدية، الحجازية، الجنوبية، الشمالية، والشرقية بدقة عالية.</p>
            </div>
            <div class="info-card">
                <div class="icon-box"><i class='bx bxs-analyse'></i></div>
                <h3 id="c3-title">تحليل ذكي للأداء</h3>
                <p id="c3-desc">خوارزميات ذكية تتابع تقدمك، تحدد نقاط ضعفك، وتقترح عليك تحديات مخصصة لرفع مستواك اللغوي.</p>
            </div>
        </div>
    </section>

    <section class="section" style="background: var(--bg-secondary);">
        <div class="sec-header">
            <h2 id="work-title">كيف <span>تعمل المنصة؟</span></h2>
            <p id="work-desc">خطوات بسيطة تفصلك عن أن تكون خبيراً في اللهجات السعودية.</p>
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
                <p id="s2-d">حدد المنطقة أو اللهجة التي ترغب بتحدي نفسك فيها.</p>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <h4 id="s3-t">اجمع النقاط</h4>
                <p id="s3-d">أجب بشكل صحيح، اكسب الـ XP وتصدر القائمة.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="sec-header">
            <h2 id="cult-title">الفسيفساء <span>الثقافية</span></h2>
            <p id="cult-desc">المملكة قارة ثقافية، وكل منطقة تروي قصة مختلفة.</p>
        </div>
        <div class="grid-3">
            <div class="info-card" style="border-color: var(--gold-dark);">
                <h3 id="r1-t">نجد العذية</h3>
                <p id="r1-d">قلب المملكة النابض، تتميز بمفرداتها الجزلة وشعرها النبطي الأصيل الذي يمتد لقرون.</p>
            </div>
            <div class="info-card" style="border-color: var(--saudi-green);">
                <h3 id="r2-t">الحجاز العريق</h3>
                <p id="r2-d">بوابة الحرمين، حيث تمتزج الثقافات لتنتج لهجة حضرية سهلة ومحببة للجميع.</p>
            </div>
            <div class="info-card" style="border-color: #3b82f6;">
                <h3 id="r3-t">الجنوب الخلاب</h3>
                <p id="r3-d">أرض الجبال والضباب، تتميز بتنوع لهجاتها من منطقة لأخرى وقربها من العربية الفصحى.</p>
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
        // --- 1. Theme Logic ---
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

        // --- 2. Language Logic ---
        const txt = {
            ar: {
                navLogo: "منصة تراث", navDash: "لوحة التحكم", navLogin: "تسجيل الدخول",
                heroTitle: "جذورنا عميقة.. <br> <span>وثقافتنا حياة</span>",
                heroDesc: "انطلق في رحلة رقمية غامرة لاستكشاف كنوز اللهجات السعودية. من قمم جبال السروات إلى رمال الربع الخالي، نجمع لك آلاف الكلمات.",
                btnStart: "ابدأ التحدي الآن", btnJoin: "أنشئ حساباً مجانياً", btnBrowse: "تصفح كزائر",
                stQ: "سؤال وتحدي", stU: "متعلم شغوف", stC: "مسار تعليمي",
                featTitle: "لماذا <span>منصة تراث؟</span>", featDesc: "نحن لا نقدم مجرد معلومات، بل نقدم تجربة ثقافية متكاملة.",
                c1t: "تعليم بالترفيه", c1d: "نستخدم أساليب التلعيب لتحويل التعلم إلى متعة.",
                c2t: "تغطية شاملة", c2d: "محتوى يغطي كافة مناطق المملكة بدقة عالية.",
                c3t: "تحليل ذكي", c3d: "خوارزميات تتابع تقدمك وتحدد نقاط ضعفك.",
                workTitle: "كيف <span>تعمل المنصة؟</span>", workDesc: "خطوات بسيطة تفصلك عن الإتقان.",
                s1t: "سجل حسابك", s1d: "أنشئ حساباً في ثوانٍ.",
                s2t: "اختر المنطقة", s2d: "حدد اللهجة التي تريد تعلمها.",
                s3t: "اجمع النقاط", s3d: "أجب وتصدر القائمة.",
                cultTitle: "الفسيفساء <span>الثقافية</span>", cultDesc: "المملكة قارة ثقافية متنوعة.",
                r1t: "نجد العذية", r1d: "قلب المملكة النابض بمفرداتها الجزلة.",
                r2t: "الحجاز العريق", r2d: "بوابة الحرمين ولهجتها الحضرية.",
                r3t: "الجنوب الخلاب", r3d: "أرض الجبال وقربها من الفصحى.",
                footDesc: "المنصة الأولى المتخصصة في التراث اللفظي السعودي.",
                copy: "&copy; 2024 جميع الحقوق محفوظة لمنصة تراث المملكة."
            },
            en: {
                navLogo: "Torath Platform", navDash: "Dashboard", navLogin: "Sign In",
                heroTitle: "Deep Roots.. <br> <span>Culture is Life</span>",
                heroDesc: "Embark on an immersive digital journey to explore Saudi dialects. From the peaks of Sarawat to the sands of the Empty Quarter.",
                btnStart: "Start Challenge", btnJoin: "Join for Free", btnBrowse: "Browse as Guest",
                stQ: "Challenges", stU: "Learners", stC: "Learning Paths",
                featTitle: "Why <span>Torath?</span>", featDesc: "We don't just offer info, we offer a complete cultural experience.",
                c1t: "Edutainment", c1d: "Using gamification to turn learning into fun.",
                c2t: "Full Coverage", c2d: "Content covering all regions with high accuracy.",
                c3t: "Smart Analytics", c3d: "Algorithms to track progress and spot weaknesses.",
                workTitle: "How it <span>Works?</span>", workDesc: "Simple steps to mastery.",
                s1t: "Register", s1d: "Create an account in seconds.",
                s2t: "Choose Region", s2d: "Pick the dialect you want to learn.",
                s3t: "Earn Points", s3d: "Answer correctly and rank up.",
                cultTitle: "Cultural <span>Mosaic</span>", cultDesc: "The Kingdom is a diverse cultural continent.",
                r1t: "Najd", r1d: "The heart of the Kingdom with its rich vocabulary.",
                r2t: "Hejaz", r2d: "The gateway to the Two Holy Mosques.",
                r3t: "The South", r3d: "Land of mountains, close to classical Arabic.",
                footDesc: "The #1 platform for Saudi verbal heritage.",
                copy: "&copy; 2024 All rights reserved to Torath Platform."
            }
        };

        function toggleLanguage() {
            const html = document.documentElement;
            const isAr = html.getAttribute('lang') === 'ar';
            const newLang = isAr ? 'en' : 'ar';
            
            html.setAttribute('lang', newLang);
            html.setAttribute('dir', newLang === 'ar' ? 'rtl' : 'ltr');
            document.getElementById('lang-btn').innerText = isAr ? 'عربي' : 'EN';
            
            if(newLang === 'en') document.body.style.fontFamily = "'Outfit', sans-serif";
            else document.body.style.fontFamily = "'Cairo', sans-serif";

            const t = txt[newLang];
            document.getElementById('nav-logo').innerText = t.navLogo;
            if(document.getElementById('nav-dash')) document.getElementById('nav-dash').innerText = t.navDash;
            if(document.getElementById('nav-login')) document.getElementById('nav-login').innerText = t.navLogin;
            
            document.getElementById('hero-title').innerHTML = t.heroTitle;
            document.getElementById('hero-desc').innerText = t.heroDesc;
            
            if(document.getElementById('btn-start')) document.getElementById('btn-start').innerText = t.btnStart;
            if(document.getElementById('btn-join')) document.getElementById('btn-join').innerText = t.btnJoin;
            if(document.getElementById('btn-browse')) document.getElementById('btn-browse').innerText = t.btnBrowse;

            document.getElementById('st-q').innerText = t.stQ;
            document.getElementById('st-u').innerText = t.stU;
            document.getElementById('st-c').innerText = t.stC;

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

            document.getElementById('cult-title').innerHTML = t.cultTitle;
            document.getElementById('cult-desc').innerText = t.cultDesc;
            document.getElementById('r1-t').innerText = t.r1t; document.getElementById('r1-d').innerText = t.r1d;
            document.getElementById('r2-t').innerText = t.r2t; document.getElementById('r2-d').innerText = t.r2d;
            document.getElementById('r3-t').innerText = t.r3t; document.getElementById('r3-d').innerText = t.r3d;

            document.getElementById('foot-desc').innerText = t.footDesc;
            document.getElementById('copy').innerHTML = t.copy;

            localStorage.setItem('lang', newLang);
        }

        if (localStorage.getItem('lang') === 'en') toggleLanguage();
    </script>

</body>
</html>