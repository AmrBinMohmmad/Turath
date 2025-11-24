<?php
include 'db.php';
include 'functions.php';

// الاتصال بقاعدة الأسئلة
$conn_qs_bd = new mysqli($host, $db_user, $db_password, "if0_40458841_questions_db");
$qs_db_available = !$conn_qs_bd->connect_error;

// إنشاء مشروع
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_card'])) {
    $card_name = trim($_POST['card_name'] ?? '');
    $num_of_user = intval($_POST['number_of_users'] ?? 0);
    $num_of_qst = intval($_POST['number_of_question'] ?? 0);

    if ($card_name === '' || $num_of_user <= 0 || $num_of_qst <= 0) {
        $create_error = "Please provide valid project name, number of users and number of questions.";
    } else {
        $stmt = $conn->prepare("INSERT INTO cards (number_of_users, card_name, number_of_question) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $num_of_user, $card_name, $num_of_qst);
        if ($stmt->execute()) {
            $card_id = $stmt->insert_id;
            $stmt->close();

            if ($qs_db_available) {
                $limit = max(1, $num_of_qst);
                $words_db = $conn_qs_bd->query("SELECT id, type_of_questions FROM words_db ORDER BY RAND() LIMIT $limit");
                if ($words_db && $words_db->num_rows > 0) {
                    $insert_stmt = $conn->prepare("INSERT INTO cards_questions (card_id, type_Of_q, number_of_q) VALUES (?, ?, ?)");
                    while ($row = $words_db->fetch_assoc()) {
                        $insert_stmt->bind_param("isi", $card_id, $row['type_of_questions'], $row['id']);
                        $insert_stmt->execute();
                    }
                    $insert_stmt->close();
                    $create_success = "Project created and questions assigned successfully.";
                } else {
                    $create_warning = "Project created but no questions were fetched from questions DB.";
                }
            } else {
                $create_warning = "Project created but questions DB is not available.";
            }
        } else {
            $create_error = "Failed to create project: " . $conn->error;
        }
    }
}

// جلب المشاريع
$projects = $conn->query("SELECT * FROM cards ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | Data Annotation</title>
<link rel="stylesheet" href="style.css">
<link rel="icon" type="image/png" href="Favicon.png">
<script>
function toggleForm() {
    var form = document.getElementById("createForm");
    form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
}
</script>
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
        <h2>Admin Panel</h2>

        <?php if (!empty($create_error)): ?>
            <div style="color: red;"><?= e($create_error) ?></div>
        <?php elseif (!empty($create_warning)): ?>
            <div style="color: orange;"><?= e($create_warning) ?></div>
        <?php elseif (!empty($create_success)): ?>
            <div style="color: green;"><?= e($create_success) ?></div>
        <?php endif; ?>

        <button onclick="toggleForm()">Create Project</button>
        <div id="createForm" style="display:none;">
            <form method="POST">
                <label>Project Name: <input type="text" name="card_name" required></label><br><br>
                <label>Number of Users: <input type="number" name="number_of_users" min="1" required></label><br><br>
                <label>Number of Questions: <input type="number" name="number_of_question" min="1" required></label><br><br>
                <button type="submit" name="create_card">Create Project</button>
            </form>
        </div>

        <div class="project-list">
            <h3>Existing Projects</h3>
            <?php if ($projects && $projects->num_rows > 0): ?>
                <?php while($project = $projects->fetch_assoc()): ?>
                    <div class="project-item">
                        <strong><?= e($project['card_name']) ?></strong><br>
                        Users: <?= (int)$project['number_of_users'] ?><br>
                        Questions: <?= (int)$project['number_of_question'] ?><br>
                        <a href="view_project.php?card_id=<?= (int)$project['id'] ?>">View</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No projects yet.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<footer>
    &copy; 2025 Data Annotation
</footer>
</body>
</html>
