<?php
session_start();

$errors = [
  "login"    => $_SESSION['login_error']    ?? '',
  "register" => $_SESSION['register_error'] ?? ''
];

$activeForm = $_GET['form'] ?? ($_SESSION['active_form'] ?? 'login');
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
  <title>تسجيل / دخول | لهجتنا</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="icon" type="image/png" href="../assets/images/Favicon.png">
</head>

<body>

<header class="navbar">
  <a href="../../index.html" class="logo" style="text-decoration:none;">
    <img src="../assets/images/Favicon.png" alt="شعار لهجتنا">
    <h1>لهجتنا</h1>
  </a>

  <nav>
    <a href="../../index.html">الرئيسية</a>

    <span class="auth-links">
      <a href="signup.php?form=register">تسجيل</a>
      <span class="divider">/</span>
      <a href="signup.php?form=login">دخول</a>
    </span>

    <a href="about.html">عن الموقع</a>
    <a href="contact.html">تواصل معنا</a>
    <button id="theme-toggle" class="theme-btn">🌙</button>

  </nav>
</header>

<div class="container">

  <!-- نموذج تسجيل الدخول -->
  <div class="form_box <?= isActiveForm('login',$activeForm); ?>" id="login-form">
    <form action="login_register.php" method="post">
      <h2>تسجيل الدخول</h2>
      <?= showError($errors['login']); ?>
      <input type="email" name="email" placeholder="البريد الإلكتروني" required>
      <input type="password" name="password" placeholder="كلمة المرور" required>
      <button type="submit" name="login">دخول</button>
      <p>ليس لديك حساب؟ <a href="signup.php?form=register">إنشاء حساب</a></p>
    </form>
  </div>

  <!-- نموذج إنشاء حساب -->
  <div class="form_box <?= isActiveForm('register',$activeForm); ?>" id="register-form">
    <form action="login_register.php" method="post">
      <h2>إنشاء حساب</h2>
      <?= showError($errors['register']); ?>
      <input type="text" name="name" placeholder="الاسم" required>
      <input type="email" name="email" placeholder="البريد الإلكتروني" required>
      <input type="password" name="password" placeholder="كلمة المرور" required>
      <button type="submit" name="register">تسجيل</button>
      <p>لديك حساب؟ <a href="signup.php?form=login">تسجيل الدخول</a></p>
    </form>
  </div>

</div>

<footer>
  <p>© 2025 لهجتنا</p>
</footer>

<script src="../js/script.js"></script>
</body>
</html>
