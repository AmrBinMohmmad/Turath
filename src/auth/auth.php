<?php
session_start();

$errors = [
  "login"    => $_SESSION['login_error']    ?? '',
  "register" => $_SESSION['register_error'] ?? ''
];

$activeForm = $_SESSION['active_form'] ?? 'login';
session_unset();

function showError($error){
  return !empty($error) ? "<p class='error-message'>$error</p>" : "";
}

function isActiveForm($formName, $activeForm){
  return $formName === $activeForm ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | لهجتنا</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/Favicon.png">
</head>
<body>

<header class="navbar">
    <a href="../pages/index.html" style="text-decoration: none;">
        <div class="logo">
            <img src="../assets/images/Favicon.png" alt="شعار لهجتنا">
            <div class="logo-text">
                <h1 class="site-title">لهجتنا</h1>
                <p class="site-tagline">اختبر معرفتك بثقافة وتراث مناطق المملكة</p>
            </div>
        </div>
    </a>
    <nav>
        <a href="../pages/index.html">الرئيسية</a>
        <a href="../auth/auth.php">تسجيل / دخول</a>
        <a href="../pages/about.html">عن الموقع</a>
        <a href="../pages/contact.html">تواصل معنا</a>
    </nav>
</header>

<div class="container">

    <div class="form_box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
        <form action="../login_register.php" method="post">
            <h2>تسجيل الدخول</h2>
            <?= showError($errors['login']); ?>
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>

            <button type="submit" name="login">دخول</button>

            <p>ليس لديك حساب؟
                <a href="#" onclick="showForm('register-form'); return false;">إنشاء حساب</a>
            </p>
        </form>
    </div>

    <div class="form_box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
        <form action="../login_register.php" method="post">
            <h2>إنشاء حساب</h2>
            <?= showError($errors['register']); ?>
            <input type="text" name="name" placeholder="الاسم الكامل" required>
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>

            <button type="submit" name="register">تسجيل</button>

            <p>عندك حساب؟
                <a href="#" onclick="showForm('login-form'); return false;">تسجيل الدخول</a>
            </p>
        </form>
    </div>

</div>

<footer>
    <p>© 2025 لهجتنا</p>
</footer>


<script src="../js/script.js"></script>

</body>
</html>
