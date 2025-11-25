<?php
// user_page.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "config2.php";
session_start();

// ����� �������� ������ (������ 1 �� �� ������)
$user_id = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['name'] ?? 'Guest';

// ��� ���� �������� �������
$projects = $conn->query("
    SELECT c.*, 
    (SELECT COUNT(DISTINCT a.user_id) FROM annotations a WHERE a.project_id=c.id) AS completed_users
    FROM cards c
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navbar container">
  <div class="header-title">Data Annotation � User</div>
  <div>
    <span>Welcome, <?= htmlspecialchars($user_name) ?></span>
    <a href="user_answers.php" class="button">My Answers</a>
  </div>
</div>

<div class="container">
  <div class="card">
    <h2>Available Projects</h2>
    
    <?php if($projects->num_rows > 0): ?>
        <?php while($p = $projects->fetch_assoc()): 
            // --- ����� ����� ���: ���� ������ (Progress) ---
            
            // 1. ��� ������� ����� �� �������
            $total_q = (int)$p['number_of_question'];

            // 2. ��� ������� ���� ���� ����� ��� �������� �������
            $answered_query = $conn->query("SELECT COUNT(*) AS c FROM annotations WHERE user_id=$user_id AND project_id={$p['id']}");
            $answered = 0;
            if($answered_query) {
                $answered = (int)$answered_query->fetch_assoc()['c'];
            }

            // 3. ����� ������� $progress ����� �����
            $progress = ($total_q > 0) ? round(($answered / $total_q) * 100) : 0;
        ?>
          <div class="card" style="margin-bottom:12px; border:1px solid #eee;">
            <div class="project-row" style="display:flex; justify-content:space-between; align-items:center;">
              <div>
                <strong style="font-size:1.1em;"><?= htmlspecialchars($p['card_name']) ?></strong><br>
                <span class="small" style="color:#666;">
                    Total Questions: <?= $total_q ?> � 
                    Users Participated: <?= (int)$p['completed_users'] ?> / <?= (int)$p['number_of_users'] ?>
                </span>
              </div>
              <div>
                <a class="button" href="answer_project.php?id=<?= (int)$p['id'] ?>">
                    <?= ($progress > 0 && $progress < 100) ? 'Continue' : 'Start' ?>
                </a>
              </div>
            </div>
            
            <div style="margin-top:12px;">
              <div class="small">Your progress: <?= $progress ?>% (<?= $answered ?>/<?= $total_q ?>)</div>
              <div class="progress-bar"><div class="progress-fill" style="width:<?= $progress ?>%;"></div></div>
            </div>
          </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No projects available at the moment.</p>
    <?php endif; ?>
    
  </div>
</div>
</body>
</html>