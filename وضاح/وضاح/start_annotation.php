<?php
session_start();
error_reporting(E_ALL);

include "fetch_questions.php";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>اختبار تفاعلي</title>
<style>
body { font-family: Tahoma; background:#f5f5f5; margin:20px; }
.q-box { background:white; padding:15px; margin-bottom:15px; border-radius:8px; box-shadow:0 0 5px #ccc; }
button { padding:6px 12px; border:none; border-radius:5px; cursor:pointer; }
button:hover { opacity:0.9; }
.correct { color:green; font-weight:bold; }
.wrong { color:red; font-weight:bold; }
</style>
</head>
<body>

<h2>الاختبار التفاعلي</h2>

<form id="quizForm">

<?php $counter=1; ?>
<?php foreach ($questions as $q): 
    $data = $q['data']; 
?>
<div class="q-box" id="question<?= $counter ?>" style="<?= $counter==1?'':'display:none;' ?>">

    <strong>سؤال رقم <?= $counter ?></strong><br>
    <strong>نوع السؤال:</strong> <?= htmlspecialchars($q['type']) ?><br><br>

    <?php if($data['task']!=""): ?>
        <strong>المهمة:</strong> <?= htmlspecialchars($data['task']) ?><br><br>
    <?php endif; ?>

    <strong>السؤال:</strong> <?= nl2br(htmlspecialchars($data['question'])) ?><br><br>

    <?php foreach($data['options'] as $k=>$v): ?>
        <label>
            <input type="radio" name="q<?= $counter ?>" value="<?= htmlspecialchars($k) ?>">
            <?= htmlspecialchars($k) ?>) <?= htmlspecialchars($v) ?>
        </label><br>
    <?php endforeach; ?>

    <input type="hidden" name="ans_q<?= $counter ?>" value="<?= htmlspecialchars($data['answer']) ?>">

    <div style="margin-top:10px;">
        <?php if ($counter > 1): ?>
            <button type="button" onclick="showQuestion(<?= $counter-1 ?>)">السابق</button>
        <?php endif; ?>

        <?php if ($counter < count($questions)): ?>
            <button type="button" onclick="showQuestion(<?= $counter+1 ?>)">التالي</button>
        <?php endif; ?>

        <?php if ($counter == count($questions)): ?>
            <button type="button" onclick="checkQuiz()">تقييم الاختبار</button>
        <?php endif; ?>
    </div>

</div>
<?php $counter++; endforeach; ?>

</form>

<div id="result" style="margin-top:20px; font-size:18px;"></div>

<script>
function showQuestion(num){
    const total = <?= count($questions) ?>;

    for(let i = 1; i <= total; i++){
        document.getElementById("question"+i).style.display = "none";
    }

    document.getElementById("question"+num).style.display = "block";
    window.scrollTo(0, 0);
}

function checkQuiz() {
    let form = document.getElementById('quizForm');
    let total = <?= count($questions) ?>;
    let score = 0;
    let resultHTML = "";

    for(let i=1;i<=total;i++){
        let radios = form['q'+i];
        let userAnswer = "";

        if(radios){
            if(radios.length === undefined){
                if(radios.checked) userAnswer = radios.value;
            } else {
                for(let r of radios){
                    if(r.checked){
                        userAnswer = r.value;
                        break;
                    }
                }
            }
        }

        let correctAnswer = form['ans_q'+i].value;

        if(userAnswer === correctAnswer){
            score++;
            resultHTML += "سؤال " + i + ": <span class='correct'>صحيح</span><br>";
        } else {
            resultHTML += "سؤال " + i + ": <span class='wrong'>خطأ</span> | الإجابة الصحيحة: " + correctAnswer + "<br>";
        }
    }

    resultHTML = "<strong>الدرجة النهائية: "+score+" / "+total+"</strong><br><br>" + resultHTML;
    document.getElementById('result').innerHTML = resultHTML;
    window.scrollTo(0, document.body.scrollHeight);
}
</script>

</body>
</html>
