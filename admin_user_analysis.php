<?php
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        
        :root {
            --bg-dark: #0f172a; --card-dark: #1e293b; --text-main: #f1f5f9; --text-muted: #94a3b8;
            --accent-blue: #3b82f6; --accent-green: #10b981; --accent-red: #ef4444; --accent-orange: #f59e0b;
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

        /* تنسيقات السايدبار والمحتوى (نفس الملفات السابقة) */
        .sidebar { width: var(--sidebar-width); background: var(--card-dark); padding: 25px; display: flex; flex-direction: column; border-left: 1px solid rgba(255,255,255,0.05); position: fixed; height: 100%; right: 0; top: 0; z-index: 1000; transition: transform 0.3s ease; box-sizing: border-box; }
        .sidebar.close { transform: translateX(100%); }
        .nav-item { display: flex; align-items: center; gap: 15px; padding: 14px; color: var(--text-muted); text-decoration: none; border-radius: 12px; margin-bottom: 8px; transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background: rgba(59, 130, 246, 0.1); color: var(--accent-blue); }
        .logo { font-size: 24px; font-weight: 700; margin-bottom: 50px; display: block; color: var(--text-main); text-decoration: none; }

        .main-content {
            margin-right: var(--sidebar-width);
            padding: 40px;
            width: auto;
            transition: margin-right 0.3s ease;
            box-sizing: border-box;
        }
        .main-content.expand { margin-right: 0; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .menu-toggle { font-size: 32px; color: var(--text-main); cursor: pointer; margin-left: 15px; display: none; }
        .back-link { color: var(--text-muted); text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 5px; transition:0.3s; }
        .back-link:hover { color: var(--accent-blue); }

        /* بطاقة الطالب */
        .profile-section {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 20px;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }
        .profile-avatar {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; font-weight: bold; color: white;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .profile-info h2 { margin: 0; font-size: 24px; color: white; }
        .profile-info p { margin: 5px 0 0; opacity: 0.8; font-size: 14px; }
        .card-tag { 
            position: absolute; left: 30px; top: 30px; 
            background: rgba(0,0,0,0.3); padding: 8px 15px; 
            border-radius: 50px; font-size: 13px; backdrop-filter: blur(5px);
        }

        /* الشبكة */
        .analysis-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        
        .box { background: var(--card-dark); padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .box h3 { margin: 0 0 20px; font-size: 18px; color: var(--text-main); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; }

        /* الجدول */
        .details-table { width: 100%; border-collapse: collapse; }
        .details-table th { text-align: right; color: var(--text-muted); font-size: 13px; padding: 10px; }
        .details-table td { padding: 15px 10px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; vertical-align: top; }
        .question-text { font-weight: bold; color: white; display: block; margin-bottom: 5px; }
        .meaning-text { color: var(--text-muted); font-size: 12px; }
        
        /* حالة الإجابة */
        .status-badge { padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; display: inline-block; }
        .status-correct { background: rgba(16, 185, 129, 0.1); color: #34d399; }
        .status-wrong { background: rgba(239, 68, 68, 0.1); color: #f87171; }

        /* الدائرة */
        .chart-wrapper { text-align: center; position: relative; }
        .score-big { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 32px; font-weight: bold; color: white; }

        @media (max-width: 1100px) {
            .sidebar { transform: translateX(100%); right: 0; }
            .sidebar.active { transform: translateX(0); box-shadow: -10px 0 30px rgba(0,0,0,0.5); }
            .main-content { margin-right: 0; padding: 20px; }
            .menu-toggle { display: block; }
            .analysis-grid { grid-template-columns: 1fr; }
            .profile-section { flex-direction: column; text-align: center; }
            .card-tag { position: static; margin-top: 15px; display: inline-block; }
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        
        <div class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <h1 style="margin:0; font-size: 24px;">تحليل الأداء 📈</h1>
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