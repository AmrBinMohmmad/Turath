<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once 'auth_guard.php';
checkAdmin();

require_once 'db.php'; // استخدام ملف الاتصال الموحد

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
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/admin_cards_list.css">
     <link rel="icon" type="image/png" href="Favicon.png" />
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <div>
                    <h1 style="margin:0; font-size: 24px;">نتائج الطلاب</h1>
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

</html>

