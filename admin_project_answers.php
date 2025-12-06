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
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/admin_project_answers.css">
     <link rel="icon" type="image/png" href="Favicon.png" />

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

