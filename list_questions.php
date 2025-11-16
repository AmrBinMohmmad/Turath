<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: signup.php');
    exit;
}
require 'Qs.php';

$listQuestions = [];
foreach ($questions as $q) {
    if (isset($q['List_question']) || isset($q['Listed_items_question'])) {
        $listQuestions[] = $q['List_question'] ?? $q['Listed_items_question'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>List Questions | Data Annotation</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="Favicon.png">
</head>
<body>
 <header class="navbar">
  <a href="index.html" class="logo">
    <img src="Favicon.png" alt="Logo">
    <h1>Data Annotation</h1>
    </div>
    <nav>
      <a href="user_page.php">Dashboard</a>
      <a href="types.html">Question Types</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="questions-main">
    <h2>List Questions</h2>
    <div class="questions-grid">
      <?php foreach ($listQuestions as $block): ?>
        <div class="question-card">
          <?= nl2br(htmlspecialchars($block)) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <footer>
    <p>© 2025 Data Annotation</p>
  </footer>
</body>
</html>
