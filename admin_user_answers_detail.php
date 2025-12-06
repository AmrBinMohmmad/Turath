<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once 'auth_guard.php';
checkAdmin();

$host = "sql206.infinityfree.com";
$user = "if0_40458841";
$password = "PoweR135";
$database = "if0_40458841_users_db";
$conn = new mysqli($host, $user, $password, $database);
$conn->set_charset("utf8mb4");
$sql = "SELECT u.id, u.username, u.email, u.level, u.xp, u.created_at, (SELECT COUNT(*) FROM if0_40458841_projects.annotations a WHERE a.user_id = u.id) as total_answers FROM if0_40458841_users_db.users u WHERE u.role != 'admin' ORDER BY u.xp DESC";
$users = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تفاصيل الطلاب | Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/admin_user_answers_detail.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <h1 style="margin:0; font-size: 24px;"> تفاصيل الطلاب والنشاط</h1>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>اسم المستخدم</th>
                        <th>البريد الإلكتروني</th>
                        <th>المستوى</th>
                        <th>النقاط (XP)</th>
                        <th>الإجابات</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0):
                        while ($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $u['id'] ?></td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div
                                            style="width:35px; height:35px; background:#334155; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                                            <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars($u['username']) ?>
                                    </div>
                                </td>
                                <td style="color:#94a3b8;"><?= htmlspecialchars($u['email']) ?></td>
                                <td><span class="badge badge-lvl">Level <?= $u['level'] ?></span></td>
                                <td><span class="badge badge-xp"><?= number_format($u['xp']) ?> XP</span></td>
                                <td><?= $u['total_answers'] ?></td>
                                <td style="color:#64748b;"><?= $u['created_at'] ?></td>
                            </tr>
                        <?php endwhile; else:
                        echo "<tr><td colspan='7' style='text-align:center;'>لا يوجد طلاب مسجلين بعد.</td></tr>";
                    endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>


</html>
