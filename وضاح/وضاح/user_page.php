<?php
include 'db.php';
include 'functions.php';

// جلب المشاريع
$projects = $conn->query("SELECT * FROM cards ORDER BY id DESC");
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
    </a>
    <nav>
        <?php if (isset($user)): ?>
            <span>Welcome, <?= e($user['name']) ?></span>
            <a href="logout.php">Logout</a>
        <?php elseif (isset($admin)): ?>
            <span>Admin: <?= e($admin['name'] ?? 'Admin') ?></span>
            <a href="user_page.php">View User View</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<main class="hero">
    <div class="hero-content">
        <h2>Welcome, <?= e($user['name']) ?></h2>
        <p>Start annotating by choosing a project.</p>

        <div class="project-list">
            <h3>Available Projects</h3>
            <?php if ($projects->num_rows > 0): ?>
                <?php while($project = $projects->fetch_assoc()): ?>
                    <div class="project-item">
                        <strong><?= e($project['card_name']) ?></strong><br>
                        Users: <?= (int)$project['number_of_users'] ?><br>
                        Questions: <?= (int)$project['number_of_question'] ?><br>
                        <a class="btn" href="start_annotation.php?card_id=<?= (int)$project['id'] ?>">Start Project</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No projects available yet.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    &copy; 2025 Data Annotation
</footer>
</body>
</html>
