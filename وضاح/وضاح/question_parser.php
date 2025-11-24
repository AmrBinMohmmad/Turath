<?php

function parse_question_text($text)
{
    $lines = explode("\n", $text);

    $task = "";
    $question = "";
    $options = [];
    $answer = "";
    $current = "";

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === "") continue;

        if (str_starts_with($line, "المهمة:")) {
            $task = trim(str_replace("المهمة:", "", $line));
            $current = "task";
        } elseif (str_starts_with($line, "السؤال:")) {
            $question = trim(str_replace("السؤال:", "", $line));
            $current = "question";
        } elseif (str_starts_with($line, "الخيارات:")) {
            $current = "options";
        } elseif (str_starts_with($line, "الإجابة الصحيحة:")) {
            $answer = trim(str_replace("الإجابة الصحيحة:", "", $line));
            $current = "answer";
        } else {
            if ($current === "options") {
                if (preg_match('/^\s*([أ-يA-Za-z0-9]+)\s*[\)\.\-]\s*(.+)$/u', $line, $m)) {
                    $key = trim($m[1]);
                    $val = trim($m[2]);
                    $options[$key] = $val;
                } else {
                    $options[] = $line;
                }
            }
        }
    }

    return [
        "task" => $task,
        "question" => $question,
        "options" => $options,
        "answer" => $answer
    ];
}
?>
