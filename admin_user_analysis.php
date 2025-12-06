<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once 'auth_guard.php';
checkAdmin();

require_once 'db.php';

// التحقق من المدخلات
if (!isset($_GET['user_id']) || !isset($_GET['card_id'])) {
    header("Location: admin_cards_list.php");
    exit();
}

$user_id = intval($_GET['user_id']);
$card_id = intval($_GET['card_id']);

// 1. جلب بيانات الطالب
$user = $conn->query("SELECT * FROM if0_40458841_users_db.users WHERE id = $user_id")->fetch_assoc();

// 2. جلب بيانات الكارد
$card = $conn->query("SELECT * FROM if0_40458841_projects.cards WHERE id = $card_id")->fetch_assoc();

if (!$user || !$card) {
    die("خطأ: البيانات غير موجودة.");
}

// 3. إحصائيات سريعة لهذا الكارد
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(score) as correct
    FROM if0_40458841_projects.annotations 
    WHERE user_id = $user_id AND project_id = $card_id
";
$stats = $conn->query($stats_query)->fetch_assoc();

$total = $stats['total'];
$correct = $stats['correct'] ?? 0;
$wrong = $total - $correct;
$percent = ($total > 0) ? round(($correct / $total) * 100) : 0;

// 4. جلب تفاصيل الأسئلة والإجابات
// هذا الاستعلام "الذكي" يقوم بجلب نص السؤال ومعناه من الجدول المناسب (كلمات، عبارات، أمثال)
// بناءً على نوع السؤال (type_of_q) المحفوظ في جدول cards_questions
$sql_details = "
    SELECT 
        a.answer as user_answer, 
        a.score,
        cq.type_of_q,
        -- جلب نص السؤال (Term) حسب الجدول
        CASE 
            WHEN cq.type_of_q = 1 THEN (SELECT Term FROM if0_40458841_questions_db.words_db WHERE id = cq.number_of_q)
            WHEN cq.type_of_q = 2 THEN (SELECT Term FROM if0_40458841_questions_db.phrases_db WHERE id = cq.number_of_q)
            WHEN cq.type_of_q = 3 THEN (SELECT Term FROM if0_40458841_questions_db.proverbs_db WHERE id = cq.number_of_q)
        END as question_text,
        -- جلب المعنى/الإجابة الصحيحة (Meaning) حسب الجدول
        CASE 
            WHEN cq.type_of_q = 1 THEN (SELECT Meaning_of_term FROM if0_40458841_questions_db.words_db WHERE id = cq.number_of_q)
            WHEN cq.type_of_q = 2 THEN (SELECT Meaning_of_term FROM if0_40458841_questions_db.phrases_db WHERE id = cq.number_of_q)
            WHEN cq.type_of_q = 3 THEN (SELECT Meaning_of_term FROM if0_40458841_questions_db.proverbs_db WHERE id = cq.number_of_q)
        END as correct_meaning
    FROM if0_40458841_projects.annotations a
    JOIN if0_40458841_projects.cards_questions cq ON (a.question_id = cq.number_of_q AND a.project_id = cq.card_id)
    WHERE a.user_id = $user_id AND a.project_id = $card_id
";

$details = $conn->query($sql_details);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحليل الطالب | <?= htmlspecialchars($user['username']) ?></title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/admin_user_analysis.css">
     <link rel="icon" type="image/png" href="Favicon.png" />

</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        
        <div class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <h1 style="margin:0; font-size: 24px;">تحليل الأداء</h1>
            </div>
            <a href="admin_card_users.php?card_id=<?= $card_id ?>" class="back-link">
                <i class='bx bx-left-arrow-alt'></i> عودة للقائمة
            </a>
        </div>

        <div class="profile-section">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['username']) ?></h2>
                <p><?= htmlspecialchars($user['email']) ?> &bull; المستوى <?= $user['level'] ?></p>
            </div>
            <div class="card-tag">
                <i class='bx bxs-folder-open'></i> <?= htmlspecialchars($card['card_name']) ?>
            </div>
        </div>

        <div class="analysis-grid">
            
            <div class="box">
                <h3><i class='bx bx-list-ul'></i> سجل الإجابات التفصيلي</h3>
                <div style="overflow-x: auto;">
                    <table class="details-table">
                        <thead>
                            <tr>
                                <th width="50%">السؤال & المعنى</th>
                                <th width="30%">إجابة الطالب</th>
                                <th width="20%">النتيجة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($details && $details->num_rows > 0): ?>
                                <?php while($d = $details->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="question-text"><?= htmlspecialchars($d['question_text'] ?? 'غير متوفر') ?></span>
                                        <span class="meaning-text"><?= htmlspecialchars($d['correct_meaning'] ?? '') ?></span>
                                    </td>
                                    <td style="color: <?= $d['score'] ? '#34d399' : '#f87171' ?>">
                                        <?= htmlspecialchars($d['user_answer']) ?>
                                    </td>
                                    <td>
                                        <?php if($d['score'] == 1): ?>
                                            <span class="status-badge status-correct">صحيح</span>
                                        <?php else: ?>
                                            <span class="status-badge status-wrong">خاطئ</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align:center; padding: 20px;">لا توجد إجابات مسجلة لهذا الكارد.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box">
                <h3><i class='bx bx-pie-chart-alt-2'></i> ملخص النتيجة</h3>
                <div class="chart-wrapper">
                    <div id="chartDonut"></div>
                    </div>
                
                <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 12px; text-align: center;">
                        <span style="display:block; color: #10b981; font-weight:bold; font-size: 20px;"><?= $correct ?></span>
                        <span style="font-size: 12px; color: var(--text-muted);">إجابة صحيحة</span>
                    </div>
                    <div style="background: rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 12px; text-align: center;">
                        <span style="display:block; color: #ef4444; font-weight:bold; font-size: 20px;"><?= $wrong ?></span>
                        <span style="font-size: 12px; color: var(--text-muted);">إجابة خاطئة</span>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <script>
        var options = {
            series: [<?= $correct ?>, <?= $wrong ?>],
            labels: ['صحيحة', 'خاطئة'],
            chart: { type: 'donut', height: 280, background: 'transparent' },
            colors: ['#10b981', '#ef4444'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
            stroke: { show: false },
            plotOptions: { 
                pie: { 
                    donut: { 
                        size: '75%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: {
                                show: true,
                                fontSize: '28px',
                                fontWeight: 'bold',
                                color: '#ffffff',
                                formatter: function (val) { return val }
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'النسبة',
                                fontSize: '14px',
                                color: '#94a3b8',
                                formatter: function (w) {
                                    return "<?= $percent ?>%"
                                }
                            }
                        }
                    } 
                } 
            }
        };
        new ApexCharts(document.querySelector("#chartDonut"), options).render();
    </script>

</body>
</html>

</html>


