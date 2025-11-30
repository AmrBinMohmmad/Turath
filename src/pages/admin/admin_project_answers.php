<?php
require_once __DIR__ . '/../../auth/auth.php';
require_admin();

$host = "localhost";
$user = "root";
$password = "";

// Database names
$projects_db_name = "projects";
$users_db_name = "users_db";

// Establish connection to projects DB
$conn_projects = new mysqli($host, $user, $password, $projects_db_name);
if ($conn_projects->connect_error) {
    die("DB Error (Projects): " . $conn_projects->connect_error);
}

$project_id = intval($_GET['id'] ?? 0);

// 1. Fetch Card Info
$card_query = $conn_projects->prepare("SELECT card_name, number_of_question FROM cards WHERE id = ?");
$card_query->bind_param("i", $project_id);
$card_query->execute();
$card_result = $card_query->get_result();

if ($card_result->num_rows == 0) {
    die("لم يتم العثور على هذا الاختبار.");
}
$card_data = $card_result->fetch_assoc();
$card_name = $card_data['card_name'];
$total_questions = intval($card_data['number_of_question']);

// 2. Fetch completed users and their scores
$completed_users_query = $conn_projects->prepare("
    SELECT user_id, SUM(score) AS total_score
    FROM annotations
    WHERE project_id = ?
    GROUP BY user_id
    HAVING COUNT(id) = ?
");
$completed_users_query->bind_param("ii", $project_id, $total_questions);
$completed_users_query->execute();
$completed_users_result = $completed_users_query->get_result();

$completed_users_data = [];
while ($row = $completed_users_result->fetch_assoc()) {
    $completed_users_data[$row['user_id']] = [
        'total_score' => $row['total_score'],
        'user_name' => 'مستخدم غير معروف' // Default value
    ];
}

// 3. Fetch user names if there are completed users
if (!empty($completed_users_data)) {
    $user_ids = array_keys($completed_users_data);
    $user_ids_string = implode(',', $user_ids);

    $conn_users = new mysqli($host, $user, $password, $users_db_name);
    if (!$conn_users->connect_error) {
        $users_query = $conn_users->query("SELECT id, name FROM users WHERE id IN ($user_ids_string)");
        if ($users_query) {
            while ($user_row = $users_query->fetch_assoc()) {
                if (isset($completed_users_data[$user_row['id']])) {
                    $completed_users_data[$user_row['id']]['user_name'] = $user_row['name'];
                }
            }
        }
        $conn_users->close();
    }
}

// 4. Analytics Section (only if 2 or more users completed)
$analytics = [];
if (count($completed_users_data) >= 2) {
    $scores = array_column($completed_users_data, 'total_score');
    $analytics['average_score'] = round(array_sum($scores) / count($scores), 2);
    $analytics['highest_score'] = max($scores);
    $analytics['lowest_score'] = min($scores);
    
    $success_rate_query = $conn_projects->prepare("
        SELECT COUNT(DISTINCT user_id) as successful_users
        FROM annotations
        WHERE project_id = ?
        GROUP BY user_id
        HAVING SUM(score) > (? / 2)
    ");
    $success_rate_query->bind_param("ii", $project_id, $total_questions);
    $success_rate_query->execute();
    $successful_users = $success_rate_query->get_result()->num_rows;
    $analytics['success_rate'] = round(($successful_users / count($completed_users_data)) * 100, 2);

    // Most frequently failed questions
    $failed_questions_query = $conn_projects->prepare("
        SELECT question_id, COUNT(*) as error_count
        FROM annotations
        WHERE project_id = ? AND score = 0
        GROUP BY question_id
        ORDER BY error_count DESC
        LIMIT 5
    ");
    $failed_questions_query->bind_param("i", $project_id);
    $failed_questions_query->execute();
    $failed_questions_result = $failed_questions_query->get_result();
    $analytics['most_failed_questions'] = [];
    while($row = $failed_questions_result->fetch_assoc()){
        $analytics['most_failed_questions'][] = $row;
    }
}

$conn_projects->close();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>إجابات اختبار: <?= htmlspecialchars($card_name) ?></title>
  <link rel="icon" type="image/png" href="../../assets/images/Favicon.png">
  <link rel="stylesheet" href="../../css/style.css" />
  <style>
    .analytics-section {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-top: 30px;
    }
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    .stat-card {
        background: white;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .stat-card h4 { margin: 0 0 10px 0; }
    .stat-card p { font-size: 1.5rem; font-weight: bold; margin: 0; }
  </style>
</head>
<body>
  <header class="navbar">
    <a href="admin_page.php" class="logo" style="text-decoration: none;">
      <img src="../../assets/images/Favicon.png" alt="شعار لهجتنا">
    </a>
    <nav>
      <a href="admin_page.php">العودة للوحة التحكم</a>
    </nav>
  </header>

  <main class="user-dashboard">
    <section class="user-panel">
      <header class="user-panel-header">
        <h2>إجابات اختبار: "<?= htmlspecialchars($card_name) ?>"</h2>
      </header>
      
      <div id="cards-box" class="cards-grid">
        <?php if (!empty($completed_users_data)): ?>
            <?php foreach ($completed_users_data as $user_id => $user_data): ?>
                <article class="quiz-card">
                  <div class="quiz-card-header">
                    <h3 class="quiz-title"><?= htmlspecialchars($user_data['user_name']) ?></h3>
                  </div>
                  <div class="quiz-meta">
                    <span>النتيجة: <strong><?= (int)$user_data['total_score'] ?> / <?= $total_questions ?></strong></span>
                  </div>
                  <div class="quiz-actions">
                   <a class="quiz-button" href="admin_user_answers_detail.php?id=<?= $project_id ?>&user_id=<?= (int)$user_id ?>">مشاهدة التفاصيل</a>
                  </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p style='padding:20px; text-align:center; width:100%;'>لم يكمل أي مستخدم هذا الاختبار بعد.</p>
        <?php endif; ?>
      </div>

      <?php if (!empty($analytics)): ?>
      <section class="analytics-section">
        <h3>تحليل أداء الاختبار</h3>
        <div class="analytics-grid">
            <div class="stat-card">
                <h4>متوسط النتيجة</h4>
                <p><?= $analytics['average_score'] ?> / <?= $total_questions ?></p>
            </div>
            <div class="stat-card">
                <h4>أعلى نتيجة</h4>
                <p><?= $analytics['highest_score'] ?></p>
            </div>
            <div class="stat-card">
                <h4>أقل نتيجة</h4>
                <p><?= $analytics['lowest_score'] ?></p>
            </div>
            <div class="stat-card">
                <h4>معدل النجاح (>50%)</h4>
                <p><?= $analytics['success_rate'] ?>%</p>
            </div>
        </div>
        <div style="margin-top: 20px;">
            <h4>الأسئلة الأكثر خطأً:</h4>
            <ul>
                <?php foreach($analytics['most_failed_questions'] as $q): ?>
                    <li>السؤال رقم (ID: <?= $q['question_id'] ?>) - أخطأ فيه <?= $q['error_count'] ?> مستخدمين</li>
                <?php endforeach; ?>
            </ul>
        </div>
      </section>
      <?php endif; ?>

    </section>
  </main>

  <footer>
    <p>© 2025 لهجتنا</p>
  </footer>
</body>
</html>