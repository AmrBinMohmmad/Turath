<?php
// create_project.php
require "config2.php";

$raw_query = $conn_qs->query("SELECT `Dialect type` FROM words_db WHERE `Dialect type` IS NOT NULL AND `Dialect type` != ''");

$unique_dialects = [];

if ($raw_query) {
    while ($row = $raw_query->fetch_assoc()) {
        $raw_val = $row['Dialect type'];
        
        // تنظيف قوي للنص: إزالة المسافات من الأطراف + توحيد الشكل
        $clean_val = trim($raw_val);
        
        // إصلاح مشكلة الحروف المخفية (مثل Non-breaking space)
        // نستبدل أي مسافة غير مرئية بمسافة عادية ثم نحذفها
        $clean_val = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $clean_val);
        
        // نستخدم المصفوفة لإزالة التكرار (المفتاح لا يتكرر)
        if (!empty($clean_val)) {
            // نستخدم القيمة كـ "مفتاح" لمنع التكرار
            $unique_dialects[$clean_val] = $clean_val;
        }
    }
}
// ترتيب أبجدي للقائمة
sort($unique_dialects);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $card_name = $conn->real_escape_string($_POST['card_name']);
    $num_users = intval($_POST['number_of_users']);
    $num_q = intval($_POST['number_of_question']);

    // 1. ����� ������
    $conn->query("INSERT INTO cards (card_name, number_of_users, number_of_question) VALUES ('$card_name', $num_users, $num_q)");
    $card_id = $conn->insert_id;

    // 2. ��� ����� �������
    if ($selected_dialect == "all") {
        $sql_questions = "SELECT id FROM words_db ORDER BY RAND() LIMIT $num_q";
    } else {
        $sql_questions = "SELECT id FROM words_db WHERE `Dialect type` LIKE '%$selected_dialect%' ORDER BY RAND() LIMIT $num_q";
    }
    
    $words = $conn_qs->query($sql_questions);
    if ($words) {
        while($w = $words->fetch_assoc()){
            // ��� ������ �������
            $conn->query("INSERT INTO cards_questions (card_id, question_id) VALUES ($card_id, {$w['id']})");
        }
    }

    header("Location: admin_page.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title> إنشاء اختبار | لهجتنا</title>
  <link rel="icon" type="image/png" href="Favicon.png">
  <link rel="stylesheet" href="style.css">
</head>
    
<body>
<header class="navbar">
  <a href="index.html" class="logo" style="text-decoration:none;">
    <img src="Favicon.png" alt="logo">
    <div class="logo-text">
      <h1 class="site-title">لهجتنا</h1>
      <p class="site-tagline">اختبر معرفتك بثقافة وتراث مناطق المملكة</p>
    </div>
  </a>
   <nav>
    <a href="logout.php">تسجيل الخروج</a>
  </nav>
  </header>

<main class="types-wrapper">
  <div class="admin-wrapper">
      
    <h2>إنشاء اختبار جديد</h2>
    <form method="post" class="card">
        <label>اسم الاختبار:<br><input type="text" name="card_name" required></label><br><br>
        <label>عدد المستخدمين:<br><input type="number" name="number_of_users" min="1" required></label><br><br>
        <label>عدد الأسئلة:<br><input type="number" name="number_of_question" min="1" required></label><br><br>
    
        <label>نوع اللهجة :</label>
            <select name="dialect_type" required>
                <option value="" disabled selected>-- اختر اللهجة --</option>
                <option value="all">مختلط (عشوائي من الجميع)</option>
                
                <?php foreach($unique_dialects as $dialect): ?>
                    <option value="<?= e($dialect) ?>"><?= e($dialect) ?></option>
                <?php endforeach; ?>
                
            </select>
        
        <button class="view-btn" type="submit">إنشاء الاختبار</button>
        <a class="button secondary" href="admin_page.php" style="text-align:center; display:block; margin-top:10px; background:#ccc;">إلغاء</a>
    </form>
    </div>
</main>
<footer>
  <p>© 2025 لهجتنا</p>
</footer>
</body>
</html>
