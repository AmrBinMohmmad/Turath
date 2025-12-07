<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once __DIR__ . '/../../auth/auth_guard.php';
checkUser();


$host = "sql206.infinityfree.com";
$user = "if0_40458841";
$password = "PoweR135";
$database = "if0_40458841_projects"; // قاعدة بيانات المشاريع

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("DB Error");
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$region = isset($_GET['region']) ? $_GET['region'] : '';

$query = "SELECT * FROM cards WHERE 1";

if ($search !== "") {
    $s = $conn->real_escape_string($search);
    $query .= " AND card_name LIKE '%$s%'";
}

if ($region !== "") {
    $r = $conn->real_escape_string($region);
    $query .= " AND Dialect_type LIKE '%$r%'";
}

$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "<p style='padding:10px;'>لا توجد اختبارات مطابقة</p>";
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['name'] ?? 'ضيف';

// -------------------------------------------------------------------
// 1. جلب عدد المعرفات الأساسية (IDs) لكل كارد لحساب العدد الكلي الفعلي للأسئلة
// -------------------------------------------------------------------
// بما أن كل ID أساسي يولد 6 أسئلة، نحتاج إلى عدد الـ IDs في cards_questions
$base_ids_query = $conn->query("
    SELECT card_id, COUNT(number_of_q) AS base_id_count 
    FROM cards_questions 
    WHERE number_of_q IS NOT NULL
    GROUP BY card_id
");

$base_ids_count = [];
if ($base_ids_query) {
    while ($row = $base_ids_query->fetch_assoc()) {
        $base_ids_count[$row['card_id']] = (int) $row['base_id_count'];
    }
}

// -------------------------------------------------------------------
// 2. جلب جميع إجابات المستخدمين
// -------------------------------------------------------------------
$user_answers_query = $conn->query("
    SELECT user_id, project_id, COUNT(*) AS answered_count
    FROM annotations
    GROUP BY user_id, project_id
");

$user_progress_data = [];
if ($user_answers_query) {
    while ($row = $user_answers_query->fetch_assoc()) {
        $user_progress_data[$row['project_id']][$row['user_id']] = (int) $row['answered_count'];
    }
}

// -------------------------------------------------------------------
// 3. حساب المستخدمين الذين أكملوا الكارد (Completed Users) لكل كارد
// -------------------------------------------------------------------
$completed_users_count = [];

foreach ($user_progress_data as $card_id => $users) {
    $base_q_count = $base_ids_count[$card_id] ?? 0;
    // العدد الكلي الفعلي للأسئلة هو عدد المعرفات الأساسية (لأننا نختار سؤال واحد فقط لكل ID)
    $actual_total_q = $base_q_count; 
    
    $completed_count = 0;
    
    foreach ($users as $user => $answered_count) {
        // المستخدم يعتبر "مكملاً" إذا كان عدد إجاباته يساوي العدد الكلي الفعلي للأسئلة
        if ($answered_count === $actual_total_q && $actual_total_q > 0) {
            $completed_count++;
        }
    }
    $completed_users_count[$card_id] = $completed_count;
}


while ($p = $result->fetch_assoc()):
        $card_id = (int) $p['id'];
        $max_users = (int) $p['number_of_users'];
        
        // عدد المستخدمين الذين أكملوا الكارد
        $current_completed_users = $completed_users_count[$card_id] ?? 0;
        
        // -------------------------------------------------------------------
        // حساب العدد الكلي الفعلي للأسئلة (Total Questions)
        // -------------------------------------------------------------------
        $base_q_count = $base_ids_count[$card_id] ?? 0;
        // العدد الكلي الفعلي للأسئلة هو عدد المعرفات الأساسية (لأننا نختار سؤال واحد فقط لكل ID)
        $actual_total_q = $base_q_count; 
        
        // إذا لم يتم العثور على أي IDs أساسية، نستخدم القيمة المخزنة في جدول cards كخيار احتياطي
        if ($actual_total_q === 0) {
            $actual_total_q = (int) $p['number_of_question'];
        }
        
        // عدد الأسئلة التي أجاب عنها هذا المستخدم (المستخدم الحالي)
        $answered = $user_progress_data[$card_id][$user_id] ?? 0;

        // نسبة التقدم (تعتمد على العدد الكلي الفعلي)
        $progress = ($actual_total_q > 0) ? round(($answered / $actual_total_q) * 100) : 0;

        // -------------------------------------------------------------------
        // منطق زر البدء/المتابعة ومنع الدخول
        // -------------------------------------------------------------------
        
        // عدد الأماكن المتاحة = الحد الأقصى - عدد المستخدمين المكملين
        $available_slots = $max_users - $current_completed_users;
        
        // الكارد ممتلئ إذا لم يكن هناك أماكن متاحة (available_slots <= 0)
        // والمستخدم الحالي لم يبدأ بعد (answered === 0)
        $is_full = ($available_slots <= 0) && ($answered === 0);
        
        if ($is_full) {
            // الكارد ممتلئ والمستخدم لم يبدأ بعد
            $btn_label = 'ممتلئ';
            $btn_class = 'disabled-button';
            $btn_link = '#';
        } elseif ($answered === $actual_total_q && $actual_total_q > 0) {
            // المستخدم انتهى
            $btn_label = 'تم الانتهاء';
            $btn_class = 'finished-button';
            $btn_link = '#';
        } elseif ($answered > 0 && $answered < $actual_total_q) {
            // المستخدم بدأ ولم ينتهِ
            $btn_label = 'متابعة';
            $btn_class = 'quiz-button';
            $btn_link = "answer_card.php?id=$card_id";
        } else {
            // المستخدم لم يبدأ بعد والكارد غير ممتلئ
            $btn_label = 'ابدأ الآن';
            $btn_class = 'quiz-button';
            $btn_link = "answer_card.php?id=$card_id";
        }
        
        // إذا كان العدد الكلي الفعلي للأسئلة هو 0، نعتبره غير متاح
        if ($actual_total_q === 0) {
            $btn_label = 'غير متاح';
            $btn_class = 'disabled-button';
            $btn_link = '#';
        }
        
        // -------------------------------------------------------------------
        // عرض الكارد
        // -------------------------------------------------------------------
        ?>
        <article class="quiz-card">
            <div class="quiz-card-header">
                <h3 class="quiz-title"><?= htmlspecialchars($p['card_name']) ?></h3>
                <span class="quiz-users">
                    عدد المستخدمين المكملين:
                    <strong><?= $current_completed_users ?></strong> / <?=$max_users?>
                </span>
            </div>

            <div class="quiz-meta">
                <span>مجموع الأسئلة: <strong><?= $actual_total_q ?></strong></span>
                <span>إجاباتك: <strong><?= $answered ?></strong> / <?= $actual_total_q ?></span>
            </div>

            <div class="quiz-progress">
                <div class="quiz-progress-top">
                    <span>مستوى تقدمك</span>
                    <span class="quiz-progress-value"><?= $progress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                </div>
            </div>

            <div class="quiz-actions">
                <a class="button <?= $btn_class ?>" href="<?= $btn_link ?>">
                    <?= $btn_label ?>
                </a>
            </div>
        </article>
    <?php endwhile; ?>

<style>
/* يجب إضافة هذه الأنماط إلى ملف CSS الخاص بك */
.disabled-button {
    background: #ccc !important;
    cursor: not-allowed !important;
    pointer-events: none;
}
.finished-button {
    background: #10b981 !important; /* لون أخضر للانتهاء */
    cursor: default !important;
    pointer-events: none;
}
</style>