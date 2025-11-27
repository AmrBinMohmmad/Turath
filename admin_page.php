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
<header class="navbar">
  <a href="index.html" class="logo">
    <img src="Favicon.png" alt="Logo">
     <h1>Data Annotation - Admin</h1>
    </div>
    <nav>
      <a href="create_project.php" class="button">Create Project</a>
      <a href="logout.php">Logout</a>
      <!---<a href="user_page.php">View User View</a>
      <a href="index.html">Home</a>
      <a href="about.html">About</a>
      <a href="contact.html">Contact</a>--->
    </nav>
</header>
<main class="types-wrapper">
<div class="container">
  <h2>All Projects</h2><br>
  <?php while($p = $projects->fetch_assoc()): ?>
    <div class="card">
        <strong><?= e($p['card_name']) ?></strong><br>
        Questions: <?= (int)$p['number_of_question'] ?> � Users: <?= (int)$p['number_of_users'] ?><br><br>
        <a class="button" href="admin_project_answers.php?id=<?= (int)$p['id'] ?>">View Answers</a>
    </div>
<?php endwhile; ?>
</div>
</main>
  <footer>
    <p>© 2025 Data Annotation</p>
  </footer>
</body>
</html>
