<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once 'auth_guard.php';
checkAdmin();

// إعدادات الاتصال
$host = "sql206.infinityfree.com";
$user = "if0_40458841";
$password = "PoweR135";
$dbname = "if0_40458841_questions_db";

$conn = new mysqli($host, $user, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$message = "";

// --- دالة قراءة ODS ---
function parseODS($filePath)
{
    $data = [];
    $zip = new ZipArchive;
    if ($zip->open($filePath) === TRUE) {
        $content = $zip->getFromName('content.xml');
        $zip->close();
        if ($content) {
            $xml = new DOMDocument();
            $xml->loadXML($content);
            $rows = $xml->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'table-row');
            foreach ($rows as $row) {
                $cellData = [];
                $cells = $row->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'table-cell');
                foreach ($cells as $cell) {
                    $text = $cell->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p')->item(0);
                    $cellValue = $text ? $text->nodeValue : '';
                    $cellData[] = $cellValue;
                    if ($cell->hasAttribute('table:number-columns-repeated')) {
                        $repeated = (int) $cell->getAttribute('table:number-columns-repeated');
                        for ($i = 1; $i < $repeated; $i++) {
                            $cellData[] = $cellValue;
                        }
                    }
                }
                if (!empty(array_filter($cellData))) {
                    $data[] = $cellData;
                }
            }
        }
    }
    return $data;
}

