<?php
require_once 'auth_guard.php';
checkAdmin();

$host = "sql206.infinityfree.com";
$user = "if0_40458841";
$password = "PoweR135";
$conn_proj = new mysqli($host, $user, $password, "if0_40458841_projects");
$conn_proj->set_charset("utf8mb4");
$conn_quest = new mysqli($host, $user, $password, "if0_40458841_questions_db");
$conn_quest->set_charset("utf8mb4");
if ($conn_proj->connect_error || $conn_quest->connect_error) {
    die("فشل الاتصال بقواعد البيانات.");
}
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_name = $_POST['card_name'];
    $description = $_POST['description'];
    $card_type = (int) $_POST['card_type'];
    $dialect_type = $_POST['dialect_type'];
    $number_of_users = (int) $_POST['number_of_users'];
    $q_count = (int) $_POST['q_count'];
    if ($q_count < 1)
        $q_count = 20;
    if ($number_of_users < 0)
        $number_of_users = 0;

    $img_path = "";
    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["img"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)) {
            $img_path = $target_file;
        }
    }

    $stmt = $conn_proj->prepare("INSERT INTO cards (card_name, description, img, card_type, dialect_type, number_of_users, number_of_question) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisii", $card_name, $description, $img_path, $card_type, $dialect_type, $number_of_users, $q_count);

    if ($stmt->execute()) {
        $new_card_id = $conn_proj->insert_id;
        $where_clause = ($dialect_type != "all") ? "WHERE Dialect_type = '" . $conn_quest->real_escape_string($dialect_type) . "'" : "";

        $sql_questions = "";
        if ($card_type == 4) {
            $sql_questions = "(SELECT id, 1 as q_type FROM words_db $where_clause ORDER BY RAND() LIMIT $q_count) UNION ALL (SELECT id, 2 as q_type FROM phrases_db $where_clause ORDER BY RAND() LIMIT $q_count) UNION ALL (SELECT id, 3 as q_type FROM proverbs_db $where_clause ORDER BY RAND() LIMIT $q_count) ORDER BY RAND() LIMIT $q_count";
        } else {
            $source_table = ($card_type == 1) ? "words_db" : (($card_type == 2) ? "phrases_db" : "proverbs_db");
            $sql_questions = "SELECT id, $card_type as q_type FROM $source_table $where_clause ORDER BY RAND() LIMIT $q_count";
        }

        $result_q = $conn_quest->query($sql_questions);
        if ($result_q && $result_q->num_rows > 0) {
            $stmt_link = $conn_proj->prepare("INSERT INTO cards_questions (card_id, number_of_q, type_of_q) VALUES (?, ?, ?)");
            while ($row = $result_q->fetch_assoc()) {
                $stmt_link->bind_param("iii", $new_card_id, $row['id'], $row['q_type']);
                $stmt_link->execute();
            }
            if ($result_q->num_rows != $q_count) {
                $conn_proj->query("UPDATE cards SET number_of_question = " . $result_q->num_rows . " WHERE id = $new_card_id");
            }
            $message = "<div class='alert success'><i class='bx bx-check-circle'></i> تم إنشاء الكارد بنجاح! جاري التحويل...</div><script>setTimeout(function(){ window.location.href = 'admin_page.php'; }, 2000);</script>";
        } else {
            $message = "<div class='alert success'><i class='bx bx-info-circle'></i> تم الإنشاء (بدون أسئلة).</div><script>setTimeout(function(){ window.location.href = 'admin_page.php'; }, 2000);</script>";
        }
    } else {
        $message = "<div class='alert error'>حدث خطأ: " . $conn_proj->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>إضافة كارد جديد | Admin</title>
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
            transition: all 0.3s ease;
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
            flex-grow: 1;
            transition: all 0.3s ease;
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

        .form-container {
            background: var(--card-dark);
            padding: 40px;
            border-radius: 20px;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #cbd5e1;
            font-weight: 600;
            font-size: 14px;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 15px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            color: white;
            font-family: inherit;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
            box-sizing: border-box;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: 0.3s;
        }

        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid transparent;
        }

        .success {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        .file-upload {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px;
            border: 2px dashed #334155;
            border-radius: 15px;
            cursor: pointer;
            background: rgba(15, 23, 42, 0.5);
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
                <h1 style="margin:0; font-size: 24px;">إضافة كارد جديد 🎲</h1>
            </div>
        </div>
        <?= $message ?>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group"><label>عنوان الكارد</label><input type="text" name="card_name" required></div>
                <div class="form-group"><label>الوصف</label><textarea name="description" rows="3" required></textarea>
                </div>
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div><label>عدد الأسئلة</label><input type="number" name="q_count" value="20" min="1" required>
                    </div>
                    <div><label>الحد الأقصى</label><input type="number" name="number_of_users" value="0" min="0"
                            required></div>
                </div>
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div><label>المصدر</label><select name="card_type" required>
                            <option value="4">Mixed</option>
                            <option value="3">Proverbs</option>
                            <option value="1">Words</option>
                            <option value="2">Phrases</option>
                        </select></div>
                    <div><label>اللهجة</label><select name="dialect_type" required>
                            <option value="all">Mixed</option>
                            <option value="General">General</option>
                            <option value="Southern">Southern</option>
                            <option value="Central">Central</option>
                            <option value="Eastern">Eastern</option>
                            <option value="Northern">Northern</option>
                            <option value="Western">Western</option>
                        </select></div>
                </div>
                <div class="form-group"><label>الصورة</label>
                    <div class="file-upload" onclick="document.getElementById('fileInput').click()"><i
                            class='bx bxs-cloud-upload' style="font-size:40px; color:#64748b;"></i><span>رفع
                            صورة</span><input type="file" name="img" id="fileInput" style="display: none;"
                            accept="image/*"></div>
                </div>
                <button type="submit" class="btn-submit">حفظ</button>
            </form>
        </div>
    </main>
</body>

</html>