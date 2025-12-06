<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

// auth_guard.php - ملف الحماية والتحقق من الصلاحيات

// التأكد من أن الجلسة بدأت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * دالة حماية صفحات الأدمن
 * تمنع أي شخص غير الأدمن من الدخول
 */
function checkAdmin() {
    // 1. إذا لم يسجل الدخول أصلاً -> يذهب لصفحة الدخول
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: login.php");
        exit();
    }

    // 2. إذا كان مسجلاً لكنه ليس أدمن -> يطرده لصفحة المستخدم
    if ($_SESSION['role'] !== 'admin') {
        header("Location: regions.php"); // أو user_page.php
        exit();
    }
}

/**
 * دالة حماية صفحات المستخدم
 * تمنع الأدمن من الدخول (إذا رغبت في فصل تام) وتمنع غير المسجلين
 */
function checkUser() {
    // 1. إذا لم يسجل الدخول -> يذهب لصفحة الدخول
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: login.php");
        exit();
    }

    // 2. إذا كان أدمن وحاول دخول صفحة مستخدم -> يطرده لصفحة الأدمن
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_page.php");
        exit();
    }
}
?>