<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once __DIR__ . '/../../auth/auth_guard.php';
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
    // 1. جعلنا النوع دائماً 4 (مختلط) افتراضياً لأنه الأكثر مرونة
    $card_type = 4;
    $dialect_type = $_POST['dialect_type'];
    $number_of_users = (int) $_POST['number_of_users'];
    $q_count = (int) $_POST['q_count'];

    if ($q_count < 1)
        $q_count = 20;
    if ($number_of_users < 2)
        $number_of_users = 2;
    $img_path = "";

    // إضافة الكارد في جدول المشاريع
    $stmt = $conn_proj->prepare("INSERT INTO cards (card_name, description, img, card_type, dialect_type, number_of_users, number_of_question) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisii", $card_name, $description, $img_path, $card_type, $dialect_type, $number_of_users, $q_count);

    if ($stmt->execute()) {
        $new_card_id = $conn_proj->insert_id;
        $where_clause = ($dialect_type != "all") ? "WHERE Dialect_type = '" . $conn_quest->real_escape_string($dialect_type) . "'" : "";

        // 2. الحل الذكي لمشكلة نقص الأسئلة: تجميع كل الأسئلة المتاحة من جميع الجداول
        // سنستخدم UNION ALL لجمع كل الـ ID المتاحة التي تطابق اللهجة المطلوبة
        // العمود q_type يحدد الجدول المصدر: 1=words, 2=phrases, 3=proverbs

        $sql_pool = "
            (SELECT id, 1 as q_type FROM words_db $where_clause) 
            UNION ALL 
            (SELECT id, 2 as q_type FROM phrases_db $where_clause) 
            UNION ALL 
            (SELECT id, 3 as q_type FROM proverbs_db $where_clause) 
            ORDER BY RAND() 
            LIMIT $q_count
        ";

        $result_q = $conn_quest->query($sql_pool);

        if ($result_q && $result_q->num_rows > 0) {
            $stmt_link = $conn_proj->prepare("INSERT INTO cards_questions (card_id, number_of_q, type_of_q) VALUES (?, ?, ?)");

            $added_questions = 0;
            while ($row = $result_q->fetch_assoc()) {
                $stmt_link->bind_param("iii", $new_card_id, $row['id'], $row['q_type']);
                $stmt_link->execute();
                $added_questions++;
            }

            // تحديث العدد الفعلي للأسئلة في حال كان المتوفر أقل من المطلوب
            if ($added_questions != $q_count) {
                $conn_proj->query("UPDATE cards SET number_of_question = $added_questions WHERE id = $new_card_id");
                $message = "<div class='alert success'><i class='bx bx-check-circle'></i> تم إنشاء الكارد بنجاح! ولكن تم العثور على <b>$added_questions</b> سؤال فقط لهذه اللهجة.</div><script>setTimeout(function(){ window.location.href = 'admin_page.php'; }, 3000);</script>";
            } else {
                $message = "<div class='alert success'><i class='bx bx-check-circle'></i> تم إنشاء الكارد بنجاح! جاري التحويل...</div><script>setTimeout(function(){ window.location.href = 'admin_page.php'; }, 2000);</script>";
            }
        } else {
            // حالة نادرة: لا يوجد أي سؤال بتاتاً لهذه اللهجة في أي جدول
            $conn_proj->query("DELETE FROM cards WHERE id = $new_card_id"); // حذف الكارد الفارغ لتنظيف القاعدة
            $message = "<div class='alert error'><i class='bx bx-error'></i> عذراً، لا توجد أي أسئلة متوفرة للهجة المختارة في أي من الجداول (كلمات، عبارات، أمثال). لم يتم إنشاء الكارد.</div>";
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
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../css/create_card_admin_site.css">
    <link rel="icon" type="image/png" href="../../assets/images/Favicon.png" />

</head>

<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="header">
            <div style="display: flex; align-items: center;">
                <i class='bx bx-menu menu-toggle' id="menuBtn"></i>
                <h1 style="margin:0; font-size: 24px;">إضافة كارد جديد</h1>
            </div>
        </div>
        <?= $message ?>
        <div class="form-container">
            <form method="POST">
                <div class="form-group"><label>عنوان الكارد</label><input type="text" name="card_name" required></div>
                <div class="form-group"><label>الوصف</label><textarea name="description" rows="3" required></textarea>
                </div>
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div><label>عدد الأسئلة</label><input type="number" name="q_count" value="20" min="1" required>
                    </div>
                    <div><label>الحد الأقصى للمستخدمين</label><input type="number" name="number_of_users" value="2"
                            min="2" required></div>
                </div>

                <div class="form-group">
                    <label>اللهجة المستهدفة</label>
                    <select name="dialect_type" required>
                        <option value="all">مختلط (كل اللهجات)</option>
                        <option value="General">عامة (General)</option>
                        <option value="Southern">الجنوبية (Southern)</option>
                        <option value="Central">الوسطى (Central)</option>
                        <option value="Eastern">الشرقية (Eastern)</option>
                        <option value="Northern">الشمالية (Northern)</option>
                        <option value="Western">الغربية (Western)</option>
                    </select>
                    <small style="color: #64748b; margin-top:5px; display:block;">سيقوم النظام تلقائياً بجمع الأسئلة
                        المتوفرة من الكلمات والعبارات والأمثال لهذه اللهجة.</small>
                </div>

                <button type="submit" class="btn-submit">حفظ وإنشاء</button>
            </form>
        </div>
    </main>
</body>

</html>

</html>

