<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once __DIR__ . '/../../auth/auth_guard.php';
checkUser();
require_once __DIR__ . '/../db.php';

// ... (نفس كود PHP الخاص بجلب البيانات دون تغيير) ...
// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
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
    header("Location: ../login.php");
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
    <style>
        /* ... نفس أكواد CSS الموجودة في ملفك الأصلي ... */
        :root {
            --bg-main: #020617;
            --bg-secondary: #0f172a;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.05);
            --item-bg: rgba(255, 255, 255, 0.02);
            --item-hover: rgba(255, 255, 255, 0.04);
            --progress-track: rgba(255, 255, 255, 0.1);
            --saudi-green: #006C35;
            --gold-light: #F2D06B;
            --gold-dark: #C69320;
            --accent-blue: #3b82f6;
            --accent-red: #ef4444;
            --shadow-soft: none;
        }

        body.light-mode {
            --bg-main: #f8fafc;
            --bg-secondary: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --item-bg: #f1f5f9;
            --item-hover: #e2e8f0;
            --progress-track: #cbd5e1;
            --shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s, box-shadow 0.3s;
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
            background-color: var(--bg-main);
            opacity: 0.97;
            border-bottom: 1px solid var(--card-border);
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
            border: 1px solid var(--card-border);
            background: var(--card-bg);
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

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            color: #000;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        /* باقي الستايلات كما هي في كودك الأصلي تماماً لضمان الشكل */
        .container {
            max-width: 1100px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }

        .profile-card {
            background: var(--bg-secondary);
            background-image: radial-gradient(circle at 10% 10%, rgba(0, 108, 53, 0.05), transparent);
            border-radius: 25px;
            padding: 40px;
            border: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .profile-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.transparenttextures.com/patterns/arabesque.png');
            opacity: 0.05;
            pointer-events: none;
        }

        body.light-mode .profile-card::after {
            opacity: 0.15;
            filter: invert(1);
        }

        .avatar {
            width: 110px;
            height: 110px;
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 900;
            color: #000;
            border: 4px solid var(--bg-secondary);
            box-shadow: 0 0 20px rgba(198, 147, 32, 0.4);
            z-index: 2;
        }

        .user-info {
            flex-grow: 1;
            z-index: 2;
        }

        .user-info h1 {
            margin: 0 0 5px;
            font-size: 32px;
            color: var(--text-main);
        }

        .user-info p {
            color: var(--text-muted);
            margin: 0 0 15px;
            font-size: 16px;
        }

        .user-title {
            background: rgba(0, 108, 53, 0.1);
            color: var(--saudi-green);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid rgba(0, 108, 53, 0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            transition: 0.3s;
            box-shadow: var(--shadow-soft);
        }

        .stat-box:hover {
            transform: translateY(-5px);
            border-color: var(--gold-light);
        }

        .stat-val {
            display: block;
            font-size: 28px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .stat-lbl {
            color: var(--text-muted);
            font-size: 13px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .panel {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 25px;
            padding: 30px;
            height: 100%;
            box-shadow: var(--shadow-soft);
        }

        .panel h3 {
            margin: 0 0 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--card-border);
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-main);
        }

        .panel h3 i {
            color: var(--saudi-green);
        }

        .history-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .h-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            border-radius: 18px;
            background: var(--item-bg);
            border: 1px solid var(--card-border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .h-item:hover {
            background: var(--item-hover);
            transform: translateX(-5px);
        }

        .h-info {
            flex-grow: 1;
        }

        .h-info h4 {
            margin: 0 0 8px;
            font-size: 18px;
            color: var(--text-main);
            font-weight: 700;
        }

        .h-details {
            display: flex;
            gap: 20px;
            margin-bottom: 12px;
            font-size: 13px;
            color: var(--text-muted);
            flex-wrap: wrap;
        }

        .detail-point {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .detail-point i {
            font-size: 16px;
            color: var(--gold-dark);
        }

        body.light-mode .detail-point i {
            color: #d97706;
        }

        .progress-container {
            width: 100%;
            max-width: 300px;
        }

        .p-bar {
            height: 8px;
            background: var(--progress-track);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 4px;
        }

        .p-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold-dark), var(--gold-light));
            border-radius: 10px;
        }

        .status-badge {
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .st-open {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        body.light-mode .st-open {
            color: #059669;
            background: #d1fae5;
            border-color: #a7f3d0;
        }

        .st-full {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        body.light-mode .st-full {
            color: #dc2626;
            background: #fee2e2;
            border-color: #fecaca;
        }

        .action-btn-modern {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 24px;
            background: linear-gradient(135deg, #F2D06B 0%, #C69320 100%);
            color: #020617;
            font-weight: 800;
            font-size: 14px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(198, 147, 32, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }

        .action-btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: 0.5s;
        }

        .action-btn-modern:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(198, 147, 32, 0.5);
            color: #000;
        }

        .action-btn-modern:hover::before {
            left: 100%;
        }

        .action-btn-modern.disabled {
            background: #334155;
            color: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
            border-color: #475569;
        }

        body.light-mode .action-btn-modern.disabled {
            background: #cbd5e1;
            color: #64748b;
            border-color: #94a3b8;
        }

        .action-btn-modern.completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            cursor: default;
            box-shadow: none;
            border-color: #059669;
        }

        @media (max-width: 900px) {
            .profile-card {
                flex-direction: column;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .h-item {
                flex-direction: column;
                text-align: center;
                padding: 25px;
            }

            .h-details {
                justify-content: center;
            }

            .progress-container {
                margin: 0 auto;
            }

            .action-btn-modern {
                width: 100%;
                margin-top: 20px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="../../../index.php" class="logo"><i class='bx bxl-flutter' style="color:var(--saudi-green)"></i> <span
                id="nav-logo">تراث المملكة</span></a>
        <div class="nav-actions">
            <a href="regions.php" class="btn-gold" style="margin-left:10px;" id="nav-challenges">التحديات</a>
            <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
            <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
            <a href="../../auth/logout.php" class="icon-btn" style="color:#ef4444; border-color:rgba(239,68,68,0.3);"><i
                    class='bx bx-log-out'></i></a>
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
                navLogo: "تراث المملكة", navChallenges: "التحديات",
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