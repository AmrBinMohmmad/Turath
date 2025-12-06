<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once 'auth_guard.php';
checkUser();
require_once 'db.php';

// ... (نفس كود PHP الخاص بجلب البيانات دون تغيير) ...
// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// 1. جلب بيانات المستخدم
$stmt = $conn->prepare("SELECT * FROM if0_40458841_users_db.users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// 2. الإحصائيات
$xp = $user['xp'] ?? 0;
$level = $user['level'] ?? 1;
$current_level_progress = ($xp % 1000) / 10;
$rank = $conn->query("SELECT COUNT(*) + 1 FROM if0_40458841_users_db.users WHERE xp > $xp")->fetch_row()[0] ?? '-';
$total_ans = $conn->query("SELECT COUNT(*) FROM if0_40458841_projects.annotations WHERE user_id = $user_id")->fetch_row()[0] ?? 0;
$projects_count = $conn->query("SELECT COUNT(DISTINCT project_id) FROM if0_40458841_projects.annotations WHERE user_id = $user_id")->fetch_row()[0] ?? 0;

// 3. الأوسمة
$badge_title = "مبتدئ تراثي";
$badge_icon = "bx-star";
if ($level >= 5) {
    $badge_title = "مستكشف الصحراء";
    $badge_icon = "bx-compass";
}
if ($level >= 10) {
    $badge_title = "حكيم القبيلة";
    $badge_icon = "bx-book-bookmark";
}
if ($level >= 20) {
    $badge_title = "أمير البيان";
    $badge_icon = "bx-crown";
}

// 4. جلب السجل
$sql_history = "SELECT c.id, c.card_name, c.img, c.number_of_users, c.number_of_question, 
        (SELECT COUNT(*) FROM if0_40458841_projects.annotations ann WHERE ann.project_id = c.id AND ann.user_id = $user_id) as my_solved,
        (SELECT COUNT(DISTINCT a2.user_id) FROM if0_40458841_projects.annotations a2 WHERE a2.project_id = c.id AND (SELECT COUNT(*) FROM if0_40458841_projects.annotations a3 WHERE a3.project_id = c.id AND a3.user_id = a2.user_id) >= c.number_of_question) as finished_users_count
    FROM if0_40458841_projects.cards c JOIN if0_40458841_projects.annotations a ON c.id = a.project_id WHERE a.user_id = $user_id GROUP BY c.id ORDER BY c.id DESC LIMIT 5";
$history = $conn->query($sql_history);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title">الملف الشخصي | <?= htmlspecialchars($user['username']) ?></title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/user_page.css">
    <link rel="icon" type="image/png" href="Favicon.png" />
    
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="logo">
            <img src="Favicon.png" alt="Logo" style="height:40px; margin-right:8px; ">
            <span id="nav-logo">منصة تراث</span>
        </a>
        <div class="nav-actions">
            <a href="regions.php" class="btn-gold" style="margin-left:10px;" id="nav-challenges">التحديات</a>
            <a href="logout.php" class="icon-btn" style="color:#ef4444; border-color:rgba(239,68,68,0.3);"><i
                    class='bx bx-log-out'></i></a>
            <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
            <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
            
        </div>
    </nav>

    <div class="container">
        <div class="profile-card">
            <div class="avatar-area">
                <div class="avatar"><?= strtoupper(mb_substr($user['username'], 0, 1)) ?></div>
            </div>
            <div class="user-info">
                <h1><?= htmlspecialchars($user['username']) ?></h1>
                <p><i class='bx bx-envelope'></i> <?= htmlspecialchars($user['email']) ?></p>
                <div class="user-title"><i class='bx <?= $badge_icon ?>'></i> <?= $badge_title ?></div>
            </div>
            <div><a href="regions.php" class="btn-gold"><i class='bx bx-play'></i> <span
                        id="btn-challenges">التحديات</span></a></div>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <i class='bx bxs-zap stat-icon'
                    style="font-size: 24px; color: var(--gold-light); margin-bottom:10px;"></i>
                <span class="stat-val"><?= number_format($xp) ?></span>
                <span class="stat-lbl" id="lbl-xp">نقاط الخبرة (XP)</span>
            </div>
            <div class="stat-box">
                <i class='bx bxs-trophy stat-icon' style="font-size: 24px; color:#ef4444; margin-bottom:10px;"></i>
                <span class="stat-val">#<?= $rank ?></span>
                <span class="stat-lbl" id="lbl-rank">الترتيب العالمي</span>
            </div>
            <div class="stat-box">
                <i class='bx bxs-check-circle stat-icon'
                    style="font-size: 24px; color:var(--saudi-green); margin-bottom:10px;"></i>
                <span class="stat-val"><?= $total_ans ?></span>
                <span class="stat-lbl" id="lbl-ans">إجابة صحيحة</span>
            </div>
            <div class="stat-box">
                <i class='bx bxs-collection stat-icon' style="font-size: 24px; color:#3b82f6; margin-bottom:10px;"></i>
                <span class="stat-val"><?= $projects_count ?></span>
                <span class="stat-lbl" id="lbl-proj">تحدي مشارك</span>
            </div>
        </div>

        <div class="content-grid">
            <div class="panel">
                <h3 id="panel-progress"><i class='bx bxs-bar-chart-alt-2'></i> مسار التقدم</h3>
                <div style="height: 250px; display:flex; justify-content:center; align-items:center;">
                    <div id="levelChart"></div>
                </div>
                <div style="text-align:center; color:var(--text-muted); font-size:14px;">
                    <span id="txt-remaining">تبقى</span> <strong><?= 1000 - ($xp % 1000) ?></strong> <span
                        id="txt-points">نقطة للمستوى</span> <?= $level + 1 ?>
                </div>
            </div>

            <div class="panel">
                <h3 id="panel-recent"><i class='bx bxs-time-five'></i> تحدياتك الأخيرة</h3>
                <div class="history-list">
                    <?php if ($history && $history->num_rows > 0): ?>
                        <?php while ($h = $history->fetch_assoc()):
                            $q_count = $h['number_of_question'] ?? 20;
                            $my_solved = $h['my_solved'];
                            $prog_percent = ($q_count > 0) ? round(($my_solved / $q_count) * 100) : 0;
                            $i_am_finished = ($my_solved >= $q_count);
                            $max_u = $h['number_of_users'];
                            $finished_users = $h['finished_users_count'];
                            $seats_left = $max_u - $finished_users;
                            if ($seats_left < 0)
                                $seats_left = 0;

                            if ($max_u > 0) {
                                $is_full = ($seats_left <= 0);
                                $seats_text = "$finished_users / $max_u";
                                $badge_class = $is_full ? "st-full" : "st-open";
                                $badge_icon = $is_full ? "bx-lock-alt" : "bx-lock-open-alt";
                            } else {
                                $seats_text = "مفتوح";
                                $is_full = false;
                                $badge_class = "st-open";
                                $badge_icon = "bx-globe";
                            }

                            $btn_href = "javascript:void(0)";
                            $onclick = "";
                            $btn_text_key = ""; // Key for translation
                    
                            if ($i_am_finished) {
                                $btn_text = "أنهيت التحدي";
                                $btn_text_key = "btn_finished";
                                $btn_icon_html = "<i class='bx bx-check'></i>";
                                $extra_class = "completed";
                            } elseif ($is_full) {
                                $btn_text = "انتهت المقاعد";
                                $btn_text_key = "btn_full";
                                $btn_icon_html = "<i class='bx bx-x-circle'></i>";
                                $extra_class = "disabled";
                                $onclick = "onclick=\"alert('Sorry, seats are full.')\"";
                            } else {
                                $btn_text = "استكمال";
                                $btn_text_key = "btn_continue";
                                $btn_icon_html = "<i class='bx bx-play-circle'></i>";
                                $btn_href = "answer_card.php?id=" . $h['id'];
                                $extra_class = "";
                            }
                            ?>
                            <div class="h-item">
                                <div style="display:flex; align-items:center; width:100%;">
                                    <div class="h-info">
                                        <div
                                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                                            <h4><?= htmlspecialchars($h['card_name']) ?></h4>
                                            <span class="status-badge <?= $badge_class ?>">
                                                <i class='bx <?= $badge_icon ?>'></i> <?= $seats_text ?>
                                            </span>
                                        </div>
                                        <div class="h-details">
                                            <div class="detail-point"><i class='bx bx-help-circle'></i> <?= $my_solved ?> /
                                                <?= $q_count ?> <span class="txt-q">سؤال</span></div>
                                            <div class="detail-point"><i class='bx bx-line-chart'></i> <?= $prog_percent ?>%
                                                <span class="txt-achieve">إنجاز</span></div>
                                        </div>
                                        <div class="progress-container">
                                            <div class="p-bar">
                                                <div class="p-fill" style="width:<?= $prog_percent ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a href="<?= $btn_href ?>" <?= $onclick ?> class="action-btn-modern <?= $extra_class ?>">
                                    <span class="dynamic-btn-text" data-key="<?= $btn_text_key ?>"><?= $btn_text ?></span>
                                    <?= $btn_icon_html ?>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding:40px; color:var(--text-muted);">
                            <i class='bx bx-ghost' style="font-size:40px; margin-bottom:10px;"></i>
                            <p id="no-activity">لا يوجد نشاط حديث.</p>
                            <a href="regions.php" style="color:var(--gold-light);" id="explore-link">استكشف التحديات</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- Shared Logic for all pages ---
        const txt = {
            ar: {
                navLogo: "منصة تراث", navChallenges: "التحديات",
                pageTitle: "الملف الشخصي",
                btnChallenges: "التحديات",
                lblXp: "نقاط الخبرة (XP)", lblRank: "الترتيب العالمي", lblAns: "إجابة صحيحة", lblProj: "تحدي مشارك",
                panelProgress: '<i class="bx bxs-bar-chart-alt-2"></i> مسار التقدم',
                txtRemaining: "تبقى", txtPoints: "نقطة للمستوى",
                panelRecent: '<i class="bx bxs-time-five"></i> تحدياتك الأخيرة',
                txtQ: "سؤال", txtAchieve: "إنجاز",
                btn_finished: "أنهيت التحدي", btn_full: "انتهت المقاعد", btn_continue: "استكمال",
                noActivity: "لا يوجد نشاط حديث.", exploreLink: "استكشف التحديات"
            },
            en: {
                navLogo: "Torath Platform", navChallenges: "Challenges",
                pageTitle: "User Profile",
                btnChallenges: "Challenges",
                lblXp: "XP Points", lblRank: "Global Rank", lblAns: "Correct Answers", lblProj: "Participated",
                panelProgress: '<i class="bx bxs-bar-chart-alt-2"></i> Progress Path',
                txtRemaining: "Remaining", txtPoints: "points to Level",
                panelRecent: '<i class="bx bxs-time-five"></i> Recent Activities',
                txtQ: "Questions", txtAchieve: "Done",
                btn_finished: "Completed", btn_full: "Seats Full", btn_continue: "Continue",
                noActivity: "No recent activity.", exploreLink: "Explore Challenges"
            }
        };

        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            const isLight = document.body.classList.contains('light-mode');
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            updateThemeIcon();
            updateChartColor(isLight ? '#1e293b' : '#f8fafc');
        }

        function updateThemeIcon() {
            const icon = document.getElementById('theme-icon');
            if (document.body.classList.contains('light-mode')) {
                icon.classList.replace('bx-sun', 'bx-moon');
            } else {
                icon.classList.replace('bx-moon', 'bx-sun');
            }
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
            // Update simple IDs
            if (document.getElementById('nav-logo')) document.getElementById('nav-logo').innerText = t.navLogo;
            if (document.getElementById('nav-challenges')) document.getElementById('nav-challenges').innerText = t.navChallenges;
            if (document.getElementById('btn-challenges')) document.getElementById('btn-challenges').innerText = t.btnChallenges;
            if (document.getElementById('lbl-xp')) document.getElementById('lbl-xp').innerText = t.lblXp;
            if (document.getElementById('lbl-rank')) document.getElementById('lbl-rank').innerText = t.lblRank;
            if (document.getElementById('lbl-ans')) document.getElementById('lbl-ans').innerText = t.lblAns;
            if (document.getElementById('lbl-proj')) document.getElementById('lbl-proj').innerText = t.lblProj;
            if (document.getElementById('panel-progress')) document.getElementById('panel-progress').innerHTML = t.panelProgress;
            if (document.getElementById('txt-remaining')) document.getElementById('txt-remaining').innerText = t.txtRemaining;
            if (document.getElementById('txt-points')) document.getElementById('txt-points').innerText = t.txtPoints;
            if (document.getElementById('panel-recent')) document.getElementById('panel-recent').innerHTML = t.panelRecent;
            if (document.getElementById('no-activity')) document.getElementById('no-activity').innerText = t.noActivity;
            if (document.getElementById('explore-link')) document.getElementById('explore-link').innerText = t.exploreLink;

            // Update Class based elements
            document.querySelectorAll('.txt-q').forEach(el => el.innerText = t.txtQ);
            document.querySelectorAll('.txt-achieve').forEach(el => el.innerText = t.txtAchieve);
            document.querySelectorAll('.dynamic-btn-text').forEach(el => {
                const key = el.getAttribute('data-key');
                if (t[key]) el.innerText = t[key];
            });
        }

        // Chart Logic (Same as before but wrapped)
        var chart;
        function initChart(textColor) {
            var options = {
                series: [<?= $current_level_progress ?>],
                chart: { height: 280, type: 'radialBar', background: 'transparent' },
                plotOptions: {
                    radialBar: {
                        hollow: { size: '65%' },
                        track: { background: 'var(--progress-track)' },
                        dataLabels: {
                            name: { show: false },
                            value: { fontSize: '32px', fontWeight: 'bold', color: textColor, formatter: function (val) { return val + "%"; } }
                        }
                    }
                },
                colors: ['#F2D06B'], stroke: { lineCap: 'round' }, labels: ['Progress'],
            };
            if (chart) chart.destroy();
            chart = new ApexCharts(document.querySelector("#levelChart"), options);
            chart.render();
        }

        function updateChartColor(color) { initChart(color); }

        // Initial Load
        const storedTheme = localStorage.getItem('theme') || 'dark';
        if (storedTheme === 'light') { document.body.classList.add('light-mode'); }
        updateThemeIcon();

        const storedLang = localStorage.getItem('lang') || 'ar';
        applyLanguage(storedLang);

        // Init Chart with correct color
        if (storedTheme === 'light') initChart('#1e293b');
        else initChart('#f8fafc');

    </script>
</body>

</html>
