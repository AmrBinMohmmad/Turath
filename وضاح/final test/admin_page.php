<?php
require "config2.php";

// demo admin
$admin = ['id'=>1,'name'=>'Admin Demo'];

// fetch all projects
$projects = $conn->query("SELECT * FROM cards ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navbar container">
  <div class="header-title">Data Annotation — Admin</div>
  <div>
    <span><?= e($admin['name']) ?></span>
    <a href="create_project.php" class="button">Create Project</a>
  </div>
</div>

<div class="container">
  <h2>All Projects</h2>
  <?php while($p = $projects->fetch_assoc()): ?>
    <div class="card">
        <strong><?= e($p['card_name']) ?></strong><br>
        Questions: <?= (int)$p['number_of_question'] ?> � Users: <?= (int)$p['number_of_users'] ?><br><br>
        <a class="button" href="admin_project_answers.php?id=<?= (int)$p['id'] ?>">View Answers</a>
    </div>
<?php endwhile; ?>
</div>
</body>
</html>
