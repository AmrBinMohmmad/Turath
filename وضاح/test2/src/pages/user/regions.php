<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once __DIR__ . '/../../auth/auth_guard.php';
checkUser();

require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// -----------------------------------------------------------
// 1. منطق حساب الإحصائيات
// -----------------------------------------------------------
$base_ids_query = $conn->query("SELECT card_id, COUNT(number_of_q) AS base_id_count FROM if0_40458841_projects.cards_questions WHERE number_of_q IS NOT NULL GROUP BY card_id");
$cards_total_questions = [];
if ($base_ids_query) {
    while ($row = $base_ids_query->fetch_assoc()) {
        $cards_total_questions[$row['card_id']] = (int) $row['base_id_count'];
    }
}

$all_answers_query = $conn->query("SELECT user_id, project_id, COUNT(*) AS answered_count FROM if0_40458841_projects.annotations GROUP BY user_id, project_id");
$completed_users_per_card = [];

if ($all_answers_query) {
    while ($row = $all_answers_query->fetch_assoc()) {
        $c_id = $row['project_id'];
        $count = $row['answered_count'];
        $total_q_for_card = $cards_total_questions[$c_id] ?? 0;
        if ($total_q_for_card > 0 && $count >= $total_q_for_card) {
            if (!isset($completed_users_per_card[$c_id])) {
                $completed_users_per_card[$c_id] = 0;
            }
            $completed_users_per_card[$c_id]++;
        }
    }
}

// -----------------------------------------------------------
// 2. جلب الكاردات
// -----------------------------------------------------------
$sql = "SELECT c.*, (SELECT COUNT(*) FROM if0_40458841_projects.annotations a WHERE a.project_id = c.id AND a.user_id = $user_id) as my_solved FROM if0_40458841_projects.cards c ORDER BY c.id DESC";
$result = $conn->query($sql);

$cards_by_region = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dialect = $row['Dialect_type'];
        $cards_by_region[$dialect][] = $row;
    }
}

