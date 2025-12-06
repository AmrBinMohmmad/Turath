<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

session_start();
require_once 'auth_guard.php';
checkAdmin();

// --- كود الاتصال المباشر ---
$host = "sql206.infinityfree.com";
$user = "if0_40458841";
$password = "PoweR135";
$database = "if0_40458841_users_db";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

require_once 'FleissKappa.php'; // تأكد من وجود ملف FleissKappa.php

if (!isset($_GET['card_id'])) {
    header("Location: admin_cards_list.php");
    exit();
}
$card_id = intval($_GET['card_id']);

// 1. بيانات الكارد
$stmt_card = $conn->prepare("SELECT * FROM if0_40458841_projects.cards WHERE id = ?");
$stmt_card->bind_param("i", $card_id);
$stmt_card->execute();
$card = $stmt_card->get_result()->fetch_assoc();
if (!$card)
    die("الكارد غير موجود.");

$max_limit = $card['number_of_users'];
$limit_text = ($max_limit > 0) ? "من أصل <strong style='color:var(--accent-blue)'>$max_limit</strong>" : "<span style='color:var(--accent-green)'>(مفتوح)</span>";

// 2. عدد الأسئلة الكلي في هذا الكارد (مهم للفلترة)
$stmt_q = $conn->prepare("SELECT COUNT(*) FROM if0_40458841_projects.cards_questions WHERE card_id = ?");
$stmt_q->bind_param("i", $card_id);
$stmt_q->execute();
$total_q_in_card = $stmt_q->get_result()->fetch_row()[0];

// 3. جلب بيانات الطلاب (الذين أكملوا الكارد فقط)
// نستخدم HAVING لضمان أن الطالب أجاب على عدد أسئلة >= عدد أسئلة الكارد
$sql_students = "
    SELECT u.id as user_id, u.username, u.email, u.level,
    COUNT(a.id) as answered_count, SUM(a.score) as correct_answers, MAX(a.created_at) as last_activity
    FROM if0_40458841_users_db.users u
    JOIN if0_40458841_projects.annotations a ON u.id = a.user_id
    WHERE a.project_id = ? 
    GROUP BY u.id
    HAVING answered_count >= ?
    ORDER BY correct_answers DESC
";
$stmt_std = $conn->prepare($sql_students);
$stmt_std->bind_param("ii", $card_id, $total_q_in_card);
$stmt_std->execute();
$result = $stmt_std->get_result();

// 4. حساب Fleiss' Kappa (للمكتملين فقط)
$sql_kappa = "
    SELECT question_id, answer, COUNT(*) as count
    FROM if0_40458841_projects.annotations
    WHERE project_id = $card_id
    AND user_id IN (
        SELECT user_id FROM if0_40458841_projects.annotations 
        WHERE project_id = $card_id 
        GROUP BY user_id 
        HAVING COUNT(*) >= $total_q_in_card
    )
    GROUP BY question_id, answer
";
$kappa_res = $conn->query($sql_kappa);
$kappa_data = [];
while ($row = $kappa_res->fetch_assoc()) {
    $kappa_data[$row['question_id']][$row['answer']] = $row['count'];
}
$kappa_score = FleissKappa::calculate($kappa_data);
list($kappa_text, $kappa_color) = FleissKappa::interpret($kappa_score);

// --- تجهيز البيانات للعرض والرسوم ---
$students_list = [];
$chart_names = [];
$chart_scores = [];
$pass_count = 0;
$fail_count = 0;
$levels_distribution = [];
$score_ranges = ['Weak (0-49%)' => 0, 'Good (50-79%)' => 0, 'Excellent (80-100%)' => 0];

while ($row = $result->fetch_assoc()) {
    $total = $row['answered_count'];
    $percent = ($total > 0) ? round(($row['correct_answers'] / $total) * 100) : 0;
    $row['percent'] = $percent;
    $students_list[] = $row;

    // بيانات "أفضل الطلاب" (Top 10)
    if (count($chart_names) < 10) {
        $chart_names[] = $row['username'];
        $chart_scores[] = $percent;
    }

    // بيانات "النجاح والرسوب"
    if ($percent >= 50)
        $pass_count++;
    else
        $fail_count++;

    // بيانات "توزيع المستويات"
    $lvl = 'Lvl ' . $row['level'];
    if (!isset($levels_distribution[$lvl]))
        $levels_distribution[$lvl] = 0;
    $levels_distribution[$lvl]++;

    // بيانات "نطاقات الدرجات"
    if ($percent >= 80)
        $score_ranges['Excellent (80-100%)']++;
    elseif ($percent >= 50)
        $score_ranges['Good (50-79%)']++;
    else
        $score_ranges['Weak (0-49%)']++;
}

