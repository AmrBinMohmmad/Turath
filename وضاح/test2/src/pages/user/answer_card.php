<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

// ... (كل كود PHP في بداية الملف يظل كما هو تماماً بما فيه دوال الـ API) ...
require_once __DIR__ . '/../../auth/auth_guard.php';
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
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * دالة تحليل النص المحسنة
 */
function parse_question_text($text, $term = "", $meaning = "")
{
    $text = str_replace('*', '', $text);
    $text = trim($text);

    $mission = "";
    $question = "";
    $options = [];
    $answer = "";

    if (preg_match('/المهمة\s*:\s*(.*?)\s*(?=السؤال)/usi', $text, $m)) {
        $mission = trim($m[1]);
    }
    if (preg_match('/السؤال\s*:\s*(.*?)\s*(?=الخيارات)/usi', $text, $m)) {
        $question = trim($m[1]);
    }

    $optionsBlock = "";
    if (preg_match('/الخيارات\s*:\s*(.*?)\s*(?=الإجابة|الجواب)/usi', $text, $m)) {
        $optionsBlock = trim($m[1]);
    }

    if (!empty($optionsBlock)) {
        $lines = preg_split('/\r\n|\r|\n/', $optionsBlock);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;
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

    return [
        "mission" => $mission, 
        "question" => $question, 
        "options" => $options, 
        "answer" => $answer,
        "term" => $term,       
        "meaning" => $meaning  
    ];
}

// مصفوفة الجداول
$table_map = [1 => "words_db", 2 => "phrases_db", 3 => "proverbs_db"];
$question_columns = ["Location_Recognition_question", "Cultural_Interpretation_question", "Contextual_Usage_question", "Fill_in_Blank_question", "True_False_question", "Meaning_question"];

// منطق جلب الأسئلة وتحديد السؤال الحالي
function getCurrentQuestionData($conn, $project_id, $user_id, $table_map, $question_columns) {
    // جلب أرقام الأسئلة
    $q_ids_result = $conn->query("SELECT number_of_q, type_of_q FROM if0_40458841_projects.cards_questions WHERE card_id = $project_id AND number_of_q IS NOT NULL");
    
    $q_data = [];
    while ($row = $q_ids_result->fetch_assoc()) {
        $q_data[] = ['qid' => intval($row['number_of_q']), 'type' => intval($row['type_of_q'])];
    }

    if (count($q_data) == 0) return null;

    // معرفة رقم السؤال الحالي
    $stmt = $conn->prepare("SELECT COUNT(*) AS answered_count FROM if0_40458841_projects.annotations WHERE user_id = ? AND project_id = ?");
    $stmt->bind_param("ii", $user_id, $project_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $q_index = intval($row['answered_count']);
    $stmt->close();

    $total_q = count($q_data);
    $is_completed = ($q_index >= $total_q);
    
    if ($is_completed) return ['is_completed' => true, 'total' => $total_q, 'progress' => 100];

    // جلب بيانات السؤال الحالي فقط
    $item = $q_data[$q_index];
    $qid = $item['qid'];
    $type = $item['type'];
    $table = $table_map[$type] ?? null;

    if ($table) {
        $column_count = count($question_columns);
        $col_index = $q_index % $column_count;
        $selected_column = $question_columns[$col_index];

        $q = $conn->query("SELECT $selected_column, Term, Meaning_of_term FROM $table WHERE id = $qid LIMIT 1");
        if ($q && $q->num_rows > 0) {
            $data = $q->fetch_assoc();
            if (!empty($data[$selected_column])) {
                $parsed = parse_question_text($data[$selected_column], $data['Term'] ?? '', $data['Meaning'] ?? '');
                return [
                    'is_completed' => false,
                    'parsed' => $parsed,
                    'q_index' => $q_index,
                    'total_q' => $total_q,
                    'qid_base' => $qid,
                    'type_base' => $type
                ];
            }
        }
    }
    return null;
}

// --- معالجة طلب AJAX للبوت ---
if (isset($_POST['action']) && $_POST['action'] === 'get_ai_hint') {
    header('Content-Type: application/json');
    
    $currentData = getCurrentQuestionData($conn, $project_id, $user_id, $table_map, $question_columns);
    
    if (!$currentData || $currentData['is_completed']) {
        echo json_encode(['error' => 'لا يوجد سؤال حالي.']);
        exit;
    }

    $term = $currentData['parsed']['term'] ?? 'N/A';
    $meaning = $currentData['parsed']['meaning'] ?? 'N/A';
    $question_text = $currentData['parsed']['question'] ?? 'N/A';
    
    // استخراج نص الإجابة الصحيحة لإرساله للبوت
    $correctKey = $currentData['parsed']['answer'];
    $correctText = $currentData['parsed']['options'][$correctKey] ?? 'غير محددة';

    $apiKey = "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"; 
    
    // تحديث البرومبت
    $prompt = "أنت مساعد ذكي لتطبيق تعليمي عن التراث السعودي. 
    المستخدم يحاول حل السؤال التالي: '$question_text'.
    المصطلح المرتبط: '$term'.
    المعنى: '$meaning'.
    
    معلومة سرية لك (لا تخبرها للمستخدم): الإجابة الصحيحة هي '$correctText'.
    
    المطلوب منك بدقة:
    1. قدم تلميحاً ذكياً ومختصراً جداً (سطر واحد) يوجه تفكير المستخدم نحو الإجابة الصحيحة '$correctText'.
    2. ممنوع منعاً باتاً ذكر الإجابة الصحيحة صراحة في التلميح.
    3. تأكد أن التلميح لا ينطبق على الخيارات الخاطئة.
    4. تحدث بلهجة سعودية بيضاء، ودودة ومشجعة.
    5. الرد يجب أن يكون باللغة العربية فقط (لا تستخدم أي أحرف إنجليزية).";

    $postData = [
        "model" => "deepseek/deepseek-chat", // DeepSeek V3
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ]
    ];

    $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json",
        "HTTP-Referer: " . $_SERVER['HTTP_HOST'], 
        "X-Title: Saudi Heritage Quiz"
    ]);

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo json_encode(['error' => 'خطأ اتصال: ' . curl_error($ch)]);
    } else {
        $json = json_decode($response, true);
        $hint = $json['choices'][0]['message']['content'] ?? 'لم يتوفر تلميح حالياً.';
        echo json_encode(['hint' => $hint]);
    }
    curl_close($ch);
    exit;
}

