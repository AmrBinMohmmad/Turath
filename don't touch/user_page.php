<?php
require_once 'auth_guard.php';
checkUser();

require_once 'db.php';

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
$if0_40458841_projects_count = $conn->query("SELECT COUNT(DISTINCT project_id) FROM if0_40458841_projects.annotations WHERE user_id = $user_id")->fetch_row()[0] ?? 0;

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

// 4. جلب السجل (مع تفاصيل المقاعد والتقدم)
$sql_history = "
    SELECT 
        c.id, c.card_name, c.img, c.number_of_users, c.number_of_question,
        (SELECT COUNT(*) FROM if0_40458841_projects.annotations ann WHERE ann.project_id = c.id AND ann.user_id = $user_id) as my_solved,
        (SELECT COUNT(DISTINCT user_id) FROM if0_40458841_projects.annotations ann WHERE ann.project_id = c.id) as current_users
    FROM if0_40458841_projects.cards c
    JOIN if0_40458841_projects.annotations a ON c.id = a.project_id
    WHERE a.user_id = $user_id
    GROUP BY c.id
    ORDER BY c.id DESC
    LIMIT 5
";
$history = $conn->query($sql_history);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي | <?= htmlspecialchars($user['username']) ?></title>

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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
            --accent-blue: #3b82f6;
            --accent-red: #ef4444;
        }

        body.light-mode {
            --bg-main: #fcfbf9;
            --bg-secondary: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #475569;
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s, color 0.3s;
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

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            color: #000;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        /* Container */
        .container {
            max-width: 1100px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }

        /* Profile Header */
        .profile-card {
            background: radial-gradient(circle at 10% 10%, rgba(0, 108, 53, 0.15), var(--bg-secondary));
            border-radius: 25px;
            padding: 40px;
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
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
            border: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 20px rgba(198, 147, 32, 0.4);
        }

        .user-info {
            flex-grow: 1;
            z-index: 2;
        }

        .user-info h1 {
            margin: 0 0 5px;
            font-size: 32px;
        }

        .user-info p {
            color: var(--text-muted);
            margin: 0 0 15px;
            font-size: 16px;
        }

        .user-title {
            background: rgba(0, 108, 53, 0.2);
            color: var(--saudi-green);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid rgba(0, 108, 53, 0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            transition: 0.3s;
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

        /* Content Area */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 30px;
            height: 100%;
        }

        .panel h3 {
            margin: 0 0 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--glass-border);
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel h3 i {
            color: var(--saudi-green);
        }

        /* --- History List Styles --- */
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
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .h-item:hover {
            background: rgba(255, 255, 255, 0.04);
            transform: translateX(-5px);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .h-img {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            background-size: cover;
            background-position: center;
            margin-left: 20px;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
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

        /* Stats inside card */
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
            color: var(--gold-light);
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            max-width: 300px;
        }

        .p-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 4px;
        }

        .p-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold-dark), var(--gold-light));
            border-radius: 10px;
        }

        .p-text {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Seat Status Badge */
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
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .st-full {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* --- الزر العصري الجديد (Modern Action Button) --- */
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

        .action-btn-modern i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .action-btn-modern:hover i {
            transform: translateX(-3px);
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

            .h-img {
                margin: 0 0 15px 0;
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
        <a href="index.php" class="logo"><i class='bx bxl-flutter' style="color:var(--saudi-green)"></i> تراث
            المملكة</a>
        <div class="nav-actions">
            <a href="regions.php" class="btn-gold" style="margin-left:10px;">التحديات</a>
            <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
            <a href="logout.php" class="icon-btn" style="color:#ef4444; border-color:rgba(239,68,68,0.3);"><i
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
            <div><a href="regions.php" class="btn-gold"><i class='bx bx-play'></i> التحديات</a></div>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <i class='bx bxs-zap stat-icon'></i>
                <span class="stat-val"><?= number_format($xp) ?></span>
                <span class="stat-lbl">نقاط الخبرة (XP)</span>
            </div>
            <div class="stat-box">
                <i class='bx bxs-trophy stat-icon' style="color:#ef4444"></i>
                <span class="stat-val">#<?= $rank ?></span>
                <span class="stat-lbl">الترتيب العالمي</span>
            </div>
            <div class="stat-box">
                <i class='bx bxs-check-circle stat-icon' style="color:var(--saudi-green)"></i>
                <span class="stat-val"><?= $total_ans ?></span>
                <span class="stat-lbl">إجابة صحيحة</span>
            </div>
            <div class="stat-box">
                <i class='bx bxs-collection stat-icon' style="color:#3b82f6"></i>
                <span class="stat-val"><?= $if0_40458841_projects_count ?></span>
                <span class="stat-lbl">تحدي مشارك</span>
            </div>
        </div>

        <div class="content-grid">
            <div class="panel">
                <h3><i class='bx bxs-bar-chart-alt-2'></i> مسار التقدم</h3>
                <div style="height: 250px; display:flex; justify-content:center; align-items:center;">
                    <div id="levelChart"></div>
                </div>
                <div style="text-align:center; color:var(--text-muted); font-size:14px;">
                    تبقى <strong><?= 1000 - ($xp % 1000) ?></strong> نقطة للمستوى <?= $level + 1 ?>
                </div>
            </div>

            <div class="panel">
                <h3><i class='bx bxs-time-five'></i> تحدياتك الأخيرة</h3>
                <div class="history-list">
                    <?php if ($history && $history->num_rows > 0): ?>
                        <?php while ($h = $history->fetch_assoc()):
                            // 1. حساب التقدم في الأسئلة
                            $q_count = $h['number_of_question'] ?? 20;
                            $my_solved = $h['my_solved'];
                            $prog_percent = ($q_count > 0) ? round(($my_solved / $q_count) * 100) : 0;

                            // 2. حساب المقاعد
                            $max_u = $h['number_of_users'];
                            $curr_u = $h['current_users'];

                            // نص الحالة والبادج
                            if ($max_u > 0) {
                                $is_full = ($curr_u >= $max_u);
                                $seats_text = "المقاعد: $curr_u من $max_u";
                                $badge_class = $is_full ? "st-full" : "st-open";
                                $badge_icon = $is_full ? "bx-lock-alt" : "bx-lock-open-alt";
                            } else {
                                $seats_text = "المقاعد: $curr_u (مفتوح)";
                                $badge_class = "st-open";
                                $badge_icon = "bx-globe";
                            }
                            ?>
                            <div class="h-item">
                                <div style="display:flex; align-items:center; width:100%;">
                                    <div class="h-img"
                                        style="background-image: url('<?= !empty($h['img']) ? htmlspecialchars($h['img']) : 'img/default.png' ?>');">
                                    </div>

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
                                                <?= $q_count ?> سؤال</div>
                                            <div class="detail-point"><i class='bx bx-line-chart'></i> <?= $prog_percent ?>%
                                                إنجاز</div>
                                        </div>

                                        <div class="progress-container">
                                            <div class="p-bar">
                                                <div class="p-fill" style="width:<?= $prog_percent ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <a href="answer_card.php?id=<?= $h['id'] ?>" class="action-btn-modern">
                                    <span>استكمال</span>
                                    <i class='bx bx-play-circle'></i>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding:40px; color:var(--text-muted);">
                            <i class='bx bx-ghost' style="font-size:40px; margin-bottom:10px;"></i>
                            <p>لا يوجد نشاط حديث.</p>
                            <a href="regions.php" style="color:var(--gold-light);">استكشف التحديات</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

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

        var options = {
            series: [<?= $current_level_progress ?>],
            chart: { height: 280, type: 'radialBar', background: 'transparent' },
            plotOptions: {
                radialBar: {
                    hollow: { size: '65%' },
                    track: { background: 'rgba(255,255,255,0.05)' },
                    dataLabels: {
                        name: { show: false },
                        value: { fontSize: '32px', fontWeight: 'bold', color: 'var(--text-main)', formatter: function (val) { return val + "%"; } }
                    }
                }
            },
            colors: ['#F2D06B'],
            stroke: { lineCap: 'round' },
            labels: ['Progress'],
        };
        new ApexCharts(document.querySelector("#levelChart"), options).render();
    </script>

</body>

</html>