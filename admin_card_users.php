<?php
require_once 'auth_guard.php';
checkAdmin();

require_once 'db.php';

// التحقق من وجود ID الكارد
if (!isset($_GET['card_id'])) {
    header("Location: admin_cards_list.php");
    exit();
}

$card_id = intval($_GET['card_id']);

// 1. جلب بيانات الكارد (بما في ذلك الحد الأقصى number_of_users)
$stmt_card = $conn->prepare("SELECT * FROM if0_40458841_projects.cards WHERE id = ?");
$stmt_card->bind_param("i", $card_id);
$stmt_card->execute();
$card = $stmt_card->get_result()->fetch_assoc();

if (!$card) {
    die("الكارد غير موجود.");
}

// 2. جلب الطلاب المشاركين
$sql_students = "
    SELECT 
        u.id as user_id, u.username, u.email, u.level,
        COUNT(a.id) as answered_count,
        SUM(a.score) as correct_answers,
        MAX(a.created_at) as last_activity
    FROM if0_40458841_users_db.users u
    JOIN if0_40458841_projects.annotations a ON u.id = a.user_id
    WHERE a.project_id = ?
    GROUP BY u.id
    ORDER BY correct_answers DESC, answered_count DESC
";

$stmt_std = $conn->prepare($sql_students);
$stmt_std->bind_param("i", $card_id);
$stmt_std->execute();
$result = $stmt_std->get_result();

// --- تجهيز البيانات ---
$students_list = [];
$chart_names = [];
$chart_scores = [];
$pass_count = 0;
$fail_count = 0;
$levels_distribution = [];
$score_ranges = ['Weak (0-49%)' => 0, 'Good (50-79%)' => 0, 'Excellent (80-100%)' => 0];

while ($row = $result->fetch_assoc()) {
    $total = $row['answered_count'];
    $correct = $row['correct_answers'];
    $percent = ($total > 0) ? round(($correct / $total) * 100) : 0;

    $row['percent'] = $percent;
    $students_list[] = $row;

    if (count($chart_names) < 10) {
        $chart_names[] = $row['username'];
        $chart_scores[] = $percent;
    }

    if ($percent >= 50)
        $pass_count++;
    else
        $fail_count++;

    $lvl = 'Lvl ' . $row['level'];
    if (!isset($levels_distribution[$lvl]))
        $levels_distribution[$lvl] = 0;
    $levels_distribution[$lvl]++;

    if ($percent >= 80)
        $score_ranges['Excellent (80-100%)']++;
    elseif ($percent >= 50)
        $score_ranges['Good (50-79%)']++;
    else
        $score_ranges['Weak (0-49%)']++;
}

$student_count = count($students_list);