// --- العرض الأساسي للصفحة ---
$currentData = getCurrentQuestionData($conn, $project_id, $user_id, $table_map, $question_columns);

if (!$currentData) {
    die("لا يوجد أسئلة صالحة أو حدث خطأ في البيانات.");
}

$is_completed = $currentData['is_completed'];
$total_q = $is_completed ? $currentData['total'] : $currentData['total_q'];
$q_index = $is_completed ? $total_q : $currentData['q_index'];
$parsed = $is_completed ? [] : $currentData['parsed'];
$progress = ($total_q > 0) ? round(($q_index / $total_q) * 100) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_completed && !isset($_POST['action'])) {
    $user_answer_key = trim($_POST['answer'] ?? '');
    $correct_answer_key = trim($parsed['answer']);
    $score = (strcasecmp($user_answer_key, $correct_answer_key) === 0) ? 1 : 0;
    $base_question_id = $currentData['qid_base'];
    $stmt = $conn->prepare("INSERT INTO if0_40458841_projects.annotations (user_id, question_id, project_id, answer, score) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisi", $user_id, $base_question_id, $project_id, $user_answer_key, $score);
    $stmt->execute();
    $stmt->close();
    if ($score == 1) {
        $conn->query("UPDATE if0_40458841_users_db.users SET xp = xp + 10 WHERE id = $user_id");
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $project_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title">التحدي | تراث المملكة</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <style>
        /* ... نفس الستايل مع الوضع الفاتح ... */
        :root {
            --bg-dark: #020617;
            --card-dark: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.05);
            --saudi-green: #006C35;
            --gold-light: #F2D06B;
            --gold-dark: #C69320;
            --ai-color: #8b5cf6;
        }

        body.light-mode {
            --bg-dark: #f8fafc;
            --card-dark: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --glass-bg: #f1f5f9;
            --glass-border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: all 0.3s ease;
        }

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

        /* زر العودة والتحكم */
        .top-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--glass-border);
            background: var(--glass-bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            font-size: 18px;
        }

        .icon-btn:hover {
            background: var(--saudi-green);
            color: white;
        }

        .container {
            width: 100%;
            max-width: 700px;
            padding: 20px;
            margin-top: 60px;
        }

        .question-card {
            background: var(--card-dark);
            border-radius: 25px;
            padding: 40px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .q-counter {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: bold;
        }

        .q-counter span {
            color: var(--gold-light);
            font-size: 18px;
        }

        .progress-track {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            margin-bottom: 30px;
            overflow: hidden;
        }

        body.light-mode .progress-track {
            background: #e2e8f0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold-dark), var(--gold-light));
            width:
                <?= $progress ?>
                %;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .mission-text {
            color: var(--gold-light);
            font-size: 20px;
            font-weight: 800;
            line-height: 1.6;
            margin-bottom: 20px;
            display: block;
            border-bottom: 1px solid rgba(242, 208, 107, 0.2);
            padding-bottom: 15px;
        }

        body.light-mode .mission-text {
            color: #d97706;
            border-color: rgba(217, 119, 6, 0.2);
        }

        .question-text {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.5;
            color: var(--text-main);
            margin-bottom: 30px;
        }

        .options-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .option-label {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            cursor: pointer;
            transition: 0.2s;
            position: relative;
        }

        body.light-mode .option-label {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .option-label:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--gold-light);
        }

        body.light-mode .option-label:hover {
            background: #f1f5f9;
            border-color: #d97706;
        }

        .option-label input[type="radio"] {
            display: none;
        }

        .option-label:has(input:checked) {
            background: rgba(242, 208, 107, 0.1);
            border-color: var(--gold-light);
            box-shadow: 0 0 15px rgba(242, 208, 107, 0.1);
        }

        .opt-key {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin-left: 15px;
            color: var(--text-muted);
            transition: 0.2s;
        }

        body.light-mode .opt-key {
            background: #e2e8f0;
            color: #64748b;
        }

        body[dir="ltr"] .opt-key {
            margin-left: 0;
            margin-right: 15px;
        }

        .option-label input:checked+.opt-key {
            background: var(--gold-light);
            color: #000;
        }

        .opt-text {
            font-size: 16px;
            font-weight: 500;
        }

        .actions {
            margin-top: 40px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-submit {
            flex: 2;
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            color: #000;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            min-width: 150px;
        }

        .btn-exit {
            flex: 1;
            background: transparent;
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 15px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-width: 100px;
        }

        .btn-ai-hint {
            flex: 1;
            background: linear-gradient(135deg, #a78bfa, #7c3aed);
            color: #fff;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 120px;
        }

        .bot-response-box {
            margin-top: 20px;
            padding: 15px;
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 14px;
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .completion-box {
            text-align: center;
            padding: 40px 0;
        }

        .completion-icon {
            font-size: 80px;
            color: var(--gold-light);
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-20px);
            }

            60% {
                transform: translateY(-10px);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 600px) {
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <div class="top-controls">
        <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
        <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
    </div>

    <div class="container">
        <div class="question-card">
            <div class="card-header">
                <div class="q-counter"><span id="txt-q-num">سؤال</span> <span><?= $q_index + 1 ?></span> <span
                        id="txt-q-of">من</span> <?= $total_q ?></div>
                <div style="font-size:12px; color:var(--text-muted);"><?= $progress ?>%</div>
            </div>
            <div class="progress-track">
                <div class="progress-fill"></div>
            </div>

            <?php if ($is_completed): ?>
                <div class="completion-box">
                    <i class='bx bxs-trophy completion-icon'></i>
                    <h2 style="color:var(--text-main); margin-bottom:10px;" id="txt-welldone">أحسنت يا بطل!</h2>
                    <p style="color:var(--text-muted); margin-bottom:30px;" id="txt-completed-msg">لقد أتممت جميع تحديات هذا
                        الكارد بنجاح.</p>
                    <a href="regions.php" class="btn-submit" style="display:inline-block; width:100%; text-decoration:none;"
                        id="btn-back">العودة للتحديات</a>
                </div>
            <?php else: ?>
                <form method="post">
                    <?php if (!empty($parsed['mission'])): ?>
                        <span class="mission-text"><i class='bx bx-target-lock'></i> <?= e($parsed['mission']) ?></span>
                    <?php endif; ?>

                    <div class="question-text"><?= e($parsed['question']) ?></div>

                    <div class="options-list">
                        <?php if (!empty($parsed['options'])): ?>
                            <?php foreach ($parsed['options'] as $key => $opt): ?>
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

                    <div id="botResponse" class="bot-response-box"></div>

                    <div class="actions">
                        <button class="btn-submit" type="submit" id="btn-confirm">تأكيد الإجابة</button>

                        <button type="button" class="btn-ai-hint" id="askBotBtn">
                            <i class='bx bxs-bot'></i> <span id="txt-hint">تلميح ذكي</span>
                        </button>

                        <a class="btn-exit" href="regions.php"><span id="txt-exit">خروج</span> <i
                                class='bx bx-log-out'></i></a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const txt = {
            ar: {
                pageTitle: "التحدي | تراث المملكة",
                txtQNum: "سؤال", txtQOf: "من",
                btnConfirm: "تأكيد الإجابة", txtHint: "تلميح ذكي", txtExit: "خروج",
                txtWelldone: "أحسنت يا بطل!", txtCompletedMsg: "لقد أتممت جميع تحديات هذا الكارد بنجاح.", btnBack: "العودة للتحديات"
            },
            en: {
                pageTitle: "Challenge | Torath Kingdom",
                txtQNum: "Question", txtQOf: "of",
                btnConfirm: "Confirm Answer", txtHint: "Smart Hint", txtExit: "Exit",
                txtWelldone: "Well Done!", txtCompletedMsg: "You have successfully completed all challenges in this card.", btnBack: "Back to Challenges"
            }
        };

        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            localStorage.setItem('theme', document.body.classList.contains('light-mode') ? 'light' : 'dark');
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const icon = document.getElementById('theme-icon');
            if (document.body.classList.contains('light-mode')) icon.classList.replace('bx-sun', 'bx-moon');
            else icon.classList.replace('bx-moon', 'bx-sun');
        }

        function toggleLanguage() {
            const currentLang = localStorage.getItem('lang') || 'ar';
            const newLang = currentLang === 'ar' ? 'en' : 'ar';
            localStorage.setItem('lang', newLang);
            applyLanguage(newLang);
        }

        function applyLanguage(lang) {
            document.documentElement.lang = lang;
            document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
            document.getElementById('lang-btn').innerText = lang === 'ar' ? 'EN' : 'عربي';
            document.body.style.fontFamily = lang === 'en' ? "'Outfit', sans-serif" : "'Cairo', sans-serif";

            const t = txt[lang];
            // Safe Update
            if (document.getElementById('page-title')) document.getElementById('page-title').innerText = t.pageTitle;
            if (document.getElementById('txt-q-num')) document.getElementById('txt-q-num').innerText = t.txtQNum;
            if (document.getElementById('txt-q-of')) document.getElementById('txt-q-of').innerText = t.txtQOf;
            if (document.getElementById('btn-confirm')) document.getElementById('btn-confirm').innerText = t.btnConfirm;
            if (document.getElementById('txt-hint')) document.getElementById('txt-hint').innerText = t.txtHint;
            if (document.getElementById('txt-exit')) document.getElementById('txt-exit').innerText = t.txtExit;
            if (document.getElementById('txt-welldone')) document.getElementById('txt-welldone').innerText = t.txtWelldone;
            if (document.getElementById('txt-completed-msg')) document.getElementById('txt-completed-msg').innerText = t.txtCompletedMsg;
            if (document.getElementById('btn-back')) document.getElementById('btn-back').innerText = t.btnBack;
        }

        // Initial Load
        const storedTheme = localStorage.getItem('theme') || 'dark';
        if (storedTheme === 'light') document.body.classList.add('light-mode');
        updateThemeIcon();

        const storedLang = localStorage.getItem('lang') || 'ar';
        applyLanguage(storedLang);

        // AI Bot Logic (Same as before)
        document.addEventListener('DOMContentLoaded', function () {
            const botBtn = document.getElementById('askBotBtn');
            const responseBox = document.getElementById('botResponse');
            if (botBtn) {
                botBtn.addEventListener('click', function () {
                    botBtn.disabled = true;
                    // Hint logic text needs to adapt too? For now keep dynamic loader text simple or ignore
                    botBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> ...";

                    const formData = new FormData();
                    formData.append('action', 'get_ai_hint');

                    fetch(window.location.href, { method: 'POST', body: formData })
                        .then(response => response.json())
                        .then(data => {
                            responseBox.style.display = 'block';
                            if (data.error) {
                                responseBox.innerHTML = `<span style="color:#ef4444">${data.error}</span>`;
                            } else {
                                // AI Hint label
                                const hintLabel = (localStorage.getItem('lang') === 'en') ? "AI Hint:" : "تلميح المساعد:";
                                responseBox.innerHTML = `<strong><i class='bx bxs-bulb'></i> ${hintLabel}</strong> ${data.hint}`;
                            }
                        })
                        .catch(error => { console.error('Error:', error); })
                        .finally(() => {
                            const hintTxt = (localStorage.getItem('lang') === 'en') ? "Smart Hint" : "تلميح ذكي";
                            botBtn.innerHTML = `<i class='bx bxs-bot'></i> <span id="txt-hint">${hintTxt}</span>`;
                            botBtn.disabled = false;
                        });
                });
            }
        });
    </script>
</body>

</html>