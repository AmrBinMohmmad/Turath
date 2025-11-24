<?php
include "db_qs.php";
include "question_parser.php";

$query = "SELECT * FROM words_db ORDER BY RAND() LIMIT 10";
$result = $conn_qs->query($query);

$questions = [];

while ($row = $result->fetch_assoc()) {

    $all_types = [
        ["type" => "سؤال التعرف على المكان", "question" => $row["Location_Recognition_question"] ?? ""],
        ["type" => "السؤال الثقافي", "question" => $row["Cultural_Interpretation_question"] ?? ""],
        ["type" => "السؤال السياقي", "question" => $row["Contextual_Usage_question"] ?? ""],
        ["type" => "املأ الفراغ", "question" => $row["Fill_in_Blank_question"] ?? ""],
        ["type" => "صح أو خطأ", "question" => $row["True_False_question"] ?? ""],
        ["type" => "سؤال المعنى", "question" => $row["Meaning_question"] ?? ""]
    ];

    $picked = $all_types[array_rand($all_types)];

    if (trim($picked["question"]) === "") continue;

    $parsed = parse_question_text($picked["question"]);

    $questions[] = [
        "type" => $picked["type"],
        "data" => $parsed
    ];
}
?>
