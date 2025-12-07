<?php
include 'db.php';
include 'functions.php';

// جلب البطاقات (المشاريع)
$projects = $conn->query("SELECT * FROM cards ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لوحة المستخدم | لهجتنا</title>
<link rel="stylesheet" href="style.css">
<link rel="icon" type="image/png" href="Favicon.png">
</head>
<body>

<header class="navbar">
    <a href="index.html" class="logo" style="text-decoration:none;">
        <img src="Favicon.png" alt="شعار لهجتنا">
        <div class="logo-text">
            <h1 class="site-title">لهجتنا</h1>
            <p class="site-tagline">اختبر معرفتك بثقافة وتراث مناطق المملكة</p>
        </div>
    </a>

    <nav>
        <?php if (isset($user)): ?>
            <span>مرحباً، <?= e($user['name']) ?></span>
            <a href="logout.php">تسجيل خروج</a>
        <?php elseif (isset($admin)): ?>
            <span>المشرف: <?= e($admin['name'] ?? 'Admin') ?></span>
            <a href="user_page.php">عرض صفحة المستخدم</a>
            <a href="logout.php">تسجيل خروج</a>
        <?php else: ?>
            <a href="login.php">تسجيل دخول</a>
        <?php endif; ?>
    </nav>
</header>

<main class="hero">
    <div class="hero-content">
        <h2>مرحباً، <?= e($user['name']) ?></h2>
        <p>ابدأ الاختبار عبر اختيار البطاقة المناسبة.</p>

        <div class="project-list">
            <h3>البطاقات المتاحة</h3>

            <?php if ($projects->num_rows > 0): ?>
                <?php while($project = $projects->fetch_assoc()): ?>
                    <div class="project-item">
                        <strong><?= e($project['card_name']) ?></strong><br>
                        عدد المستخدمين المسموح: <?= (int)$project['number_of_users'] ?><br>
                        عدد الأسئلة: <?= (int)$project['number_of_question'] ?><br>
                        
                        <a class="btn" href="start_annotation.php?card_id=<?= (int)$project['id'] ?>">
                            ابدأ الآن
                        </a>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <p>لا توجد بطاقات متاحة حالياً.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    © 2025 لهجتنا
</footer>

</body>
</html>
