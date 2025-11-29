<?php
session_start();

// يجب التأكد من أن هذه البيانات صحيحة للاتصال بقاعدة البيانات الخاصة بالأسئلة
$host = "localhost";
$user = "root";
$password = "";
$database = "questions_db"; // قاعدة البيانات التي تحتوي على جداول الأسئلة (phrases_db, words_db, proverbs_db)

$project_id = intval($_GET['id'] ?? 0);

// يجب أن يكون لديك طريقة لمعرفة هوية المستخدم الحالي.
// سنفترض أن هوية المستخدم مخزنة في $_SESSION['user_id']
// إذا لم يكن لديك نظام تسجيل دخول، يجب عليك إضافة واحد.
$user_id = $_SESSION['user_id'] ?? 1; // استخدام 1 كقيمة افتراضية إذا لم يكن هناك نظام تسجيل دخول

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function parse_question_text($text)
{
    $lines = explode("\n", $text);
    $mission = $question = $answer = "";
    $options = [];
    $current = "";

    foreach ($lines as $line) {
        $line = trim($line);

        if (str_starts_with($line, "المهمة:")) {
            $current = "mission";
            $mission = trim(str_replace("المهمة:", "", $line));

        } elseif (str_starts_with($line, "السؤال:")) {
            $current = "question";
            $question = trim(str_replace("السؤال:", "", $line));

        } elseif (str_starts_with($line, "الخيارات:")) {
            $current = "options";

        } elseif (str_starts_with($line, "الإجابة الصحيحة:")) {
            $current = "answer";
            $answer = trim(str_replace("الإجابة الصحيحة:", "", $line));

        } else {
            if ($current === "options" && !empty($line)) {
                $parts = explode(")", $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim(trim($parts[1]));
                    $options[$key] = $value;
                }
            }
        }
    }

    return [
        "mission"  => $mission,
        "question" => $question,
        "options"  => $options,
        "answer"   => $answer
    ];
}

// مصفوفة ربط type_of_q باسم الجدول
$table_map = [
    1 => "words_db",
    2 => "phrases_db",
    3 => "proverbs_db"
];

$question_columns = [
    "Location_Recognition_question",
    "Cultural_Interpretation_question",
    "Contextual_Usage_question",
    "Fill_in_Blank_question",
    "True_False_question",
    "Meaning_question"
];