// --- دالة لاكتشاف فاصلة CSV ---
function detectDelimiter($csvFile)
{
    $handle = fopen($csvFile, "r");
    $firstLine = fgets($handle);
    fclose($handle);
    if (strpos($firstLine, ';') !== false)
        return ';';
    return ',';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_import'])) {

    $selected_type = $_POST['table_type'];
    $target_table = "";

    switch ($selected_type) {
        case '1':
            $target_table = "words_db";
            break;
        case '2':
            $target_table = "phrases_db";
            break;
        case '3':
            $target_table = "proverbs_db";
            break;
        default:
            $message = "<div class='alert error'>يرجى اختيار نوع البيانات.</div>";
            break;
    }

    if ($target_table != "" && isset($_FILES['file_input']) && $_FILES['file_input']['error'] == 0) {

        $fileName = $_FILES['file_input']['name'];
        $fileTmp = $_FILES['file_input']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $finalData = [];

        // معالجة الملف
        if ($fileExt == 'csv') {
            $delimiter = detectDelimiter($fileTmp); // كشف تلقائي للفاصلة
            $handle = fopen($fileTmp, 'r');
            if ($handle !== FALSE) {
                // قراءة السطر الأول لإزالة BOM إذا وجد (مشكلة شائعة في الاكسل)
                $bom = pack('H*', 'EFBBBF');
                $firstLine = fgets($handle);
                if (strncmp($firstLine, $bom, 3) === 0) {
                    $firstLine = substr($firstLine, 3);
                }
                // إعادة المؤشر للبداية ومعالجة البيانات
                rewind($handle);

                while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    $finalData[] = $row;
                }
                fclose($handle);
            }
        } elseif ($fileExt == 'ods') {
            $finalData = parseODS($fileTmp);
        } else {
            $message = "<div class='alert error'>صيغة الملف غير مدعومة.</div>";
        }

        if (!empty($finalData)) {
            $success_count = 0;
            $skipped_count = 0;
            $db_error = "";

            // 1. تأكد من أسماء الأعمدة هنا! هل هي مطابقة لقاعدة بياناتك 100%؟
            // لقد استخدمت الأسماء التي ظهرت في صورتك السابقة
            // type_of_questions (هل هي جمع أم مفرد في قاعدتك؟ تأكد)

            $sql = "INSERT INTO $target_table 
                    (Term, Meaning_of_term, Dialect_type, Location_Recognition_question, Cultural_Interpretation_question, Contextual_Usage_question, Fill_in_Blank_question, True_False_question, Meaning_question, type_of_questions) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // 2. فحص هل الاستعلام صحيح أم لا (هنا تظهر المشكلة عادة)
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                // إذا فشل التحضير، فهذا يعني أن اسم عمود خطأ
                $message = "<div class='alert error'>خطأ في قاعدة البيانات: " . $conn->error . "<br>تأكد من أسماء الأعمدة في الكود ومطابقتها للجدول ($target_table).</div>";
            } else {
                foreach ($finalData as $index => $row) {
                    // تخطي العناوين
                    if (empty($row[0]) || strtolower(trim($row[0])) == 'term' || strtolower(trim($row[0])) == 'test')
                        continue;

                    // تعبئة البيانات
                    $p_term = isset($row[0]) ? trim($row[0]) : "";
                    $p_meaning = isset($row[1]) ? trim($row[1]) : "";
                    $p_dialect = isset($row[2]) ? trim($row[2]) : "";
                    $p_location = isset($row[3]) ? trim($row[3]) : "";
                    $p_cultural = isset($row[4]) ? trim($row[4]) : "";
                    $p_context = isset($row[5]) ? trim($row[5]) : "";
                    $p_fill = isset($row[6]) ? trim($row[6]) : "";
                    $p_tf = isset($row[7]) ? trim($row[7]) : "";
                    $p_options = isset($row[8]) ? trim($row[8]) : "";
                    $p_type = $selected_type;

                    $stmt->bind_param(
                        "sssssssssi",
                        $p_term,
                        $p_meaning,
                        $p_dialect,
                        $p_location,
                        $p_cultural,
                        $p_context,
                        $p_fill,
                        $p_tf,
                        $p_options,
                        $p_type
                    );

                    if ($stmt->execute()) {
                        $success_count++;
                    } else {
                        $skipped_count++;
                        // حفظ أول خطأ يظهر
                        if (empty($db_error))
                            $db_error = $stmt->error;
                    }
                }

                if ($success_count > 0) {
                    $message = "<div class='alert success'><i class='bx bx-check-circle'></i> تم إضافة <b>$success_count</b> سجل بنجاح.</div>";
                }

                if ($skipped_count > 0) {
                    $message .= "<div class='alert error'>فشل إضافة <b>$skipped_count</b> سجل.<br>سبب الخطأ: $db_error</div>";
                }
            }
        } elseif (empty($message)) {
            $message = "<div class='alert error'>لم يتم العثور على بيانات في الملف، تأكد أن الملف ليس فارغاً.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>إضافة بيانات مخصصة | Admin</title>
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
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: right;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #cbd5e1;
            font-weight: 600;
            font-size: 14px;
        }

        select,
        input[type="file"] {
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

        select:focus {
            border-color: var(--accent-blue);
        }

        .file-upload-box {
            border: 2px dashed #334155;
            padding: 40px;
            border-radius: 15px;
            cursor: pointer;
            transition: 0.3s;
            margin-bottom: 20px;
            background: rgba(15, 23, 42, 0.3);
        }

        .file-upload-box:hover {
            border-color: var(--accent-blue);
            background: rgba(59, 130, 246, 0.05);
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

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            border: 1px solid transparent;
            text-align: right;
        }

        .success {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
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
                <h1 style="margin:0; font-size: 24px;">📥 إضافة بيانات (CSV / ODS)</h1>
            </div>
        </div>
        <?= $message ?>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>اختر الجدول المراد الإضافة إليه</label>
                    <select name="table_type" required>
                        <option value="" disabled selected>-- اختر النوع --</option>
                        <option value="1">كلمات (Words DB)</option>
                        <option value="2">عبارات (Phrases DB)</option>
                        <option value="3">أمثال (Proverbs DB)</option>
                    </select>
                    <small style="color: #64748b; margin-top:5px; display:block;">سيتم إضافة الرقم المخصص للنوع
                        تلقائياً</small>
                </div>
                <div class="form-group">
                    <label>ملف البيانات</label>
                    <div class="file-upload-box" onclick="document.getElementById('fileInput').click()">
                        <i class='bx bxs-file-plus'
                            style="font-size: 50px; color: var(--accent-green); margin-bottom: 10px;"></i>
                        <p style="margin: 0; color: var(--text-muted);">اضغط لاختيار ملف .csv أو .ods</p>
                        <p style="margin: 5px 0 0; font-size: 12px; color: #64748b;" id="fileNameDisplay">لم يتم اختيار
                            ملف</p>
                    </div>
                    <input type="file" name="file_input" id="fileInput" style="display: none;" accept=".csv, .ods"
                        required
                        onchange="document.getElementById('fileNameDisplay').innerText = this.files[0].name; document.getElementById('fileNameDisplay').style.color = '#34d399';">
                </div>
                <button type="submit" name="submit_import" class="btn-submit">رفع البيانات</button>
            </form>
        </div>
    </main>
    <script>
        // نفس كود الجافاسكريبت المستخدم في باقي الصفحات
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const mainContent = document.getElementById('mainContent');

            if (window.innerWidth <= 1100) {
                sidebar.classList.toggle('active');
                // إظهار/إخفاء الخلفية المظللة
                if (overlay) {
                    overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
                }
            } else {
                sidebar.classList.toggle('close');
                mainContent.classList.toggle('expand');
            }
        }

        const menuBtn = document.getElementById('menuBtn');
        if (menuBtn) {
            menuBtn.addEventListener('click', toggleMenu);
        }
    </script>
</body>

</html>