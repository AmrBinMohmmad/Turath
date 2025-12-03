<?php
require_once 'auth_guard.php';
checkUser();

require_once 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];

// 1. جلب جميع الكاردات
$sql = "
    SELECT c.*, 
    (SELECT COUNT(*) FROM if0_40458841_projects.cards_questions cq WHERE cq.card_id = c.id) as total_q,
    (SELECT COUNT(*) FROM if0_40458841_projects.annotations a WHERE a.project_id = c.id AND a.user_id = $user_id) as solved_q
    FROM if0_40458841_projects.cards c
    ORDER BY c.id DESC
";
$result = $conn->query($sql);

// 2. تجميع الكاردات حسب المنطقة
$cards_by_region = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dialect = $row['Dialect_type'];
        $cards_by_region[$dialect][] = $row;
    }
}

// 3. بيانات المناطق
$regions_info = [
    'Central' => ['title' => 'المنطقة الوسطى (نجد)', 'desc' => 'قلب المملكة النابض، موطن الفصاحة والشعر النبطي الأصيل.', 'icon' => 'bx-sun'],
    'Western' => ['title' => 'المنطقة الغربية (الحجاز)', 'desc' => 'بوابة الحرمين الشريفين وملتقى الثقافات.', 'icon' => 'bx-buildings'],
    'Southern' => ['title' => 'المنطقة الجنوبية', 'desc' => 'أرض الجبال والضباب، حيث الطبيعة الخلابة واللهجات العريقة.', 'icon' => 'bx-landscape'],
    'Eastern' => ['title' => 'المنطقة الشرقية', 'desc' => 'لؤلؤة الخليج ومصدر الطاقة.', 'icon' => 'bx-water'],
    'Northern' => ['title' => 'المنطقة الشمالية', 'desc' => 'بوابة الشمال وحارسة الحدود.', 'icon' => 'bx-compass'],
    'all' => ['title' => 'تحديات شاملة', 'desc' => 'مجموعة متنوعة من التحديات من كافة أنحاء المملكة.', 'icon' => 'bx-world'],
    'General' => ['title' => 'مصطلحات عامة', 'desc' => 'كلمات وعبارات شائعة في معظم المناطق السعودية.', 'icon' => 'bx-chat']
];

