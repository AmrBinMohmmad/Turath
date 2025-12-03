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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        :root { --bg-dark: #0f172a; --card-dark: #1e293b; --text-main: #f1f5f9; --text-muted: #94a3b8; --accent-blue: #3b82f6; --accent-red: #ef4444; --accent-green: #10b981; --accent-orange: #f59e0b; --sidebar-width: 260px; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-dark); color: var(--text-main); min-height: 100vh; overflow-x: hidden; display: block; }
        
        /* Sidebar Styles need to be present for layout, even if HTML is included */
        .sidebar { width: var(--sidebar-width); background: var(--card-dark); padding: 25px; display: flex; flex-direction: column; border-left: 1px solid rgba(255,255,255,0.05); position: fixed; height: 100%; right: 0; top: 0; z-index: 1000; transition: all 0.3s ease; box-sizing: border-box; }
        .sidebar.close { transform: translateX(100%); }
        .logo { font-size: 24px; font-weight: 700; margin-bottom: 50px; display: flex; align-items: center; gap: 10px; color: var(--text-main); white-space: nowrap; }
        .logo span { color: var(--accent-red); }
        .nav-item { display: flex; align-items: center; gap: 15px; padding: 14px 18px; color: var(--text-muted); text-decoration: none; border-radius: 12px; margin-bottom: 8px; transition: 0.3s; font-size: 15px; white-space: nowrap; }
        .nav-item:hover, .nav-item.active { background: rgba(239, 68, 68, 0.1); color: var(--accent-red); }

        .main-content { margin-right: var(--sidebar-width); padding: 30px; width: auto; min-width: 0; transition: all 0.3s ease; }
        .main-content.expand { margin-right: 0; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .menu-toggle { font-size: 32px; color: var(--text-main); cursor: pointer; margin-left: 15px; display: none; } /* مخفي في الديسك توب */
        .admin-badge { background: var(--accent-red); color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; box-shadow: 0 0 15px rgba(239, 68, 68, 0.4); }

        .dashboard-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; margin-bottom: 30px; }
        .card { background: var(--card-dark); border-radius: 20px; padding: 25px; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.2); position: relative; overflow: hidden; height: 100%; }
        .stat-card { display: flex; flex-direction: column; justify-content: space-between; height: 140px; }
        .stat-icon { position: absolute; top: 20px; left: 20px; font-size: 40px; opacity: 0.1; }
        .stat-value { font-size: 32px; font-weight: 700; color: var(--text-main); margin-top: auto; }
        .stat-label { font-size: 14px; color: var(--text-muted); }

        .actions-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .action-btn { background: linear-gradient(135deg, var(--card-dark) 0%, #263345 100%); padding: 20px; border-radius: 15px; text-align: center; text-decoration: none; color: var(--text-main); border: 1px solid rgba(255,255,255,0.05); transition: 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; }
        .action-btn:hover { transform: translateY(-5px); border-color: var(--accent-red); box-shadow: 0 5px 15px rgba(239, 68, 68, 0.1); }
        .action-btn i { font-size: 28px; color: var(--accent-red); }

        .table-container { grid-column: span 3; background: var(--card-dark); border-radius: 20px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: right; color: var(--text-muted); font-size: 13px; padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; }
        tr:last-child td { border-bottom: none; }
        .user-row:hover { background: rgba(255,255,255,0.02); }

        @media (max-width: 1100px) {
            .dashboard-grid, .actions-grid { grid-template-columns: 1fr; }
            .sidebar { transform: translateX(100%); right: 0; }
            .sidebar.active { transform: translateX(0); box-shadow: -10px 0 30px rgba(0,0,0,0.5); }
            .main-content { margin-right: 0; }
            .menu-toggle { display: flex; } /* إظهار الزر في الجوال */
            .table-container { grid-column: span 1; overflow-x: auto; }
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        
        <header class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <div style="margin-right: 15px;">
                    <h1 style="font-size: 24px; margin:0;">لوحة التحكم ⚙️</h1>
                    <p style="color: var(--text-muted); font-size: 14px; margin:5px 0 0 0;">نظرة شاملة على أداء المنصة</p>
                </div>
            </div>
            <div class="admin-badge">Admin Access</div>
        </header>

        <div class="dashboard-grid">
            <div class="card stat-card" style="border-right: 4px solid var(--accent-blue);">
                <i class='bx bxs-user stat-icon' style="color: var(--accent-blue);"></i>
                <div class="stat-label">إجمالي الطلاب</div>
                <div class="stat-value"><?= number_format($users_count) ?></div>
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
            <div class="card stat-card" style="border-right: 4px solid var(--accent-red);">
                <i class='bx bxs-server stat-icon' style="color: var(--accent-red);"></i>
                <div class="stat-label">حالة السيرفر</div>
                <div class="stat-value" style="font-size: 24px; color: var(--accent-green);">مستقر 100%</div>
            </div>
        </div>

        <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
            <div class="card" style="min-height: 350px;">
                <h3 style="margin-bottom: 20px;">نشاط المنصة (Weekly Activity)</h3>
                <div id="adminChart"></div>
            </div>
            <div class="card">
                <h3 style="margin-bottom: 20px;">إجراءات سريعة</h3>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <a href="create_card_admin_site.php" class="action-btn" style="flex-direction: row; justify-content: flex-start; padding: 15px;">
                        <i class='bx bxs-plus-circle'></i> <span>إضافة مشروع جديد</span>
                    </a>
                    <a href="admin_project_answers.php" class="action-btn" style="flex-direction: row; justify-content: flex-start; padding: 15px;">
                        <i class='bx bxs-analyse'></i> <span>مراجعة التقارير</span>
                    </a>
                    
                </div>
            </div>
        </div>

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