// --- منطق عرض النص الجديد (X من أصل Y) ---
$max_limit = $card['number_of_users'];
// إذا كان الحد 0، نعتبره "غير محدود"
$limit_text = ($max_limit > 0) ? "من أصل <strong style='color:var(--accent-blue)'>$max_limit</strong>" : "<span style='color:var(--accent-green)'>(مفتوح)</span>";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الطلاب المشاركون | <?= htmlspecialchars($card['card_name']) ?></title>
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

        /* Sidebar & Layout */
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

        .main-content {
            margin-right: var(--sidebar-width);
            padding: 40px;
            width: auto;
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
            color: var(--text-main);
            cursor: pointer;
            margin-left: 15px;
            display: none;
        }

        .back-btn {
            text-decoration: none;
            color: var(--accent-blue);
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Table */
        .table-container {
            background: var(--card-dark);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow-x: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 40px;
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
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        td {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 15px;
        }

        tr:last-child td {
            border-bottom: none;
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

        .btn-analyze {
            background: var(--accent-blue);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .score-box {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
        }

        .score-high {
            color: var(--accent-green);
        }

        .score-med {
            color: #f59e0b;
        }

        .score-low {
            color: var(--accent-red);
        }

        /* Charts */
        .charts-section {
            margin-top: 40px;
        }

        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 25px;
        }

        .chart-card {
            background: var(--card-dark);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .chart-card h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            color: var(--text-muted);
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
                    <h1 style="margin:0;">
                        <i class='bx bxs-group' style="color:var(--accent-blue)"></i>
                        الطلاب المشاركون: <span
                            style="color:var(--text-muted); font-weight:400;"><?= htmlspecialchars($card['card_name']) ?></span>
                    </h1>
                    <p style="margin:5px 0 0 0; color:var(--text-muted); font-size:13px;">
                        إجمالي المشاركين: <strong style="color:white"><?= $student_count ?></strong> <?= $limit_text ?>
                    </p>
                </div>
            </div>
            <a href="admin_cards_list.php" class="back-btn"><i class='bx bx-left-arrow-alt'></i> العودة للكاردات</a>
        </div>

        <div class="table-container">
            <?php if ($student_count > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>المستوى</th>
                            <th>الإجابات الكلية</th>
                            <th>الإجابات الصحيحة</th>
                            <th>نسبة النجاح</th>
                            <th>آخر نشاط</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students_list as $s):
                            $percent = $s['percent'];
                            $bg_color = ($percent >= 80) ? '#10b981' : (($percent >= 50) ? '#f59e0b' : '#ef4444');
                            $txt_color = ($percent >= 80) ? 'var(--accent-green)' : (($percent >= 50) ? 'var(--accent-orange)' : 'var(--accent-red)');
                            $class_color = ($percent >= 80) ? 'score-high' : (($percent >= 50) ? 'score-med' : 'score-low');
                            ?>
                            <tr
                                onclick="window.location='admin_user_analysis.php?user_id=<?= $s['user_id'] ?>&card_id=<?= $card_id ?>'">
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div
                                            style="width:35px; height:35px; background:#334155; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:white;">
                                            <?= strtoupper(substr($s['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:bold;"><?= htmlspecialchars($s['username']) ?></div>
                                            <div style="font-size:12px; color:var(--text-muted)">
                                                <?= htmlspecialchars($s['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge-lvl">Lvl <?= $s['level'] ?></span></td>
                                <td style="font-weight:bold;"><?= $s['answered_count'] ?></td>
                                <td style="color:var(--accent-green); font-weight:bold;"><?= $s['correct_answers'] ?></td>
                                <td>
                                    <div class="score-box <?= $class_color ?>">
                                        <span><?= $percent ?>%</span>
                                        <div class="progress-bar">
                                            <div class="progress-fill"
                                                style="width:<?= $percent ?>%; background:<?= $bg_color ?>;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:var(--text-muted); font-size:13px;">
                                    <?= date('Y-m-d', strtotime($s['last_activity'])) ?></td>
                                <td><a href="admin_user_analysis.php?user_id=<?= $s['user_id'] ?>&card_id=<?= $card_id ?>"
                                        class="btn-analyze">تحليل</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class='bx bx-user-x'></i>
                    <h3>لا يوجد طلاب مشاركون في هذا الكارد بعد.</h3>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($student_count > 0): ?>
            <div class="charts-section">
                <h2 class="section-title"><i class='bx bx-bar-chart-square'></i> مقارنة أداء الطلاب</h2>
                <div class="charts-grid">
                    <div class="chart-card" style="grid-column: span 2;">
                        <h3>أعلى الطلاب أداءً (Top 10)</h3>
                        <div id="barChart"></div>
                    </div>
                    <div class="chart-card">
                        <h3>معدل النجاح العام</h3>
                        <div id="donutChart"></div>
                    </div>
                    <div class="chart-card">
                        <h3>توزيع المستويات</h3>
                        <div id="levelsChart"></div>
                    </div>
                    <div class="chart-card" style="grid-column: span 2;">
                        <h3>توزيع الدرجات (Score Ranges)</h3>
                        <div id="rangesChart"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <?php if ($student_count > 0): ?>
        <script>
            var optionsBar = { series: [{ name: 'نسبة النجاح', data: <?= json_encode($chart_scores) ?> }], chart: { type: 'bar', height: 300, background: 'transparent', toolbar: { show: false } }, plotOptions: { bar: { borderRadius: 4, horizontal: true, distributed: true } }, dataLabels: { enabled: true, formatter: function (val) { return val + "%"; } }, xaxis: { categories: <?= json_encode($chart_names) ?>, labels: { style: { colors: '#94a3b8' } } }, yaxis: { labels: { style: { colors: '#fff' } } }, theme: { mode: 'dark' }, colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'] };
            new ApexCharts(document.querySelector("#barChart"), optionsBar).render();

            var optionsDonut = { series: [<?= $pass_count ?>, <?= $fail_count ?>], labels: ['ناجح (>=50%)', 'يحتاج تحسين (<50%)'], chart: { type: 'donut', height: 250, background: 'transparent' }, colors: ['#10b981', '#ef4444'], legend: { position: 'bottom', labels: { colors: '#94a3b8' } } };
            new ApexCharts(document.querySelector("#donutChart"), optionsDonut).render();

            var optionsLevels = { series: <?= json_encode(array_values($levels_distribution)) ?>, labels: <?= json_encode(array_keys($levels_distribution)) ?>, chart: { type: 'pie', height: 280, background: 'transparent' }, legend: { position: 'bottom', labels: { colors: '#94a3b8' } }, theme: { mode: 'dark' } };
            new ApexCharts(document.querySelector("#levelsChart"), optionsLevels).render();

            var optionsRanges = { series: [{ name: 'عدد الطلاب', data: <?= json_encode(array_values($score_ranges)) ?> }], chart: { type: 'bar', height: 300, background: 'transparent', toolbar: { show: false } }, plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } }, xaxis: { categories: <?= json_encode(array_keys($score_ranges)) ?>, labels: { style: { colors: '#94a3b8' } } }, yaxis: { labels: { style: { colors: '#fff' } } }, colors: ['#8b5cf6'], theme: { mode: 'dark' } };
            new ApexCharts(document.querySelector("#rangesChart"), optionsRanges).render();
        </script>
    <?php endif; ?>

</body>


</html>
