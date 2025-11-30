<?php
ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

session_start();

// يجب التأكد من أن هذه البيانات صحيحة للاتصال بقاعدة البيانات الخاصة بالأسئلة
$host = "sql204.infinityfree.com";
$user = "if0_40419506";
$password = "Abmw123456789";
$database = "if0_40419506_questions_db"; // قاعدة البيانات التي تحتوي على جداول الأسئلة (phrases_db, words_db, proverbs_db)

$project_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 1;

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// دالة لتأمين المخرجات
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * دالة تحليل النص المحسنة
 * تعالج اختلاف اللغات بين الإجابة والخيارات (A -> أ)
 */
function parse_question_text($text)
{
    // 1. تنظيف النص
    $text = str_replace('*', '', $text);
    $text = trim($text);

    $mission = "";
    $question = "";
    $options = [];
    $answer = "";

    // 2. استخراج "المهمة"
    if (preg_match('/المهمة\s*:\s*(.*?)\s*(?=السؤال)/usi', $text, $m)) {
        $mission = trim($m[1]);
    }

    // 3. استخراج "السؤال"
    if (preg_match('/السؤال\s*:\s*(.*?)\s*(?=الخيارات)/usi', $text, $m)) {
        $question = trim($m[1]);
    }

    // 4. استخراج "الخيارات"
    $optionsBlock = "";
    if (preg_match('/الخيارات\s*:\s*(.*?)\s*(?=الإجابة|الجواب)/usi', $text, $m)) {
        $optionsBlock = trim($m[1]);
    }

    if (!empty($optionsBlock)) {
        $lines = preg_split('/\r\n|\r|\n/', $optionsBlock);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            // استخراج المفتاح (أ، ب، A، B) والنص
            if (preg_match('/^([A-Zأ-ي0-9]+)\s*[\)\.\-]\s*(.*)$/iu', $line, $optMatch)) {
                $key = trim($optMatch[1]);
                $val = trim($optMatch[2]);
                $options[$key] = $val;
            }
        }
    }

    // 5. استخراج "الإجابة الصحيحة" (الرمز فقط)
    if (preg_match('/(?:الإجابة الصحيحة|الجواب الصحيح|الجواب)\s*:\s*(.*)/ui', $text, $m)) {
        $rawAnswer = trim($m[1]);
        // نأخذ الحرف الأول فقط (مثلاً من "B (خطأ)" نأخذ "B")
        if (preg_match('/^([A-Zأ-ي0-9]+)/iu', $rawAnswer, $ansMatch)) {
            $answer = mb_strtoupper(trim($ansMatch[1])); 
        } else {
            $answer = $rawAnswer;
        }
    }

    // ---------------------------------------------------------
    // حل مشكلة اختلاف اللغة (A vs أ)
    // ---------------------------------------------------------
    if (!empty($options) && !empty($answer)) {
        // إذا كانت الإجابة المستخرجة غير موجودة في مفاتيح الخيارات
        if (!array_key_exists($answer, $options)) {
            // خريطة التحويل
            $map = [
                'A' => 'أ', 'B' => 'ب', 'C' => 'ج', 'D' => 'د',
                'أ' => 'A', 'ب' => 'B', 'ج' => 'C', 'د' => 'D'
            ];
            
            // إذا كانت الإجابة قابلة للتحويل، والمقابل لها موجود في الخيارات
            if (isset($map[$answer]) && array_key_exists($map[$answer], $options)) {
                $answer = $map[$answer]; // تحويل B إلى ب
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

// مصفوفة الجداول
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

// جلب الأسئلة
$q_ids_result = $conn->query("
    SELECT number_of_q, type_of_q 
    FROM if0_40419506_projects.cards_questions
    WHERE card_id = $project_id AND number_of_q IS NOT NULL
");

$all_questions = [];
$q_data = []; 

while ($row = $q_ids_result->fetch_assoc()) {
    $q_data[] = [
        'qid' => intval($row['number_of_q']),
        'type' => intval($row['type_of_q'])
    ];
}

if (count($q_data) == 0) {
    die("لا يوجد أسئلة مرتبطة بهذه البطاقة.");
}

$column_count = count($question_columns);
$question_index = 0;

foreach ($q_data as $item) {
    $qid = $item['qid'];
    $type = $item['type'];
    $table = $table_map[$type] ?? null;

    if ($table) {
        $col_index = $question_index % $column_count;
        $selected_column = $question_columns[$col_index];
        
        $q = $conn->query("SELECT $selected_column FROM $table WHERE id = $qid LIMIT 1");

        if ($q && $q->num_rows > 0) {
            $data = $q->fetch_assoc();
            if (!empty($data[$selected_column])) {
                $all_questions[] = parse_question_text($data[$selected_column]);
            }
        }
    }
    $question_index++;
}

$total_q = count($all_questions);
if ($total_q == 0) {
    die("لم يتم العثور على أي سؤال صالح للعرض.");
}

// جلب التقدم
$stmt = $conn->prepare("
    SELECT COUNT(*) AS answered_count 
    FROM projects.annotations 
    WHERE user_id = ? AND project_id = ?
");
$stmt->bind_param("ii", $user_id, $project_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$q_index = intval($row['answered_count']);
$stmt->close();

if ($q_index >= $total_q) {
    $is_completed = true;
    $q_index = $total_q;
} else {
    $is_completed = false;
}

// معالجة الإجابة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_completed) {
    
    $current_question_data = $all_questions[$q_index];
    $user_answer_key = trim($_POST['answer'] ?? '');
    
    // الإجابة الصحيحة الآن موحدة (إذا كانت الخيارات "ب"، فالإجابة ستكون "ب" حتى لو في الداتا B)
    $correct_answer_key = trim($current_question_data['answer']); 
    
    $score = 0;
    if (strcasecmp($user_answer_key, $correct_answer_key) === 0) {
        $score = 1;
    }
    
    $base_question_id = $q_data[$q_index]['qid'];
    
    $stmt = $conn->prepare("
        INSERT INTO projects.annotations (user_id, question_id, project_id, answer, score) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiisi", $user_id, $base_question_id, $project_id, $user_answer_key, $score);
    $stmt->execute();
    $stmt->close();

    header("Location: ".$_SERVER['PHP_SELF']."?id=".$project_id);
    exit;
}

if (!$is_completed) {
    $parsed = $all_questions[$q_index];
}

$progress = ($total_q > 0) ? round(($q_index / $total_q) * 100) : 0;
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
    <h3>السؤال <?= $q_index + 1 ?> / <?= $total_q ?></h3>
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

            <?php if (!empty($parsed['options'])): ?>
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
            <?php else: ?>
                <p style="color:red;">لا توجد خيارات متاحة لهذا السؤال.</p>
            <?php endif; ?>

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