// بيانات المناطق (المفاتيح ستستخدم للترجمة)
$regions_info = [
    'Central' => ['icon' => 'bx-sun'],
    'Western' => ['icon' => 'bx-buildings'],
    'Southern' => ['icon' => 'bx-landscape'],
    'Eastern' => ['icon' => 'bx-water'],
    'Northern' => ['icon' => 'bx-compass'],
    'all' => ['icon' => 'bx-world'],
    'General' => ['icon' => 'bx-chat']
];
$display_order = ['Central', 'Western', 'Southern', 'Eastern', 'Northern', 'all', 'General'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title">مناطق المملكة | تحديات تراثية</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">

    <style>
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
        }

        body.light-mode {
            --bg-main: #fcfbf9;
            --bg-secondary: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #475569;
            --glass-bg: #ffffff;
            --glass-border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
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
            text-decoration: none;
            font-size: 20px;
        }

        .icon-btn:hover {
            background: var(--saudi-green);
            color: white;
            border-color: var(--saudi-green);
        }

        .container {
            max-width: 1400px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 60px;
            padding: 40px 0;
            border-bottom: 1px solid var(--glass-border);
        }

        .page-header h1 {
            font-size: 48px;
            margin-bottom: 10px;
            font-weight: 900;
        }

        .page-header span {
            color: var(--gold-light);
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
        }

        .region-section {
            margin-bottom: 60px;
            scroll-margin-top: 100px;
        }

        .region-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--glass-border);
        }

        .region-icon {
            font-size: 30px;
            color: var(--saudi-green);
            background: rgba(0, 108, 53, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .region-info h2 {
            font-size: 24px;
            margin: 0;
            color: var(--text-main);
        }

        .region-info p {
            color: var(--text-muted);
            font-size: 14px;
            margin: 0;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .card-item {
            background: var(--glass-bg);
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            overflow: hidden;
            transition: 0.3s;
            display: flex;
            flex-direction: column;
        }

        .card-item:hover {
            transform: translateY(-5px);
            border-color: var(--gold-dark);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.4;
        }

        .type-badge {
            font-size: 11px;
            padding: 4px 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        body.light-mode .type-badge {
            background: #e2e8f0;
            color: #475569;
        }

        .seats-info {
            font-size: 13px;
            font-weight: bold;
            padding: 8px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .seats-open {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .seats-full {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .seats-num {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        .progress-box {
            margin-top: auto;
        }

        .progress-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        body.light-mode .progress-bar {
            background: #cbd5e1;
        }

        .progress-fill {
            height: 100%;
            background: var(--saudi-green);
            border-radius: 3px;
        }

        .progress-text {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
        }

        .btn-start {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            color: #000;
            text-decoration: none;
            padding: 10px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn-start:hover {
            box-shadow: 0 4px 12px rgba(198, 147, 32, 0.3);
            transform: scale(1.02);
        }

        .btn-completed {
            background: transparent;
            border: 1px solid var(--saudi-green);
            color: var(--saudi-green);
            cursor: default;
        }

        .btn-completed:hover {
            transform: none;
            box-shadow: none;
        }

        .btn-disabled {
            background: #334155;
            cursor: not-allowed;
            color: #94a3b8;
            pointer-events: none;
        }

        body.light-mode .btn-disabled {
            background: #cbd5e1;
            color: #64748b;
        }

        .hidden-card {
            display: none;
        }

        .card-item.show-enter {
            animation: fadeIn 0.6s ease forwards;
            display: flex;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-more {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text-muted);
            padding: 8px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 13px;
            margin: 20px auto 0;
            display: block;
            transition: 0.3s;
        }

        .btn-more:hover {
            border-color: var(--gold-light);
            color: var(--gold-light);
        }

        body.light-mode .btn-more {
            border-color: #cbd5e1;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }

            .page-header h1 {
                font-size: 32px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="../../../index.php" class="logo"><i class='bx bxl-flutter' style="color:var(--saudi-green)"></i> <span
                id="nav-logo">تراث المملكة</span></a>
        <div class="nav-actions">
            <a href="../../../index.php" class="icon-btn" title="Home"><i class='bx bx-home-alt'></i></a>
            <a href="user_page.php" class="icon-btn" title="Profile"><i class='bx bx-user'></i></a>
            <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
            <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
            <a href="../../auth/logout.php" class="icon-btn" style="color:#ef4444; border-color:rgba(239,68,68,0.3);"><i
                    class='bx bx-log-out'></i></a>
        </div>
    </nav>

    <div class="container">

        <header class="page-header">
            <h1 id="header-title">خريطة <span>الثقافة</span></h1>
            <p id="header-desc">استكشف التحديات حسب المنطقة. المقاعد محدودة للأبطال الذين ينهون التحديات للنهاية!</p>
        </header>

        <?php foreach ($display_order as $region_key):
            if (empty($cards_by_region[$region_key]))
                continue;

            $info = $regions_info[$region_key];
            $total_region_cards = count($cards_by_region[$region_key]);
            $limit = 4;
            ?>

            <section class="region-section">
                <div class="region-header">
                    <div class="region-icon"><i class='bx <?= $info['icon'] ?>'></i></div>
                    <div class="region-info">
                        <h2 class="region-title-txt" data-key="region_<?= $region_key ?>"></h2>
                        <p class="region-desc-txt" data-key="desc_<?= $region_key ?>"></p>
                    </div>
                </div>

                <div class="cards-grid" id="grid-<?= $region_key ?>">
                    <?php
                    $counter = 0;
                    foreach ($cards_by_region[$region_key] as $c):
                        $counter++;
                        $card_id = $c['id'];
                        $total_q = $cards_total_questions[$card_id] ?? 20;
                        $my_solved = $c['my_solved'];
                        $my_percent = ($total_q > 0) ? round(($my_solved / $total_q) * 100) : 0;
                        $is_done = ($my_solved >= $total_q && $total_q > 0);
                        $max_users = (int) $c['number_of_users'];
                        $completed_users = $completed_users_per_card[$card_id] ?? 0;
                        $is_full = ($max_users > 0 && $completed_users >= $max_users);

                        if ($max_users > 0) {
                            $seats_text = "<span class='seats-num'>$completed_users</span> / <span class='seats-num'>$max_users</span> <span class='lbl-completed'>مكتمل</span>";
                            $seats_class = $is_full ? "seats-full" : "seats-open";
                            $seats_icon = $is_full ? "bx-lock-alt" : "bx-lock-open-alt";
                        } else {
                            $seats_text = "<span class='seats-num'>$completed_users</span> (<span class='lbl-open'>مفتوح</span>)";
                            $seats_class = "seats-open";
                            $seats_icon = "bx-globe";
                        }

                        $type_names_key = [1 => 'lbl_type_words', 2 => 'lbl_type_phrases', 3 => 'lbl_type_proverbs', 4 => 'lbl_type_mixed'];
                        $type_key = $type_names_key[$c['card_type']] ?? 'lbl_type_mixed';

                        $class_hidden = ($counter > $limit) ? 'hidden-card' : '';
                        ?>
                        <div class="card-item <?= $class_hidden ?>">
                            <div class="card-body">
                                <div class="card-top">
                                    <div class="card-title"><?= htmlspecialchars($c['card_name']) ?></div>
                                    <span class="type-badge lbl-dynamic" data-key="<?= $type_key ?>"></span>
                                </div>

                                <div class="seats-info <?= $seats_class ?>">
                                    <span><i class='bx <?= $seats_icon ?>'></i> <span class="lbl-seats">المقاعد</span></span>
                                    <span><?= $seats_text ?></span>
                                </div>

                                <div class="progress-box">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $my_percent ?>%"></div>
                                    </div>
                                    <div class="progress-text">
                                        <span><?= $my_percent ?>% <span class="lbl-progress">إنجازك</span></span>
                                    </div>
                                </div>

                                <?php if ($is_done): ?>
                                    <a href="#" class="btn-start btn-completed"><i class='bx bx-check-circle'></i> <span
                                            class="btn-done">أنهيت التحدي</span></a>
                                <?php elseif ($is_full): ?>
                                    <div class="btn-start btn-disabled">
                                        <i class='bx bx-block'></i>
                                        <?php if ($my_solved > 0): ?>
                                            <span class="btn-full-sorry">عذراً، اكتمل العدد</span>
                                        <?php else: ?>
                                            <span class="btn-full">مكتمل العدد</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <a href="answer_card.php?id=<?= $c['id'] ?>" class="btn-start">
                                        <?php if ($my_solved > 0): ?>
                                            <span class="btn-continue">متابعة الحل</span>
                                        <?php else: ?>
                                            <span class="btn-start-txt">دخول التحدي</span>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_region_cards > $limit): ?>
                    <button class="btn-more" onclick="toggleCards('grid-<?= $region_key ?>', this)">
                        <span class="btn-show-more">عرض المزيد</span> <i class='bx bx-chevron-down'></i>
                    </button>
                <?php endif; ?>

            </section>
        <?php endforeach; ?>

    </div>

    <script>
        const txt = {
            ar: {
                navLogo: "تراث المملكة", pageTitle: "مناطق المملكة | تحديات",
                headerTitle: "خريطة <span>الثقافة</span>",
                headerDesc: "استكشف التحديات حسب المنطقة. المقاعد محدودة للأبطال الذين ينهون التحديات للنهاية!",

                // Regions
                region_Central: "المنطقة الوسطى (نجد)", desc_Central: "قلب المملكة النابض.",
                region_Western: "المنطقة الغربية (الحجاز)", desc_Western: "بوابة الحرمين الشريفين.",
                region_Southern: "المنطقة الجنوبية", desc_Southern: "أرض الجبال والضباب.",
                region_Eastern: "المنطقة الشرقية", desc_Eastern: "لؤلؤة الخليج.",
                region_Northern: "المنطقة الشمالية", desc_Northern: "بوابة الشمال.",
                region_all: "تحديات شاملة", desc_all: "مختارات من كافة المناطق.",
                region_General: "مصطلحات عامة", desc_General: "كلمات شائعة.",

                // Card Labels
                lbl_type_words: "كلمات", lbl_type_phrases: "عبارات", lbl_type_proverbs: "أمثال", lbl_type_mixed: "مختلط",
                lblSeats: "المقاعد", lblCompleted: "مكتمل", lblOpen: "مفتوح", lblProgress: "إنجازك",

                // Buttons
                btnDone: "أنهيت التحدي", btnFullSorry: "عذراً، اكتمل العدد", btnFull: "مكتمل العدد",
                btnContinue: "متابعة الحل", btnStartTxt: "دخول التحدي",
                btnShowMore: "عرض المزيد", btnShowLess: "عرض أقل"
            },
            en: {
                navLogo: "Torath Platform", pageTitle: "Kingdom Regions | Challenges",
                headerTitle: "Culture <span>Map</span>",
                headerDesc: "Explore challenges by region. Seats are limited for champions who finish the challenges!",

                // Regions
                region_Central: "Central Region (Najd)", desc_Central: "The beating heart of the Kingdom.",
                region_Western: "Western Region (Hejaz)", desc_Western: "Gateway to the Two Holy Mosques.",
                region_Southern: "Southern Region", desc_Southern: "Land of mountains and mist.",
                region_Eastern: "Eastern Region", desc_Eastern: "Pearl of the Gulf.",
                region_Northern: "Northern Region", desc_Northern: "Gateway to the North.",
                region_all: "Comprehensive Challenges", desc_all: "Selections from all regions.",
                region_General: "General Terms", desc_General: "Common words.",

                // Card Labels
                lbl_type_words: "Words", lbl_type_phrases: "Phrases", lbl_type_proverbs: "Proverbs", lbl_type_mixed: "Mixed",
                lblSeats: "Seats", lblCompleted: "Completed", lblOpen: "Open", lblProgress: "Progress",

                // Buttons
                btnDone: "Challenge Completed", btnFullSorry: "Sorry, Full", btnFull: "Full",
                btnContinue: "Continue", btnStartTxt: "Start Challenge",
                btnShowMore: "Show More", btnShowLess: "Show Less"
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

            // Static IDs
            document.getElementById('page-title').innerText = t.pageTitle;
            document.getElementById('nav-logo').innerText = t.navLogo;
            document.getElementById('header-title').innerHTML = t.headerTitle;
            document.getElementById('header-desc').innerText = t.headerDesc;

            // Region Titles & Descs (using data-key)
            document.querySelectorAll('.region-title-txt').forEach(el => el.innerText = t[el.getAttribute('data-key')] || el.getAttribute('data-key'));
            document.querySelectorAll('.region-desc-txt').forEach(el => el.innerText = t[el.getAttribute('data-key')] || el.getAttribute('data-key'));

            // Dynamic Labels (Classes)
            document.querySelectorAll('.lbl-seats').forEach(el => el.innerText = t.lblSeats);
            document.querySelectorAll('.lbl-completed').forEach(el => el.innerText = t.lblCompleted);
            document.querySelectorAll('.lbl-open').forEach(el => el.innerText = t.lblOpen);
            document.querySelectorAll('.lbl-progress').forEach(el => el.innerText = t.lblProgress);

            // Type Badges
            document.querySelectorAll('.lbl-dynamic').forEach(el => el.innerText = t[el.getAttribute('data-key')] || '');

            // Buttons
            document.querySelectorAll('.btn-done').forEach(el => el.innerText = t.btnDone);
            document.querySelectorAll('.btn-full-sorry').forEach(el => el.innerText = t.btnFullSorry);
            document.querySelectorAll('.btn-full').forEach(el => el.innerText = t.btnFull);
            document.querySelectorAll('.btn-continue').forEach(el => el.innerText = t.btnContinue);
            document.querySelectorAll('.btn-start-txt').forEach(el => el.innerText = t.btnStartTxt);

            // Reset Show More buttons text based on their current state (collapsed)
            document.querySelectorAll('.btn-more').forEach(btn => {
                const isExpanded = btn.innerHTML.includes('bx-chevron-up');
                const span = btn.querySelector('.btn-show-more, span'); // Find the text span
                if (span) {
                    span.innerText = isExpanded ? t.btnShowLess : t.btnShowMore;
                }
            });
        }

        function toggleCards(gridId, btn) {
            const grid = document.getElementById(gridId);
            const hiddenCards = grid.querySelectorAll('.hidden-card');
            const lang = localStorage.getItem('lang') || 'ar';
            const t = txt[lang];

            if (hiddenCards.length > 0) {
                // Expand
                hiddenCards.forEach(card => {
                    card.classList.remove('hidden-card');
                    card.classList.add('show-enter');
                });
                btn.innerHTML = `<span class="btn-show-more">${t.btnShowLess}</span> <i class='bx bx-chevron-up'></i>`;
            } else {
                // Collapse
                const allCards = grid.querySelectorAll('.card-item');
                allCards.forEach((card, index) => {
                    if (index >= 4) {
                        card.classList.add('hidden-card');
                        card.classList.remove('show-enter');
                    }
                });
                btn.innerHTML = `<span class="btn-show-more">${t.btnShowMore}</span> <i class='bx bx-chevron-down'></i>`;
            }
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