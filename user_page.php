<?php
 session_start();
 if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
     header('Location: signup.php');
      exit;
  }
  $user = $_SESSION['user'];
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard | Data Annotation</title>
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
      <span>Welcome, <?= htmlspecialchars($user['name']) ?></span>
      <a href="types.html">Question Types</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="hero">
    <div class="hero-content">
      <h2>Welcome, <?= htmlspecialchars($user['name']) ?> 👋</h2>
      <p>Start annotating by choosing a question type.</p>
      <a href="types.html" class="btn">Go to Question Types</a>
    </div>
  </main>

  <footer>
    <p>© 2025 Data Annotation</p>
  </footer>
</body>
</html>

