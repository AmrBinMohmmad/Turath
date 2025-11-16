<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: signup.php');
    exit;
}
$admin = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Data Annotation</title>
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
      <span>Admin: <?= htmlspecialchars($admin['name']) ?></span>
      <a href="user_page.php">View User View</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="hero">
    <div class="hero-content">
      <h2>Admin Panel</h2>
      <p>Here you can manage users and monitor annotation activity (placeholder for now).</p>
    </div>
  </main>

  <footer>
    <p>© 2025 Data Annotation</p>
  </footer>
</body>
</html>
