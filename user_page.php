<?php
session_start();
require "config2.php";

$user_id = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['name'] ?? "زائر";

$projects = $conn->query("
    SELECT c.*, 
    (SELECT COUNT(DISTINCT a.user_id) FROM annotations a WHERE a.project_id=c.id) AS completed_users
    FROM cards c ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>لوحة المستخدم | لهجتنا</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
    <div class="logo-area">
        <img src="Favicon.png" class="logo-img">
        <div>
            <h1 class="site-title">لهجتنا</h1>
            <p class="site-tagline">اختبر معرفتك بثقافة وتراث مناطق المملكة</p>
        </div>
    </div>
    <nav>
        <span>مرحباً، <?= htmlspecialchars($user_name) ?></span>
        <a href="logout.php">تسجيل خروج</a>
    </nav>
</header>

<section class="user-page">
    <div class="user-header">
        <h2>اختر فئة للبدء</h2>
        <p>الفئات مقسمة حسب اللهجة أو المنطقة. اختر بطاقة للدخول في وضع الأسئلة.</p>
    </div>

    <div class="cards-grid">
        <?php if($projects->num_rows > 0): ?>
            <?php while($p = $projects->fetch_assoc()):
                $total_q = $p['number_of_question'];
                $answered = $conn->query("SELECT COUNT(*) AS c FROM annotations WHERE user_id=$user_id AND project_id={$p['id']}")->fetch_assoc()['c'];
                $progress = ($total_q > 0) ? round(($answered / $total_q) * 100) : 0;
            ?>
            <div class="card">
                <div class="card-title"><?= htmlspecialchars($p['card_name']) ?></div>

                <div class="card-info">
                    عدد الأسئلة: <?= $total_q ?><br>
                    عدد المشاركين: <?= $p['completed_users'] ?> / <?= $p['number_of_users'] ?><br>
                    تقدّمك: <?= $progress ?>% (<?= $answered ?>/<?= $total_q ?>)
                </div>

                <a class="card-btn" href="answer_project.php?id=<?= $p['id'] ?>">
                    <?= ($progress > 0 && $progress < 100) ? "أكمل الآن" : "ابدأ الآن" ?>
                </a>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-projects">لا توجد بطاقات حالياً.</p>
        <?php endif; ?>
    </div>
</section>

<footer>
    © 2025 لهجتنا
</footer>
</body>
</html>
