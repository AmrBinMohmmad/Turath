<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once __DIR__ . '/../../auth/auth_guard.php';
checkAdmin();

require_once __DIR__ . '/../db.php'; // استخدام ملف الاتصال الموحد

// جلب الكاردات + عدد الطلاب الحاليين
$sql = "
    SELECT c.*, 
    (SELECT COUNT(DISTINCT user_id) FROM if0_40458841_projects.annotations a WHERE a.project_id = c.id) as student_count
    FROM if0_40458841_projects.cards c
    ORDER BY c.id DESC
";
$cards = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتائج الطلاب | Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        
        :root {
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
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
        
        * { box-sizing: border-box; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--card-dark);
            padding: 25px;
            display: flex;
            flex-direction: column;
            border-left: 1px solid rgba(255,255,255,0.05);
            position: fixed;
            height: 100%;
            right: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-sizing: border-box;
        }
        .sidebar.close { transform: translateX(100%); }
        .logo { font-size: 24px; font-weight: 700; margin-bottom: 50px; display: block; color: var(--text-main); text-decoration: none; }
        .nav-item { display: flex; align-items: center; gap: 15px; padding: 14px; color: var(--text-muted); text-decoration: none; margin-bottom: 8px; border-radius: 12px; transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background: rgba(59, 130, 246, 0.1); color: var(--accent-blue); }

        /* Main Content */
        .main-content {
            margin-right: var(--sidebar-width);
            padding: 40px;
            width: auto;
            min-width: 0;
            transition: margin-right 0.3s ease;
            box-sizing: border-box;
        }
        .main-content.expand { margin-right: 0; }

        .header { display: flex; align-items: center; margin-bottom: 30px; }
        .menu-toggle { font-size: 32px; color: var(--text-main); cursor: pointer; margin-left: 15px; display: none; }

        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .card-item {
            background: var(--card-dark);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
        }

        .card-item:hover {
            transform: translateY(-5px);
            border-color: var(--accent-blue);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
        }

        .card-img {
            height: 160px;
            background-size: cover;
            background-position: center;
            background-color: #334155;
            position: relative;
        }
        
        .badge-type {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 12px;
            backdrop-filter: blur(4px);
        }

        .card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-body h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: var(--text-main);
        }

        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--text-muted);
            background: rgba(255,255,255,0.03);
            padding: 10px;
            border-radius: 10px;
        }

        .stat { display: flex; align-items: center; gap: 5px; }
        .stat i { color: var(--accent-blue); font-size: 16px; }

        .btn-view {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 12px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            margin-top: auto;
            transition: 0.3s;
        }
        .btn-view:hover { box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4); }

        @media (max-width: 1100px) {
            .sidebar { transform: translateX(100%); right: 0; }
            .sidebar.active { transform: translateX(0); box-shadow: -10px 0 30px rgba(0,0,0,0.5); }
            .main-content { margin-right: 0; padding: 20px; }
            .menu-toggle { display: block; }
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <div>
                    <h1 style="margin:0; font-size: 24px;">نتائج الطلاب 📂</h1>
                    <p style="color: var(--text-muted); font-size: 14px; margin:5px 0 0 0;">اختر الكارد لعرض قائمة الطلاب المشاركين وتحليل أدائهم</p>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <?php if ($cards && $cards->num_rows > 0): ?>
                <?php while($c = $cards->fetch_assoc()): 
                    $types = [1=>'Words', 2=>'Phrases', 3=>'Proverbs', 4=>'Mixed'];
                    $type_name = $types[$c['card_type']] ?? 'Unknown';
                    
                    // --- التعديل هنا: منطق عرض العدد ---
                    $limit = $c['number_of_users'];
                    $current = $c['student_count'];
                    
                    // إذا كان الحد 0 يعني مفتوح، وإلا نعرض الحالي / الحد الأقصى
                    $participants_text = ($limit > 0) ? "$current / $limit" : "$current (مفتوح)";
                ?>
                <div class="card-item">
                    
                    
                    <div class="card-body">
                        <h3><?= htmlspecialchars($c['card_name']) ?></h3>
                        
                        <div class="stats-row">
                            <div class="stat">
                                <i class='bx bxs-user-detail'></i>
                                <span><?= $participants_text ?> مشارك</span>
                            </div>
                            <div class="stat">
                                <i class='bx bxs-help-circle'></i>
                                <span><?= $c['number_of_question'] ?? 20 ?> سؤال</span>
                            </div>
                        </div>

                        <a href="admin_card_users.php?card_id=<?= $c['id'] ?>" class="btn-view">
                            عرض النتائج <i class='bx bx-right-arrow-alt'></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: var(--text-muted);">
                    <i class='bx bx-folder-open' style="font-size: 50px; margin-bottom: 10px;"></i>
                    <p>لا توجد كاردات مضافة حالياً.</p>
                    <a href="create_card_admin_site.php" style="color: var(--accent-blue);">أضف كارد جديد</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>