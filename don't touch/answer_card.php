<?php
require_once 'auth_guard.php';
checkUser();


// إعدادات الاتصال بقاعدة البيانات
$host = "sql206.infinityfree.com";
$user = "if0_40458841";
$password = "PoweR135";
$database = "if0_40458841_questions_db";

$project_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 1;

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// دالة لتأمين المخرجات
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * دالة تحليل النص المحسنة
 */
function parse_question_text($text)
{
    $text = str_replace('*', '', $text);
    $text = trim($text);

    $mission = "";
    $question = "";
    $options = [];
    $answer = "";

    if (preg_match('/المهمة\s*:\s*(.*?)\s*(?=السؤال)/usi', $text, $m)) { $mission = trim($m[1]); }
    if (preg_match('/السؤال\s*:\s*(.*?)\s*(?=الخيارات)/usi', $text, $m)) { $question = trim($m[1]); }

    $optionsBlock = "";
    if (preg_match('/الخيارات\s*:\s*(.*?)\s*(?=الإجابة|الجواب)/usi', $text, $m)) { $optionsBlock = trim($m[1]); }

    if (!empty($optionsBlock)) {
        $lines = preg_split('/\r\n|\r|\n/', $optionsBlock);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            if (preg_match('/^([A-Zأ-ي0-9]+)\s*[\)\.\-]\s*(.*)$/iu', $line, $optMatch)) {
                $key = trim($optMatch[1]);
                $val = trim($optMatch[2]);
                $options[$key] = $val;
            }
        }
    }

    if (preg_match('/(?:الإجابة الصحيحة|الجواب الصحيح|الجواب)\s*:\s*(.*)/ui', $text, $m)) {
        $rawAnswer = trim($m[1]);
        if (preg_match('/^([A-Zأ-ي0-9]+)/iu', $rawAnswer, $ansMatch)) {
            $answer = mb_strtoupper(trim($ansMatch[1])); 
        } else {
            $answer = $rawAnswer;
        }
    }

    // تطابق اللغة (A vs أ)
    if (!empty($options) && !empty($answer)) {
        if (!array_key_exists($answer, $options)) {
            $map = ['A' => 'أ', 'B' => 'ب', 'C' => 'ج', 'D' => 'د', 'أ' => 'A', 'ب' => 'B', 'ج' => 'C', 'د' => 'D'];
            if (isset($map[$answer]) && array_key_exists($map[$answer], $options)) {
                $answer = $map[$answer];
            }
        }
    }

    return ["mission" => $mission, "question" => $question, "options" => $options, "answer" => $answer];
}

// مصفوفة الجداول
$table_map = [1 => "words_db", 2 => "phrases_db", 3 => "proverbs_db"];
$question_columns = ["Location_Recognition_question", "Cultural_Interpretation_question", "Contextual_Usage_question", "Fill_in_Blank_question", "True_False_question", "Meaning_question"];

// جلب الأسئلة
$q_ids_result = $conn->query("SELECT number_of_q, type_of_q FROM if0_40458841_projects.cards_questions WHERE card_id = $project_id AND number_of_q IS NOT NULL");

$all_questions = [];
$q_data = []; 

while ($row = $q_ids_result->fetch_assoc()) {
    $q_data[] = ['qid' => intval($row['number_of_q']), 'type' => intval($row['type_of_q'])];
}

if (count($q_data) == 0) { die("لا يوجد أسئلة مرتبطة بهذه البطاقة."); }

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
if ($total_q == 0) { die("لم يتم العثور على أي سؤال صالح للعرض."); }

// جلب التقدم
$stmt = $conn->prepare("SELECT COUNT(*) AS answered_count FROM if0_40458841_projects.annotations WHERE user_id = ? AND project_id = ?");
$stmt->bind_param("ii", $user_id, $project_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$q_index = intval($row['answered_count']);
$stmt->close();

$is_completed = ($q_index >= $total_q);
if ($is_completed) { $q_index = $total_q; }

// معالجة الإجابة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_completed) {
    $current_question_data = $all_questions[$q_index];
    $user_answer_key = trim($_POST['answer'] ?? '');
    $correct_answer_key = trim($current_question_data['answer']); 
    
    $score = (strcasecmp($user_answer_key, $correct_answer_key) === 0) ? 1 : 0;
    
    $base_question_id = $q_data[$q_index]['qid'];
    
    $stmt = $conn->prepare("INSERT INTO if0_40458841_projects.annotations (user_id, question_id, project_id, answer, score) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisi", $user_id, $base_question_id, $project_id, $user_answer_key, $score);
    $stmt->execute();
    $stmt->close();

    // تحديث نقاط الخبرة XP عند الإجابة الصحيحة
    if ($score == 1) {
        $conn->query("UPDATE if0_40458841_users_db.users SET xp = xp + 10 WHERE id = $user_id");
    }

    header("Location: ".$_SERVER['PHP_SELF']."?id=".$project_id);
    exit;
}

