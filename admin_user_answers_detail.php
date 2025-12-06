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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

        :root {
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            display: block;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--card-dark);
            padding: 25px;
            display: flex;
            flex-direction: column;
            border-left: 1px solid rgba(255, 255, 255, 0.05);
            position: fixed;
            height: 100%;
            right: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-sizing: border-box;
        }

        .sidebar.close {
            transform: translateX(100%);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 50px;
            display: block;
            color: var(--text-main);
            text-decoration: none;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 8px;
            border-radius: 12px;
            transition: 0.3s;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
        }

        .main-content {
            margin-right: var(--sidebar-width);
            padding: 40px;
            width: auto;
            transition: margin-right 0.3s ease;
            box-sizing: border-box;
        }

        .main-content.expand {
            margin-right: 0;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .menu-toggle {
            font-size: 32px;
            color: var(--text-main);
            cursor: pointer;
            margin-left: 15px;
            display: none;
        }

        .table-container {
            background: var(--card-dark);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow-x: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th {
            text-align: right;
            padding: 20px;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        td {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 14px;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .badge {
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-xp {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .badge-lvl {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        @media (max-width: 1100px) {
            .sidebar {
                transform: translateX(100%);
                right: 0;
            }

            .sidebar.active {
                transform: translateX(0);
                box-shadow: -10px 0 30px rgba(0, 0, 0, 0.5);
            }

            .main-content {
                margin-right: 0;
                padding: 20px;
            }

            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <h1 style="margin:0; font-size: 24px;">👨‍🎓 تفاصيل الطلاب والنشاط</h1>
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