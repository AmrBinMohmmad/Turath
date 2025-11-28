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

while ($p = $result->fetch_assoc()):
?>

<div class="card" style="margin-bottom:15px;">
    <strong><?= $p['card_name'] ?></strong><br>
    عدد الأسئلة: <?= (int)$p['number_of_question'] ?> |
    المستخدمون: <?= (int)$p['number_of_users'] ?> |
    منطقة الأسئلة: <?= $p['Dialect_type'] ?><br><br>

    <a class="button" href="admin_project_answers.php?id=<?= (int)$p['id'] ?>">
        مشاهدة الأجوبة
    </a>
</div>

<?php endwhile; ?>
