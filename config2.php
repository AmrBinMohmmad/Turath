<?php

// config2.php

header('Content-Type: text/html; charset=utf-8');

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);



$host = "sql204.infinityfree.com";

$user = "if0_40419506";

$password = "Abmw123456789";

$database = "if0_40419506_projects";



// 1. اتصال قاعدة بيانات المشاريع

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {

    die("DB Connection failed: " . $conn->connect_error);

}



// إعدادات الترميز الصارمة

$conn->set_charset("utf8mb4");

$conn->query("SET NAMES 'utf8mb4'"); 

$conn->query("SET CHARACTER SET utf8mb4");

$conn->query("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");


// 2. اتصال قاعدة بيانات الأسئلة

$conn_qs = new mysqli($host, $user, $password, "if0_40419506_questions_db");

if ($conn_qs->connect_error) {

    die("Questions DB Connection failed: " . $conn_qs->connect_error);

}



$conn_qs->set_charset("utf8mb4");

$conn_qs->query("SET NAMES 'utf8mb4'");

$conn_qs->query("SET CHARACTER SET utf8mb4");

$conn_qs->query("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");



// --- دالة المعالجة الآمنة (Fix UTF-8 Errors) ---

function parseQuestionRow($dataString) {
    // 1. حماية أولية
    if (empty($dataString)) {
        return ['question' => '', 'options' => [], 'correct' => ''];
    }
    
    // 2. إصلاح الترميز (مهم جداً للغة العربية)
    $text = mb_convert_encoding($dataString, 'UTF-8', 'UTF-8');
    $text = trim($text);

    $question = $text;
    $options = [];
    $correct = '';

    // ---------------------------------------------------------
    // الخطوة 1: استخراج الإجابة الصحيحة (تنظيف النهاية)
    // ---------------------------------------------------------
    // نبحث عن "الإجابة الصحيحة" أو "Correct Answer" في آخر النص
    // النمط يدعم العربية والإنجليزية (مثال: الإجابة الصحيحة: A)
    if (preg_match('/(الإجابة الصحيحة|Correct Answer)[:\s\-\(]*([^\s\)]+).*$/ui', $text, $matches)) {
        // ننظف الإجابة من الأقواس إذا وجدت (مثلا (A تصبح A)
        $rawCorrect = trim($matches[2]);
        $correct = str_replace(['(', ')', '-', '[', ']'], '', $rawCorrect);
        
        // نحذف جملة الإجابة الصحيحة من النص الأصلي لكي لا تظهر مع الخيارات
        $text = preg_replace('/(الإجابة الصحيحة|Correct Answer)[:\s\-\(]*([^\s\)]+).*$/ui', '', $text);
        $text = trim($text);
    }

    // ---------------------------------------------------------
    // الخطوة 2: فصل السؤال عن الخيارات
    // ---------------------------------------------------------
    // نستخدم "الخيارات:" كفاصل قاطع
    $parts = preg_split('/(الخيارات|Choices|Options)[:\s]*/ui', $text);

    if (count($parts) > 1) {
        $question = trim($parts[0]);      // ما قبل كلمة "الخيارات" هو السؤال
        $optionsBlock = trim($parts[1]);  // ما بعدها هو كتلة الخيارات

        // ---------------------------------------------------------
        // الخطوة 3: تقسيم الخيارات (المنطق المطور)
        // ---------------------------------------------------------
        // هذا النمط يبحث عن:
        // 1. بداية سطر أو مسافة
        // 2. قوس مفتوح ( ثم حرف (مثل (A )
        // 3. أو حرف ثم قوس مغلق/شرطة (مثل A) أو A- )
        // يشمل العربية والإنجليزية (A-E)
        
        $pattern = '/(?:\s|^)(?:[\(\[][A-Ea-eأ-ي][\)\-\]]?|[A-Ea-eأ-ي][\)\-\]])/u';

        // نستخدم PREG_SPLIT_DELIM_CAPTURE للاحتفاظ برمز الخيار (A, B...)
        $splitOpts = preg_split($pattern, $optionsBlock, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
        
        // مصفوفة لتجميع النصوص
        $finalOptions = [];
        
        // استراتيجية التقسيم: preg_split أحياناً تحذف الفاصل، لذا سنستخدم match_all لاستخراج الفواصل والنصوص
        // الطريقة الأضمن: البحث عن كل "بداية خيار" وأخذ النص الذي يليه
        
        preg_match_all($pattern, $optionsBlock, $matches, PREG_OFFSET_CAPTURE);
        
        if (count($matches[0]) > 0) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                // $marker هو (A أو A)
                $marker = trim($matches[0][$i][0]);
                $startPos = $matches[0][$i][1] + strlen($matches[0][$i][0]);
                
                // تحديد نهاية النص الحالي (هي بداية الماركر التالي)
                if (isset($matches[0][$i+1])) {
                    $endPos = $matches[0][$i+1][1];
                    $length = $endPos - $startPos;
                    $optionText = substr($optionsBlock, $startPos, $length);
                } else {
                    // هذا آخر خيار
                    $optionText = substr($optionsBlock, $startPos);
                }
                
                $fullOption = $marker . " " . trim($optionText);
                $finalOptions[] = $fullOption;
            }
            $options = $finalOptions;
        } else {
            // فشل التقسيم بالRegex، نضع النص كما هو (حالة طوارئ)
            $options[] = $optionsBlock;
        }

    } elseif (strpos($text, '||') !== false) {
        // دعم الصيغة القديمة (||)
        $p = explode("||", $text);
        $question = trim($p[0]);
        for($k=1; $k<count($p)-1; $k++) { // -1 لكي لا نأخذ الإجابة كخيار
            if(!empty(trim($p[$k]))) $options[] = trim($p[$k]);
        }
        if(empty($correct)) $correct = trim(end($p));
    }

    return [
        'question' => $question,
        'options'  => $options,
        'correct'  => $correct
    ];
}


// دالة مساعدة لتأمين النصوص

function e($v) {

    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

}

?>