// جلب IDs الأسئلة ونوع الجدول من جدول cards_questions
$q_ids_result = $conn->query("
    SELECT number_of_q, type_of_q 
    FROM projects.cards_questions
    WHERE card_id = $project_id AND number_of_q IS NOT NULL
");

$all_questions = [];
$q_data = []; // لتخزين الـ ID ونوع الجدول معاً

while ($row = $q_ids_result->fetch_assoc()) {
    $q_data[] = [
        'qid' => intval($row['number_of_q']),
        'type' => intval($row['type_of_q'])
    ];
}

if (count($q_data) == 0) {
    die("لا يوجد أسئلة مرتبطة بهذه البطاقة.");
}

// -------------------------------------------------------------------
// منطق تجميع الأسئلة: اختيار عمود واحد بالتتابع لكل ID
// -------------------------------------------------------------------
$column_count = count($question_columns);
$question_index = 0; // فهرس السؤال الكلي (من 0 إلى 19)

foreach ($q_data as $item) {
    $qid = $item['qid'];
    $type = $item['type'];
    
    // تحديد اسم الجدول بناءً على type_of_q
    $table = $table_map[$type] ?? null;

    if ($table) {
        // تحديد اسم العمود الذي سيتم اختياره بناءً على فهرس السؤال الكلي
        // (question_index % column_count) يعطينا فهرس العمود (من 0 إلى 5)
        $col_index = $question_index % $column_count;
        $selected_column = $question_columns[$col_index];
        
        // الاستعلام عن السؤال في الجدول الصحيح واختيار العمود المحدد فقط
        $q = $conn->query("SELECT $selected_column FROM $table WHERE id = $qid LIMIT 1");

        if ($q && $q->num_rows > 0) {
            $data = $q->fetch_assoc();

            // إضافة السؤال المحدد فقط إلى مصفوفة الأسئلة
            if (!empty($data[$selected_column])) {
                $all_questions[] = parse_question_text($data[$selected_column]);
            }
        }
    }
    
    // زيادة فهرس السؤال الكلي للانتقال للعمود التالي في التكرار القادم
    $question_index++;
}

$total_q = count($all_questions);
if ($total_q == 0) {
    die("لم يتم العثور على أي سؤال في الجداول الثلاثة.");
}

// -------------------------------------------------------------------
// 1. جلب الفهرس الحالي من جدول annotations
// -------------------------------------------------------------------
$stmt = $conn->prepare("
    SELECT COUNT(*) AS answered_count 
    FROM projects.annotations 
    WHERE user_id = ? AND project_id = ?
");
$stmt->bind_param("ii", $user_id, $project_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$q_index = intval($row['answered_count']); // عدد الإجابات هو الفهرس التالي
$stmt->close();

// -------------------------------------------------------------------
// 2. التحقق من الانتهاء من جميع الأسئلة
// -------------------------------------------------------------------
if ($q_index >= $total_q) {
    // تم الانتهاء من جميع الأسئلة، قم بعرض رسالة الانتهاء ومنع عرض النموذج
    $is_completed = true;
    $q_index = $total_q; // لضمان عرض 20/20
} else {
    $is_completed = false;
}

// -------------------------------------------------------------------
// 3. حفظ الإجابة في جدول annotations عند الانتقال للسؤال التالي
// -------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_completed) {
    
    $user_answer = $_POST['answer'] ?? '';
    
    // تحديد الـ ID الأساسي للسؤال الذي تمت الإجابة عليه
    // الـ ID الأساسي هو $q_data[$q_index]['qid']
    $base_question_id = $q_data[$q_index]['qid'];
    
    // **ملاحظة هامة:** يجب أن يكون question_id في جدول annotations فريداً لكل سؤال.
    // بما أننا نستخدم الـ ID الأساسي، يجب أن نضمن أن الـ question_id يعكس الـ ID الأساسي ونوع السؤال (1-6).
    // لتبسيط الأمر، سنستخدم الـ ID الأساسي كـ question_id مؤقت، مع العلم أن هذا قد يسبب مشاكل إذا أردت تتبع الإجابة على كل سؤال من الـ 6 أسئلة بشكل منفصل.
    
    $actual_question_id = $base_question_id; 
    
    // إدخال الإجابة في جدول annotations
    $stmt = $conn->prepare("
        INSERT INTO projects.annotations (user_id, question_id, project_id, answer) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiis", $user_id, $actual_question_id, $project_id, $user_answer);
    $stmt->execute();
    $stmt->close();

    // إعادة التوجيه لعرض السؤال الجديد
    header("Location: ".$_SERVER['PHP_SELF']."?id=".$project_id);
    exit;
}

// إذا لم يكن قد تم الانتهاء، يتم عرض السؤال الحالي
if (!$is_completed) {
    $parsed = $all_questions[$q_index];
}

$progress = round(($q_index / $total_q) * 100);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Answer Project</title>
<style>
body { font-family: Tahoma, sans-serif; background:#f5f5f5; direction:rtl; }
.container { max-width:700px; margin:40px auto; }
.card { background:white; padding:25px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
.small { font-size:14px; color:#64748b; margin-bottom:5px; }
.progress-bar { width:100%; height:10px; background:#e5e7eb; border-radius:5px; margin-bottom:15px; }
.progress-fill { height:100%; background:#2563eb; border-radius:5px; width:0%; transition:width 0.3s; }
.button { background:#2563eb; color:white; padding:10px 20px; border:none; border-radius:8px; cursor:pointer; text-decoration:none; }
.button:hover { background:#1d4ed8; }
.completion-message { text-align:center; padding:50px 0; font-size:24px; color:#10b981; font-weight:bold; }
</style>
</head>
<body>
<div class="container">
<div class="card">
    <h3>السؤال <?= $q_index ?> / <?= $total_q ?></h3>
    <div class="small">التقدم: <?= $progress ?>%</div>
    <div class="progress-bar"><div class="progress-fill" style="width:<?= $progress ?>%;"></div></div>
    <hr>

    <?php if ($is_completed): ?>
        <div class="completion-message">
            تهانينا! لقد أكملت جميع الأسئلة في هذا الكارد.
            <div style="margin-top:20px;">
                <a class="button" href="user_page.php" style="background:#64748b;">العودة لصفحة الكاردات</a>
            </div>
        </div>
    <?php else: ?>
        <form method="post">
            <?php if(!empty($parsed['mission'])): ?>
                <p style="font-size:18px; font-weight:bold; color:#0f172a; margin-bottom:15px;">
                    <?= e($parsed['mission']) ?>
                </p>
            <?php endif; ?>

            <h3 style="color:#1e3a8a; margin-bottom:15px;"><?= e($parsed['question']) ?></h3>

            <?php foreach($parsed['options'] as $key => $opt): ?>
                <label style="
                    display:block; 
                    margin-bottom:10px; 
                    padding:10px; 
                    background:#f9fafb; 
                    border-radius:8px; 
                    cursor:pointer;
                    border:1px solid #e5e7eb;
                ">
                    <input type="radio" name="answer" value="<?= e($key) ?>" required> 
                    <span style="margin-right:8px;"><?= e($key) ?>) <?= e($opt) ?></span>
                </label>
            <?php endforeach; ?>

            <div style="margin-top:20px;">
                <button class="button" type="submit">التالي</button>
                <a class="button" href="user_page.php" style="background:#64748b; margin-top:10px;">خروج</a>
            </div>
        </form>
    <?php endif; ?>
</div>
</div>
</body>
</html>
