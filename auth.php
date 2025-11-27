<?php
// تأكد من بدء الجلسة إذا لم تكن مبدوءة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// دالة التحقق من المستخدم العادي
function require_user() {
    // نتحقق من وجود user_id وأن الدور هو user
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
        header("Location: signup.php");
        exit;
    }
}

// دالة التحقق من الأدمن
function require_admin() {
    // نتحقق من وجود user_id وأن الدور هو admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        // إذا كان يوزر عادي يحاول يدخل صفحة أدمن، نطرده
        header("Location: signup.php");
        exit;
    }
}
?>