if (!$is_completed) { $parsed = $all_questions[$q_index]; }
$progress = ($total_q > 0) ? round(($q_index / $total_q) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحدي | تراث المملكة</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-dark: #020617; --card-dark: #1e293b; --text-main: #f8fafc; --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 255, 255, 0.05);
            --saudi-green: #006C35; --gold-light: #F2D06B; --gold-dark: #C69320;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; transition: all 0.3s ease; }
        
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle at 50% 0%, rgba(0, 108, 53, 0.15), transparent 60%);
        }

        .container {
            width: 100%; max-width: 700px; padding: 20px;
        }

        .question-card {
            background: var(--card-dark);
            border-radius: 25px;
            padding: 40px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }

        /* رأس الكارد */
        .card-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .q-counter { font-size: 14px; color: var(--text-muted); font-weight: bold; }
        .q-counter span { color: var(--gold-light); font-size: 18px; }

        /* شريط التقدم */
        .progress-track {
            width: 100%; height: 8px; background: rgba(255,255,255,0.05);
            border-radius: 10px; margin-bottom: 30px; overflow: hidden;
        }
        .progress-fill {
            height: 100%; background: linear-gradient(90deg, var(--gold-dark), var(--gold-light));
            width: <?= $progress ?>%; border-radius: 10px; transition: width 0.5s ease;
        }

        /* السؤال والمهمة */
        .mission-text {
            color: var(--saudi-green); font-size: 14px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; display: block;
        }
        .question-text {
            font-size: 24px; font-weight: 700; line-height: 1.5;
            color: var(--text-main); margin-bottom: 30px;
        }

        /* الخيارات */
        .options-list { display: flex; flex-direction: column; gap: 15px; }
        
        .option-label {
            display: flex; align-items: center; padding: 15px 20px;
            background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);
            border-radius: 15px; cursor: pointer; transition: 0.2s; position: relative;
        }
        .option-label:hover { background: rgba(255,255,255,0.05); border-color: var(--gold-light); }
        
        /* إخفاء الراديو الافتراضي */
        .option-label input[type="radio"] { display: none; }
        
        /* ستايل عند الاختيار */
        .option-label:has(input:checked) {
            background: rgba(242, 208, 107, 0.1);
            border-color: var(--gold-light);
            box-shadow: 0 0 15px rgba(242, 208, 107, 0.1);
        }
        
        .opt-key {
            width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 14px; margin-left: 15px; color: var(--text-muted);
            transition: 0.2s;
        }
        
        /* تغيير لون الدائرة عند الاختيار */
        .option-label input:checked + .opt-key {
            background: var(--gold-light); color: #000;
        }

        .opt-text { font-size: 16px; font-weight: 500; }

        /* الأزرار */
        .actions { margin-top: 40px; display: flex; gap: 15px; }
        
        .btn-submit {
            flex: 2; background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            color: #000; padding: 15px; border: none; border-radius: 12px;
            font-weight: bold; font-size: 16px; cursor: pointer;
            transition: 0.3s;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(198, 147, 32, 0.3); }

        .btn-exit {
            flex: 1; background: transparent; border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444; padding: 15px; border-radius: 12px;
            font-weight: bold; font-size: 16px; text-decoration: none; text-align: center;
            display: flex; align-items: center; justify-content: center; gap: 5px;
        }
        .btn-exit:hover { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; }

        /* شاشة النهاية */
        .completion-box { text-align: center; padding: 40px 0; }
        .completion-icon { font-size: 80px; color: var(--gold-light); margin-bottom: 20px; animation: bounce 2s infinite; }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }

        @media (max-width: 600px) {
            .question-text { font-size: 20px; }
            .option-label { padding: 12px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="question-card">
        
        <div class="card-header">
            <div class="q-counter">سؤال <span><?= $q_index + 1 ?></span> من <?= $total_q ?></div>
            <div style="font-size:12px; color:var(--text-muted);"><?= $progress ?>%</div>
        </div>
        <div class="progress-track">
            <div class="progress-fill"></div>
        </div>

        <?php if ($is_completed): ?>
            <div class="completion-box">
                <i class='bx bxs-trophy completion-icon'></i>
                <h2 style="color:var(--text-main); margin-bottom:10px;">أحسنت يا بطل!</h2>
                <p style="color:var(--text-muted); margin-bottom:30px;">لقد أتممت جميع تحديات هذا الكارد بنجاح.</p>
                
                <a href="regions.php" class="btn-submit" style="display:inline-block; width:100%; text-decoration:none;">
                    العودة للتحديات
                </a>
            </div>
        <?php else: ?>
            <form method="post">
                <?php if(!empty($parsed['mission'])): ?>
                    <span class="mission-text"><i class='bx bx-target-lock'></i> <?= e($parsed['mission']) ?></span>
                <?php endif; ?>

                <div class="question-text"><?= e($parsed['question']) ?></div>

                <div class="options-list">
                    <?php if (!empty($parsed['options'])): ?>
                        <?php foreach($parsed['options'] as $key => $opt): ?>
                            <label class="option-label">
                                <input type="radio" name="answer" value="<?= e($key) ?>" required>
                                <span class="opt-key"><?= e($key) ?></span>
                                <span class="opt-text"><?= e($opt) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#ef4444;">لا توجد خيارات متاحة لهذا السؤال.</p>
                    <?php endif; ?>
                </div>

                <div class="actions">
                    <button class="btn-submit" type="submit">تأكيد الإجابة</button>
                    <a class="btn-exit" href="regions.php">خروج <i class='bx bx-log-out'></i></a>
                </div>
            </form>
        <?php endif; ?>

    </div>
</div>

</body>
</html>