$student_count = count($students_list);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>الطلاب والتحليل | <?= htmlspecialchars($card['card_name']) ?></title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

        :root {
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-orange: #f59e0b;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            display: block;
        }

        * {
            box-sizing: border-box;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--card-dark);
            padding: 25px;
            display: flex;
            flex-direction: column;
            border-left: 1px solid rgba(255, 255, 255, 0.05);
            position: fixed;
            height: 100%;
            right: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-sizing: border-box;
        }

        .sidebar.close {
            transform: translateX(100%);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: 0.3s;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 50px;
            display: block;
            color: var(--text-main);
            text-decoration: none;
        }

        /* Main Content */
        .main-content {
            margin-right: var(--sidebar-width);
            padding: 40px;
            transition: margin-right 0.3s ease;
            box-sizing: border-box;
        }

        .main-content.expand {
            margin-right: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--card-dark);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .menu-toggle {
            font-size: 32px;
            display: none;
            cursor: pointer;
        }

        .back-btn {
            text-decoration: none;
            color: var(--accent-blue);
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Kappa Section */
        .kappa-section {
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
        }

        .kappa-card {
            background: linear-gradient(145deg, var(--card-dark), #162032);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .kappa-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background:
                <?= $kappa_color ?>
            ;
        }

        .kappa-val {
            font-size: 48px;
            font-weight: 900;
            color:
                <?= $kappa_color ?>
            ;
            margin: 10px 0;
        }

        .kappa-label {
            font-size: 18px;
            font-weight: bold;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .kappa-desc {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 25px;
        }

        .chart-box {
            background: var(--card-dark);
            padding: 20px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Table */
        .table-container {
            background: var(--card-dark);
            border-radius: 20px;
            padding: 20px;
            overflow-x: auto;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th {
            text-align: right;
            padding: 20px;
            color: var(--text-muted);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        td {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 15px;
        }

        tbody tr:hover {
            background: rgba(59, 130, 246, 0.05);
            cursor: pointer;
        }

        .badge-lvl {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: bold;
        }

        .btn-analyze {
            background: var(--accent-blue);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .progress-bar {
            width: 80px;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
        }

        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: var(--text-muted);
        }

        @media (max-width: 1100px) {
            .sidebar {
                transform: translateX(100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-right: 0;
                padding: 20px;
            }

            .menu-toggle {
                display: block;
            }

            .kappa-section,
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content" id="mainContent">

        <div class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <div>
                    <h1 style="margin:0;">المشروع: <span
                            style="color:var(--text-muted); font-weight:400;"><?= htmlspecialchars($card['card_name']) ?></span>
                    </h1>
                    <p style="margin:5px 0 0 0; color:var(--text-muted); font-size:13px;">إجمالي المكتملين: <strong
                            style="color:white"><?= $student_count ?></strong> <?= $limit_text ?></p>
                </div>
            </div>
            <a href="admin_cards_list.php" class="back-btn"><i class='bx bx-left-arrow-alt'></i> العودة</a>
        </div>

        <?php if ($student_count > 1): ?>
            <div class="kappa-section">
                <div class="kappa-card">
                    <div class="kappa-label">مؤشر الاتفاق (Fleiss' Kappa)</div>
                    <div class="kappa-val"><?= $kappa_score ?></div>
                    <div
                        style="background: <?= $kappa_color ?>20; color: <?= $kappa_color ?>; padding: 5px 15px; border-radius: 50px; display: inline-block; font-weight: bold; font-size: 14px; margin-bottom: 15px;">
                        <?= $kappa_text ?></div>
                    <p class="kappa-desc">يقيس مدى اتفاق الطلاب على الإجابات</p>
                </div>
                <div class="charts-grid">
                    <div class="chart-box">
                        <h3 style="margin-top:0; font-size:16px; color:var(--text-muted)">أعلى الطلاب أداءً</h3>
                        <div id="barChart"></div>
                    </div>
                    <div class="chart-box">
                        <h3 style="margin-top:0; font-size:16px; color:var(--text-muted)">نسب النجاح</h3>
                        <div id="donutChart"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <?php if ($student_count > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>المستوى</th>
                            <th>الإجابات</th>
                            <th>الصح</th>
                            <th>النسبة</th>
                            <th>آخر نشاط</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students_list as $s):
                            $percent = $s['percent'];
                            $bg = ($percent >= 80) ? '#10b981' : (($percent >= 50) ? '#f59e0b' : '#ef4444');
                            ?>
                            <tr
                                onclick="window.location='admin_user_analysis.php?user_id=<?= $s['user_id'] ?>&card_id=<?= $card_id ?>'">
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div
                                            style="width:30px; height:30px; background:#334155; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                                            <?= strtoupper(substr($s['username'], 0, 1)) ?></div>
                                        <?= htmlspecialchars($s['username']) ?>
                                    </div>
                                </td>
                                <td><span class="badge-lvl">Lvl <?= $s['level'] ?></span></td>
                                <td><?= $s['answered_count'] ?></td>
                                <td style="color:var(--accent-green); font-weight:bold;"><?= $s['correct_answers'] ?></td>
                                <td>
                                    <div style="display:flex; align-items:center;">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width:<?= $percent ?>%; background:<?= $bg ?>;">
                                            </div>
                                        </div> <?= $percent ?>%
                                    </div>
                                </td>
                                <td><?= $s['last_activity'] ? date('Y-m-d', strtotime($s['last_activity'])) : '-' ?></td>
                                <td><a href="admin_user_analysis.php?user_id=<?= $s['user_id'] ?>&card_id=<?= $card_id ?>"
                                        class="btn-analyze">تحليل</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class='bx bx-loader-circle' style="font-size:40px; margin-bottom:15px;"></i>
                    <h3>لا يوجد طلاب أكملوا هذا الكارد حتى الآن.</h3>
                    <p>التحليل والنتائج تظهر فقط للطلاب الذين أجابوا على جميع الأسئلة.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($student_count > 0): ?>
            <h2 class="section-title"><i class='bx bx-pie-chart-alt-2'></i> تحليلات إضافية</h2>
            <div class="charts-grid">
                <div class="chart-box">
                    <h3 style="margin-top:0; font-size:16px; color:var(--text-muted)">توزيع المستويات</h3>
                    <div id="levelsChart"></div>
                </div>
                <div class="chart-box">
                    <h3 style="margin-top:0; font-size:16px; color:var(--text-muted)">توزيع الدرجات</h3>
                    <div id="rangesChart"></div>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <?php if ($student_count > 0): ?>
        <script>
            function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const mainContent = document.getElementById('mainContent');

            if (window.innerWidth <= 1100) {
                sidebar.classList.toggle('active');
                // إظهار/إخفاء الخلفية المظللة
                if (overlay) {
                    overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
                }
            } else {
                sidebar.classList.toggle('close');
                mainContent.classList.toggle('expand');
            }
        }
            const menuBtn = document.getElementById('menuBtn');
            if (menuBtn) menuBtn.addEventListener('click', toggleMenu);

            // 1. Bar Chart (Top Students)
            var optionsBar = { series: [{ name: 'النسبة', data: <?= json_encode($chart_scores) ?> }], chart: { type: 'bar', height: 200, toolbar: { show: false }, background: 'transparent' }, colors: ['#3b82f6'], theme: { mode: 'dark' }, xaxis: { categories: <?= json_encode($chart_names) ?> } };
            new ApexCharts(document.querySelector("#barChart"), optionsBar).render();

            // 2. Donut Chart (Pass/Fail)
            var optionsDonut = { series: [<?= $pass_count ?>, <?= $fail_count ?>], labels: ['ناجح', 'راسب'], chart: { type: 'donut', height: 200, background: 'transparent' }, colors: ['#10b981', '#ef4444'], theme: { mode: 'dark' }, legend: { position: 'bottom' } };
            new ApexCharts(document.querySelector("#donutChart"), optionsDonut).render();

            // 3. Pie Chart (Levels)
            var optionsLevels = { series: <?= json_encode(array_values($levels_distribution)) ?>, labels: <?= json_encode(array_keys($levels_distribution)) ?>, chart: { type: 'pie', height: 200, background: 'transparent' }, legend: { position: 'bottom' }, theme: { mode: 'dark' } };
            new ApexCharts(document.querySelector("#levelsChart"), optionsLevels).render();

            // 4. Bar Chart (Ranges)
            var optionsRanges = { series: [{ name: 'عدد الطلاب', data: <?= json_encode(array_values($score_ranges)) ?> }], chart: { type: 'bar', height: 200, toolbar: { show: false }, background: 'transparent' }, plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } }, xaxis: { categories: <?= json_encode(array_keys($score_ranges)) ?> }, colors: ['#8b5cf6'], theme: { mode: 'dark' } };
            new ApexCharts(document.querySelector("#rangesChart"), optionsRanges).render();
        </script>
    <?php endif; ?>

</body>

</html>
