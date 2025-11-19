<?php

session_start();

$host="localhost";
$user="root";
$password="";
$database="questions_db";

$conn=new mysqli($host,$user,$password,$database);

if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}


function separat_text($array_q)
{

    foreach ($array_q as $i) {
        $text = $i;

        // تحويل النص إلى أسطر
        $lines = explode("\n", $text);

        // متغيرات لتخزين البيانات
        $task = $question = $answer = "";
        $options = [];
        $current_section = "";

        // المرور على كل سطر
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, "المهمة:")) {
                $current_section = "task";
                $task = trim(str_replace("المهمة:", "", $line));
            } elseif (str_starts_with($line, "السؤال:")) {
                $current_section = "question";
                $question = trim(str_replace("السؤال:", "", $line));
            } elseif (str_starts_with($line, "الخيارات:")) {
                $current_section = "options";
            } elseif (str_starts_with($line, "الإجابة الصحيحة:")) {
                $current_section = "answer";
                $answer = trim(str_replace("الإجابة الصحيحة:", "", $line));
            } else {
                // إذا كنا في قسم الخيارات، نضيف كل سطر كخيار
                if ($current_section === "options" && !empty($line)) {
                    $parts = explode(")", $line, 2);
                    if (count($parts) == 2) {
                        $options[trim($parts[0])] = trim($parts[1]);
                    }
                }
            }
        }

        // طباعة منسقة
        echo "=== المهمة ===\n$task\n\n";
        echo "<br/>";
        echo "=== السؤال ===\n$question\n\n";
        echo "<br/>";
        echo "=== الخيارات ===\n";
        echo "<br/>";
        foreach ($options as $key => $value) {
            echo "$key) $value\n";
            echo "<br/>";
        }
        echo "\n=== الإجابة الصحيحة ===\n$answer";
        echo "<br/>";
    }
}

if (isset($_POST["bt1"])) {
    $type = $_POST['type'];
    $result = $conn->query("SELECT *  from words_db where Dialect_type='$type'");
    $row = $result->fetch_assoc();


    while ($row = $result->fetch_assoc()) {
        separat_text($row);
        echo "-----------------------------------";
    }


}


