<?php

session_start();

require_once "config.php";


if (isset($_POST["bt1"])) {
    $type=$_POST['type'];
    $result=$conn->query("SELECT *  from words_db where Dialect_type='$type'");
    
    // while($row=$result->fetch_assoc()){
    //     echo "Term: " . $row["Term"] . "<br>";
    //     echo "Meaning_of_term: " . $row["Meaning_of_term"] . "<br>";
    //     echo "Dialect_type: " . $row["Dialect_type"] . "<br>";
    //     echo "Location_Recognition_question: " . $row["Location_Recognition_question"] . "<br>";
    //     echo "Cultural_Interpretation_question: " . $row["Cultural_Interpretation_question"] . "<br>";
    //     echo "Contextual_Usage_question: " . $row["Contextual_Usage_question"] . "<br>";
    //     echo "Fill_in_Blank_question: " . $row["Fill_in_Blank_question"] . "<br>";
    //     echo "True_False_question: " . $row["True_False_question"] . "<br>";
    //     echo "Meaning_question: " . $row["Meaning_question"] . "<br>";
    //     echo "<hr>";
    // }
$text = "المهمة: املأ الفراغ بالكلمة المناسبة.
السؤال: بعد أن أنهيت دراستي، قلت لأصدقائي: 'أنا _____ من الدراسة.'
الخيارات:
أ) مشغول
ب) آزيت
ج) متعب
د) سعيد
الإجابة الصحيحة: ب";

// تقسيم النص حسب الأسطر
$lines = explode("\n", $text);

// استخراج البيانات
$task = trim(str_replace("المهمة:", "", $lines[0]));
$question = trim(str_replace("السؤال:", "", $lines[1]));

// خيارات
$options = [];
for($i = 3; $i <= 6; $i++){
    $parts = explode(")", $lines[$i], 2);
    $options[trim($parts[0])] = trim($parts[1]);
}

// الإجابة الصحيحة
$answer = trim(str_replace("الإجابة الصحيحة:", "", $lines[7]));

// طباعة السؤال بشكل منسق
echo "مهمة: $task\n\n";
echo "<br/>";
echo "السؤال:\n$question\n\n";
echo "<br/>";
echo "الخيارات:\n";
foreach($options as $key => $value){
    echo "$key) $value\n";
    echo "<br/>";
}

echo "\n(الإجابة الصحيحة مخفية، تظهر بعد الحل)";

}