$display_order = ['Central', 'Western', 'Southern', 'Eastern', 'Northern', 'all', 'General'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مناطق المملكة | تحديات تراثية</title>
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-main: #020617; --bg-secondary: #0f172a; --text-main: #f8fafc; --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 255, 255, 0.05);
            --saudi-green: #006C35; --gold-light: #F2D06B; --gold-dark: #C69320;
        }
        body.light-mode {
            --bg-main: #fcfbf9; --bg-secondary: #f1f5f9; --text-main: #1e293b; --text-muted: #475569;
            --glass-bg: rgba(255, 255, 255, 0.8); --glass-border: rgba(0, 0, 0, 0.05);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; transition: background-color 0.3s, color 0.3s; }
        body { font-family: 'Cairo', sans-serif; background-color: var(--bg-main); color: var(--text-main); overflow-x: hidden; }

        /* Navbar */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; position: fixed; width: 100%; top: 0; z-index: 1000; background: rgba(var(--bg-main), 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid var(--glass-border); }
        .logo { font-size: 26px; font-weight: 800; color: var(--text-main); text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .nav-actions { display: flex; align-items: center; gap: 15px; }
        .icon-btn { width: 40px; height: 40px; border-radius: 50%; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-main); display: flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; font-size: 20px; }
        .icon-btn:hover { background: var(--saudi-green); color: white; border-color: var(--saudi-green); }
        .btn-gold { background: linear-gradient(135deg, var(--gold-light), var(--gold-dark)); color: #000; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 14px; }

        /* Page Layout */
        .container { max-width: 1400px; margin: 100px auto 50px; padding: 0 20px; }
        
        .page-header { text-align: center; margin-bottom: 60px; padding: 40px 0; border-bottom: 1px solid var(--glass-border); }
        .page-header h1 { font-size: 48px; margin-bottom: 10px; font-weight: 900; }
        .page-header span { color: var(--gold-light); }
        .page-header p { color: var(--text-muted); font-size: 18px; max-width: 700px; margin: 0 auto; }

        /* Region Section */
        .region-section { margin-bottom: 80px; scroll-margin-top: 100px; }
        
        .region-header { 
            display: flex; align-items: flex-start; gap: 20px; margin-bottom: 30px; 
            background: linear-gradient(to left, var(--glass-bg), transparent);
            padding: 20px; border-radius: 20px; border-right: 4px solid var(--gold-dark);
        }
        .region-icon { 
            font-size: 40px; color: var(--saudi-green); 
            background: rgba(0, 108, 53, 0.1); width: 70px; height: 70px; 
            border-radius: 15px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .region-info h2 { font-size: 28px; margin: 0 0 10px; color: var(--text-main); }
        .region-info p { color: var(--text-muted); font-size: 15px; line-height: 1.6; margin: 0; max-width: 800px; }

        /* Cards Grid */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
        
        .card-item {
            background: var(--glass-bg); border-radius: 20px; border: 1px solid var(--glass-border);
            overflow: hidden; transition: 0.3s; display: flex; flex-direction: column; position: relative;
            /* الكاردات المخفية ستأخذ كلاس خاص */
        }
        .card-item:hover { transform: translateY(-10px); border-color: var(--gold-dark); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        
        /* كلاس لإخفاء الكاردات الزائدة */
        .hidden-card { display: none; }
        /* كلاس لإظهارها مع حركة */
        .card-item.show-enter { animation: fadeIn 0.6s ease forwards; display: flex; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-img { height: 180px; background-size: cover; background-position: center; position: relative; }
        .card-type-badge { position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.6); color: white; padding: 5px 12px; border-radius: 8px; font-size: 12px; backdrop-filter: blur(5px); }
        
        .card-body { padding: 25px; flex-grow: 1; display: flex; flex-direction: column; }
        .card-title { font-size: 20px; font-weight: 700; margin-bottom: 10px; color: var(--text-main); }
        .card-desc { font-size: 14px; color: var(--text-muted); margin-bottom: 20px; line-height: 1.6; }
        
        .progress-box { margin-top: auto; margin-bottom: 15px; }
        .progress-bar { height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; margin-bottom: 5px; }
        .progress-fill { height: 100%; background: var(--saudi-green); border-radius: 3px; }
        .progress-text { font-size: 12px; color: var(--text-muted); display: flex; justify-content: space-between; }

        .btn-start { 
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark)); 
            color: #000; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: bold; transition: 0.3s; 
        }
        .btn-start:hover { box-shadow: 0 5px 15px rgba(198, 147, 32, 0.3); transform: scale(1.02); }
        .btn-completed { background: rgba(0, 108, 53, 0.1); color: var(--saudi-green); border: 1px solid var(--saudi-green); cursor: default; }

        /* زر عرض المزيد */
        .show-more-container { text-align: center; margin-top: 30px; }
        .btn-more {
            background: transparent; border: 2px solid var(--gold-dark); color: var(--gold-light);
            padding: 10px 30px; border-radius: 50px; cursor: pointer; font-weight: bold; font-family: inherit;
            transition: 0.3s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-more:hover { background: var(--gold-dark); color: #000; }

        @media (max-width: 768px) {
            .navbar { padding: 15px 20px; }
            .page-header h1 { font-size: 32px; }
            .region-header { flex-direction: column; align-items: center; text-align: center; border-right: none; border-bottom: 4px solid var(--gold-dark); }
            .region-info p { text-align: center; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo"><i class='bx bxl-flutter' style="color:var(--saudi-green)"></i> تراث المملكة</a>
        <div class="nav-actions">
            <a href="index.php" class="icon-btn" title="الرئيسية"><i class='bx bx-home-alt'></i></a>
            <a href="user_page.php" class="icon-btn" title="الملف الشخصي"><i class='bx bx-user'></i></a>
            <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
            <a href="logout.php" class="icon-btn" style="color:#ef4444; border-color:rgba(239,68,68,0.3);"><i class='bx bx-log-out'></i></a>
        </div>
    </nav>

    <div class="container">
        
        <header class="page-header">
            <h1>خريطة <span>الثقافة</span></h1>
            <p>اختر المنطقة وانطلق في رحلة لاستكشاف الكنوز اللغوية المخفية في كل زاوية من زوايا المملكة.</p>
        </header>

        <?php foreach ($display_order as $region_key): 
            if (empty($cards_by_region[$region_key])) continue;
            
            $info = $regions_info[$region_key] ?? [
                'title' => $region_key, 
                'desc' => 'مجموعة مميزة من الأسئلة التراثية.', 
                'icon' => 'bx-map'
            ];
            
            // عدد الكاردات في هذه المنطقة
            $total_region_cards = count($cards_by_region[$region_key]);
            $limit = 4; // عدد الكاردات التي ستظهر أولاً
        ?>
        
        <section class="region-section" id="region-<?= $region_key ?>">
            <div class="region-header">
                <div class="region-icon"><i class='bx <?= $info['icon'] ?>'></i></div>
                <div class="region-info">
                    <h2><?= $info['title'] ?> <span style="font-size:14px; color:var(--text-muted); font-weight:normal;">(<?= $total_region_cards ?> تحديات)</span></h2>
                    <p><?= $info['desc'] ?></p>
                </div>
            </div>

            <div class="cards-grid" id="grid-<?= $region_key ?>">
                <?php 
                $counter = 0;
                foreach ($cards_by_region[$region_key] as $c): 
                    $counter++;
                    $total = $c['total_q'];
                    $solved = $c['solved_q'];
                    $percent = ($total > 0) ? round(($solved / $total) * 100) : 0;
                    $is_done = $percent >= 100;
                    $type_names = [1 => 'كلمات', 2 => 'عبارات', 3 => 'أمثال', 4 => 'مختلط'];
                    $type_label = $type_names[$c['card_type']] ?? 'تحدي';
                    
                    // تحديد هل الكارد مخفي أم ظاهر
                    $class_hidden = ($counter > $limit) ? 'hidden-card' : '';
                ?>
                <div class="card-item <?= $class_hidden ?>">
                    <div class="card-img" style="background-image: url('<?= !empty($c['img']) ? htmlspecialchars($c['img']) : 'img/default.png' ?>');">
                        <span class="card-type-badge"><?= $type_label ?></span>
                    </div>
                    <div class="card-body">
                        <div class="card-title"><?= htmlspecialchars($c['card_name']) ?></div>
                        <div class="card-desc"><?= mb_substr(htmlspecialchars($c['description']), 0, 80) ?>...</div>
                        
                        <div class="progress-box">
                            <div class="progress-bar"><div class="progress-fill" style="width: <?= $percent ?>%"></div></div>
                            <div class="progress-text">
                                <span><?= $solved ?> / <?= $total ?> سؤال</span>
                                <span><?= $percent ?>%</span>
                            </div>
                        </div>

                        <?php if($is_done): ?>
                            <a href="#" class="btn-start btn-completed"><i class='bx bx-check-circle'></i> مكتمل</a>
                        <?php else: ?>
                            <a href="answer_card.php?id=<?= $c['id'] ?>" class="btn-start">
                                ابدأ التحدي <i class='bx bx-left-arrow-alt'></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_region_cards > $limit): ?>
                <div class="show-more-container">
                    <button class="btn-more" onclick="toggleCards('grid-<?= $region_key ?>', this)">
                        عرض المزيد <i class='bx bx-chevron-down'></i>
                    </button>
                </div>
            <?php endif; ?>

        </section>
        
        <?php endforeach; ?>

        <?php if(empty($cards_by_region)): ?>
            <div style="text-align:center; padding:50px; color:var(--text-muted);">
                <i class='bx bx-ghost' style="font-size:50px; margin-bottom:20px;"></i>
                <h2>لا توجد كاردات متاحة حالياً</h2>
                <p>انتظرونا قريباً بمحتوى جديد!</p>
            </div>
        <?php endif; ?>

    </div>

    <script>
        // دالة عرض المزيد / عرض الأقل
        function toggleCards(gridId, btn) {
            const grid = document.getElementById(gridId);
            const hiddenCards = grid.querySelectorAll('.hidden-card');
            
            // حالة 1: إظهار الكاردات
            if (hiddenCards.length > 0) {
                hiddenCards.forEach(card => {
                    card.classList.remove('hidden-card');
                    card.classList.add('show-enter'); // إضافة أنيميشن
                });
                btn.innerHTML = "عرض أقل <i class='bx bx-chevron-up'></i>";
            } 
            // حالة 2: إخفاء الكاردات (العودة للوضع الأصلي)
            else {
                const allCards = grid.querySelectorAll('.card-item');
                allCards.forEach((card, index) => {
                    if (index >= 4) { // إخفاء ما بعد الرابع
                        card.classList.add('hidden-card');
                        card.classList.remove('show-enter');
                    }
                });
                btn.innerHTML = "عرض المزيد <i class='bx bx-chevron-down'></i>";
                
                // تمرير الشاشة لأعلى القسم قليلاً
                grid.parentElement.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Theme Logic
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
    </script>

</body>
</html>