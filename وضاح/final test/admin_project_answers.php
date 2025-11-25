<?php
// admin_project_answers.php
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config2.php";

$project_id = intval($_GET['id'] ?? 0);
$project = $conn->query("SELECT * FROM cards WHERE id=$project_id")->fetch_assoc();
if(!$project) die("Project not found");

// ---------------------------------------------------------------------------
// التعديل هنا: نطلب المستخدمين من قاعدة البيانات الثانية مباشرة
// نستخدم الصيغة: DatabaseName.TableName
// ---------------------------------------------------------------------------
$sql_users = "SELECT DISTINCT u.id, u.name 
              FROM annotations a 
              JOIN if0_40458841_users_db.users u ON u.id = a.user_id 
              WHERE a.project_id = $project_id 
              ORDER BY u.id ASC";

$users = $conn->query($sql_users);

// التحقق من وجود أخطاء في الاتصال بالقاعدة الثانية
if (!$users) {
    die("Error fetching users: " . $conn->error . "<br>Please check if both databases are on the same server.");
}

// كود حفظ الدرجات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_scores'])) {
    foreach ($_POST['scores'] as $annot_id => $score_val) {
        $clean_score = intval($score_val);
        $clean_id = intval($annot_id);
        $conn->query("UPDATE annotations SET score=$clean_score WHERE id=$clean_id");
    }
    echo "<script>alert('Grades Saved Successfully!'); window.location.href='admin_project_answers.php?id=$project_id';</script>";
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="utf-8">
<title>Grading Project</title>
<link rel="stylesheet" href="style.css">
<style>
    .correct-hint { color: #059669; font-size: 0.85rem; margin-top: 5px; }
    .ans-card { padding: 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #e5e7eb; background: #fff; }
    .ans-correct { background-color: #d1fae5; border-color: #34d399; }
    .ans-wrong { background-color: #fee2e2; border-color: #f87171; }
</style>
</head>
<body>
<div class="navbar container">
    <div class="header-title">Grading: <?= e($project['card_name']) ?></div>
    <a href="admin_page.php" class="button" style="width:auto;">Back to Dashboard</a>
</div>

<div class="container">
<?php if($users->num_rows == 0): ?>
    <p>No users have answered this project yet.</p>
<?php endif; ?>

<?php while($u = $users->fetch_assoc()): ?>
    <div class="card">
        <h3>User: <?= e($u['name']) ?></h3>
        <form method="post">
            <input type="hidden" name="save_scores" value="1">
            
            <?php
            // استعلام الأسئلة والإجابات
            // هنا ندمج بين:
            // 1. جدول الإجابات (في القاعدة الحالية)
            // 2. جدول الأسئلة (في قاعدة الأسئلة if0_40458841_questions_db)
            $sql = "SELECT a.id as annot_id, a.answer, a.score, 
                    COALESCE(
                        NULLIF(w.Meaning_question, ''),
                        NULLIF(w.Location_Recognition_question, ''),
                        NULLIF(w.Cultural_Interpretation_question, ''),
                        NULLIF(w.Contextual_Usage_question, ''),
                        NULLIF(w.Fill_in_Blank_question, ''),
                        NULLIF(w.True_False_question, '')
                    ) as question_data 
                    FROM annotations a 
                    JOIN if0_40458841_questions_db.words_db w ON w.id=a.question_id 
                    WHERE a.project_id=$project_id AND a.user_id={$u['id']} 
                    ORDER BY a.id ASC";
            
            $ans = $conn->query($sql);

            while($r = $ans->fetch_assoc()):
                $parsed = parseQuestionRow($r['question_data']);
                $student_ans = trim($r['answer']);
                $correct_ans = trim($parsed['correct']);
                
                // منطق التصحيح التلقائي
                $is_correct = false;
                if (!empty($correct_ans)) {
                    if ($student_ans === $correct_ans) $is_correct = true;
                    elseif (mb_strpos($student_ans, $correct_ans) === 0) $is_correct = true;
                }

                $css_class = $is_correct ? 'ans-correct' : 'ans-wrong';
                $current_score = ($r['score'] !== null) ? $r['score'] : ($is_correct ? 1 : 0);
            ?>
            
            <div class="ans-card <?= $css_class ?>">
                <strong>Q:</strong> <?= e($parsed['question']) ?><br>
                <div style="margin: 5px 0;">
                    <strong>Student Answer:</strong> <?= e($student_ans) ?> 
                    <?= $is_correct ? "✅" : "❌" ?>
                </div>
                <div class="correct-hint">Correct Answer: <?= e($correct_ans) ?></div>
                <div style="margin-top:10px;">
                    <label>Score: </label>
                    <input type="number" name="scores[<?= $r['annot_id'] ?>]" value="<?= $current_score ?>" style="width:60px; padding:5px;">
                </div>
            </div>
            
            <?php endwhile; ?>
            
            <button class="button" type="submit">Save Grades for <?= e($u['name']) ?></button>
        </form>
    </div>
<?php endwhile; ?>
</div>
</body>
</html>