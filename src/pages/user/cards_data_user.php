<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "projects";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("DB Error");
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$region = isset($_GET['region']) ? $_GET['region'] : '';

$query = "SELECT * FROM cards WHERE 1";

if ($search !== "") {
    $s = $conn->real_escape_string($search);
    $query .= " AND card_name LIKE '%$s%'";
}

if ($region !== "") {
    $r = $conn->real_escape_string($region);
    $query .= " AND Dialect_type LIKE '%$r%'";
}

$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "<p style='padding:10px;'>لا توجد اختبارات مطابقة</p>";
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['name'] ?? 'ضيف';


while ($p = $result->fetch_assoc()):
        // عدد الأسئلة
        $total_q = (int) $p['number_of_question'];

        $number_of_users =$p["number_of_users"] ;
    
        // عدد الأسئلة التي أجاب عنها هذا المستخدم في هذا المشروع
        $answered_query = $conn->query("
                  SELECT COUNT(*) AS c 
                  FROM annotations 
                  WHERE user_id = $user_id 
                    AND project_id = {$p['id']}
              ");
        $answered = 0;
        if ($answered_query) {
            $answered = (int) $answered_query->fetch_assoc()['c'];
        }

        // نسبة التقدم
        $progress = ($total_q > 0) ? round(($answered / $total_q) * 100) : 0;

        // نص زر
        $btn_label = ($progress > 0 && $progress < 100) ? 'متابعة' : 'ابدأ الآن';
        ?>
        <article class="quiz-card">
            <div class="quiz-card-header">
                <h3 class="quiz-title"><?= htmlspecialchars($p['card_name']) ?></h3>
                <span class="quiz-users">
                    عدد المستخدمين:
                    <strong>3</strong> / <?=$number_of_users?>
                </span>
            </div>

            <div class="quiz-meta">
                <span>مجموع الأسئلة: <strong><?= $total_q ?></strong></span>
                <span>إجاباتك: <strong><?= $answered ?></strong> / <?= $total_q ?></span>
            </div>

            <div class="quiz-progress">
                <div class="quiz-progress-top">
                    <span>مستوى تقدمك</span>
                    <span class="quiz-progress-value"><?= $progress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                </div>
            </div>

            <div class="quiz-actions">
                <a class="button quiz-button" href="answer_card.php?id=<?= (int) $p['id'] ?>">
                    <?= $btn_label ?>
                </a>
            </div>
        </article>
    <?php endwhile; ?>