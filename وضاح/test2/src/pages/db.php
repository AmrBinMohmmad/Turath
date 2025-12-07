<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

// db.php - ملف الاتصال الموحد لسيرفر InfinityFree

// 1. إعدادات السيرفر (احصل عليها من لوحة تحكم InfinityFree)
$host = "sql206.infinityfree.com";  // مثال: sql300.infinityfree.com (تأكد من الرقم الصحيح)
$user = "if0_40458841";             // اسم المستخدم الخاص بقاعدة البيانات
$password = "PoweR135"; // كلمة مرور الـ vPanel (ليست كلمة مرور دخول الموقع)

// 2. قواعد البيانات (أضفنا البادئة الجديدة)
// سنعتمد قاعدة المستخدمين كقاعدة افتراضية للاتصال
$database = "if0_40458841_users_db"; 

// 3. إنشاء الاتصال
$conn = new mysqli($host, $user, $password, $database);

// 4. التحقق من وجود أخطاء
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// 5. ضبط الترميز (مهم جداً للغة العربية)
$conn->set_charset("utf8mb4");

?>