<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once 'auth_guard.php';
checkAdmin();

$host = "sql206.infinityfree.com";
$user = "if0_40458841";
$password = "PoweR135";
$conn = new mysqli($host, $user, $password);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$total_attempts = $conn->query("SELECT COUNT(*) FROM if0_40458841_projects.annotations")->fetch_row()[0];
$avg_score_query = $conn->query("SELECT AVG(score) * 100 FROM if0_40458841_projects.annotations");
$avg_score = $avg_score_query ? round($avg_score_query->fetch_row()[0], 1) : 0;

$sql_type = "SELECT c.card_type, COUNT(a.id) as count, SUM(a.score) as correct FROM if0_40458841_projects.cards c JOIN if0_40458841_projects.annotations a ON c.id = a.project_id GROUP BY c.card_type";
$res_type = $conn->query($sql_type);
$type_labels = [];
$type_data = [];
$type_names = [1 => 'Words', 2 => 'Phrases', 3 => 'Proverbs', 4 => 'Mixed'];
while ($row = $res_type->fetch_assoc()) {
    $type_labels[] = $type_names[$row['card_type']] ?? 'Unknown';
    $type_data[] = ($row['count'] > 0) ? round(($row['correct'] / $row['count']) * 100, 1) : 0;
}

$sql_dialect = "SELECT c.dialect_type, AVG(a.score) * 100 as success_rate FROM if0_40458841_projects.cards c JOIN if0_40458841_projects.annotations a ON c.id = a.project_id WHERE c.dialect_type != 'all' GROUP BY c.dialect_type ORDER BY success_rate ASC";
$res_dialect = $conn->query($sql_dialect);
$dialect_labels = [];
$dialect_data = [];
while ($row = $res_dialect->fetch_assoc()) {
    $dialect_labels[] = $row['dialect_type'];
    $dialect_data[] = round($row['success_rate'], 1);
}

$res_hardest = $conn->query("SELECT c.card_name, c.img, COUNT(a.id) as attempts, AVG(a.score) * 100 as rate FROM if0_40458841_projects.cards c JOIN if0_40458841_projects.annotations a ON c.id = a.project_id GROUP BY c.id HAVING attempts > 0 ORDER BY rate ASC LIMIT 5");
$res_top_students = $conn->query("SELECT u.username, u.level, COUNT(a.id) as total_ans FROM if0_40458841_users_db.users u JOIN if0_40458841_projects.annotations a ON u.id = a.user_id GROUP BY u.id ORDER BY total_ans DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تحليل البيانات | Admin</title>
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
            --accent-purple: #8b5cf6;
            --accent-green: #10b981;
            --accent-red: #f43f5e;
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
            margin-bottom: 8px;
            border-radius: 12px;
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
            padding: 30px;
            width: auto;
            flex-grow: 1;
            transition: margin-right 0.3s ease;
            box-sizing: border-box;
        }

        .main-content.expand {
            margin-right: 0;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .menu-toggle {
            font-size: 32px;
            color: var(--text-main);
            cursor: pointer;
            margin-left: 15px;
            display: none;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 25px;
        }

        .card {
            background: var(--card-dark);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: linear-gradient(135deg, var(--card-dark) 0%, rgba(30, 41, 59, 0.5) 100%);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            text-align: right;
            color: var(--text-muted);
            font-size: 13px;
        }

        td {
            color: #fff;
            font-size: 14px;
        }

        .progress-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            width: 100px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
        }

        @media (max-width: 1100px) {

            .analytics-grid,
            .stats-row {
                grid-template-columns: 1fr;
            }

            .sidebar {
                transform: translateX(100%);
                right: 0;
            }

            .sidebar.active {
                transform: translateX(0);
                box-shadow: -10px 0 30px rgba(0, 0, 0, 0.5);
            }

            .main-content {
                margin-right: 0;
                padding: 20px;
            }

            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="header">
            <div style="display: flex; align-items: center;"><i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <h1 style="margin:0; font-size: 24px;">مركز التحليلات الشامل 📊</h1>
            </div>
        </div>
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-icon" style="background:rgba(59,130,246,0.1); color:#3b82f6;"><i
                        class='bx bx-target-lock'></i></div>
                <div class="stat-info">
                    <h4><?= number_format($total_attempts) ?></h4><span>محاولات</span>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:rgba(16,185,129,0.1); color:#10b981;"><i
                        class='bx bx-check-shield'></i></div>
                <div class="stat-info">
                    <h4><?= $avg_score ?>%</h4><span>دقة</span>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:rgba(245,158,11,0.1); color:#f59e0b;"><i
                        class='bx bx-category'></i></div>
                <div class="stat-info">
                    <h4><?= count($type_labels) ?></h4><span>أنواع</span>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:rgba(244,63,94,0.1); color:#f43f5e;"><i
                        class='bx bx-error-circle'></i></div>
                <div class="stat-info">
                    <h4><?= 100 - $avg_score ?>%</h4><span>خطأ</span>
                </div>
            </div>
        </div>
        <div class="analytics-grid">
            <div class="card">
                <h3>أداء الطلاب</h3>
                <div id="typeChart"></div>
            </div>
            <div class="card">
                <h3>اللهجات</h3>
                <div id="dialectChart"></div>
            </div>
            <div class="card">
                <h3 style="color:#f43f5e;">🔥 الأصعب</h3>
                <table>
                    <thead>
                        <tr>
                            <th>الكارد</th>
                            <th>محاولات</th>
                            <th>نجاح</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_hardest->num_rows > 0):
                            while ($h = $res_hardest->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($h['card_name']) ?></td>
                                    <td><?= $h['attempts'] ?></td>
                                    <td style="color:#f43f5e; font-weight:bold;"><?= round($h['rate'], 1) ?>%</td>
                                </tr><?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h3 style="color:#10b981;">🏆 الأنشط</h3>
                <table>
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>مستوى</th>
                            <th>إجابات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_top_students->num_rows > 0):
                            while ($s = $res_top_students->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['username']) ?></td>
                                    <td style="color:#3b82f6;">Lvl <?= $s['level'] ?></td>
                                    <td><?= $s['total_ans'] ?></td>
                                </tr><?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script>
        new ApexCharts(document.querySelector("#typeChart"), { series: [{ name: 'نجاح', data: <?= json_encode($type_data) ?> }], chart: { type: 'bar', height: 300, background: 'transparent', toolbar: { show: false } }, colors: ['#3b82f6'], theme: { mode: 'dark' }, xaxis: { categories: <?= json_encode($type_labels) ?> } }).render();
        new ApexCharts(document.querySelector("#dialectChart"), { series: <?= json_encode($dialect_data) ?>, chart: { type: 'polarArea', height: 320, background: 'transparent' }, labels: <?= json_encode($dialect_labels) ?>, theme: { mode: 'dark' }, yaxis: { show: false } }).render();
    </script>
</body>

</html>