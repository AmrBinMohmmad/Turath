<?php
ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

$host = "sql206.infinityfree.com"; $user = "if0_40458841"; $password = "PoweR135"; $database = "if0_40458841_users_db"; 
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) { die("فشل الاتصال: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

$users_count = $conn->query("SELECT COUNT(*) FROM if0_40458841_users_db.users")->fetch_row()[0];
$cards_count = $conn->query("SELECT COUNT(*) FROM if0_40458841_projects.cards")->fetch_row()[0];
$answers_count = $conn->query("SELECT COUNT(*) FROM if0_40458841_projects.annotations")->fetch_row()[0];
$recent_users = $conn->query("SELECT id, username, email, xp, level FROM if0_40458841_users_db.users ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | لوحة المدير</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../css/admin_page.css">
    <link rel="icon" type="image/png" href="../../assets/images/Favicon.png" />
</head>
<body>

    <?php include __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        
        <header class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <div style="margin-right: 15px;">
                    <h1 style="font-size: 24px; margin:0;">لوحة التحكم </h1>
                    <p style="color: var(--text-muted); font-size: 14px; margin:5px 0 0 0;">نظرة شاملة على أداء المنصة</p>
                </div>
            </div>
            <div class="admin-badge">Admin Access</div>
        </header>

        <div class="dashboard-grid">
            <div class="card stat-card" style="border-right: 4px solid var(--accent-blue);">
                <i class='bx bxs-user stat-icon' style="color: var(--accent-blue);"></i>
                <div class="stat-label">إجمالي الطلاب</div>
                <div class="stat-value"><?= number_format($users_count-1) ?></div>
            </div>
            <div class="card stat-card" style="border-right: 4px solid var(--accent-green);">
                <i class='bx bxs-folder stat-icon' style="color: var(--accent-green);"></i>
                <div class="stat-label">المشاريع النشطة</div>
                <div class="stat-value"><?= number_format($cards_count) ?></div>
            </div>
            <div class="card stat-card" style="border-right: 4px solid var(--accent-orange);">
                <i class='bx bxs-edit stat-icon' style="color: var(--accent-orange);"></i>
                <div class="stat-label">إجمالي الإجابات</div>
                <div class="stat-value"><?= number_format($answers_count) ?></div>
            </div>
            
        </div>

        <div class="card">
                <h3 style="margin-bottom: 20px;">إجراءات سريعة</h3>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <a href="create_card_admin_site.php" class="action-btn" style="flex-direction: row; justify-content: flex-start; padding: 15px;">
                        <i class='bx bxs-plus-circle'></i> <span>إضافة كارد جديد</span>
                    </a>
                    <a href="admin_project_answers.php" class="action-btn" style="flex-direction: row; justify-content: flex-start; padding: 15px;">
                        <i class='bx bxs-analyse'></i> <span>مراجعة التقارير</span>
                    </a>
                    
                </div>
            </div>

        <br>

        <div class="card">
            <h3 style="margin-bottom: 15px;">آخر الأعضاء المنضمين</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead><tr><th>ID</th><th>اسم المستخدم</th><th>البريد الإلكتروني</th><th>المستوى</th><th>XP</th><th>الحالة</th></tr></thead>
                    <tbody>
                        <?php if ($recent_users && $recent_users->num_rows > 0): ?>
                            <?php while($u = $recent_users->fetch_assoc()): ?>
                            <tr class="user-row">
                                <td>#<?= $u['id'] ?></td>
                                <td style="font-weight: bold; color: white;"><?= htmlspecialchars($u['username']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><span style="background: rgba(59, 130, 246, 0.1); color: var(--accent-blue); padding: 2px 8px; border-radius: 4px;">Lvl <?= $u['level'] ?? 1 ?></span></td>
                                <td><?= number_format($u['xp'] ?? 0) ?> XP</td>
                                <td style="color: var(--accent-green);">نشط</td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center;">لا يوجد مستخدمين</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        var options = {
            series: [{ name: 'تفاعلات الطلاب', data: [44, 55, 57, 56, 61, 58, 63] }, { name: 'تسجيلات جديدة', data: [76, 85, 101, 98, 87, 105, 91] }],
            chart: { type: 'bar', height: 300, toolbar: { show: false }, background: 'transparent', fontFamily: 'Outfit, sans-serif' },
            plotOptions: { bar: { horizontal: false, columnWidth: '55%', endingShape: 'rounded' } },
            dataLabels: { enabled: false }, stroke: { show: true, width: 2, colors: ['transparent'] },
            xaxis: { categories: ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'], labels: { style: { colors: '#94a3b8' } } },
            yaxis: { labels: { style: { colors: '#94a3b8' } } }, fill: { opacity: 1 }, colors: ['#3b82f6', '#ef4444'], grid: { borderColor: 'rgba(255,255,255,0.05)' }, theme: { mode: 'dark' }
        };
        var chart = new ApexCharts(document.querySelector("#adminChart"), options);
        chart.render();
    </script>
</body>
</html>
