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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome | Data Annotation</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="icon" type="image/png" href="../assets/images/Favicon.png">
</head>
<body>
  <header class="navbar">
  <a href="../../index.html" class="logo">
    <img src="../assets/images/Favicon.png" alt="Logo">
     <h1>Data Annotation</h1>
    </div>
    <nav>
      <a href="../../index.html">الرئيسية</a>

      <span class="auth-links">
        <a href="signup.php?form=register">تسجيل</a>
        <span class="divider">/</span>
        <a href="signup.php?form=login">دخول</a>
      </span>

      <a href="about.html">عن الموقع</a>
      <a href="contact.html">تواصل معنا</a>
    </nav>
  </header>

  <div class="container">
    <div class="form_box <?= isActiveForm('login',$activeForm); ?>" id="login-form">
      <form action="login_register.php" method="post">
        <h2>Login</h2>
        <?= showError($errors['login']); ?>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
        <p>Don't have an account? <a onclick="showForm('register-form')">Register</a></p>
      </form>
    </div>

    <div class="form_box <?= isActiveForm('register',$activeForm); ?>" id="register-form">
      <form action="login_register.php" method="post">
        <h2>Register</h2>
        <?= showError($errors['register']); ?>
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
        <p>Already have an account? <a onclick="showForm('login-form')">Login</a></p>
      </form>
    </div>
  </div>

  <footer>
    <p>© 2025 Data Annotation</p>
  </footer>
  <script src="../js/script.js"></script>
</body>
</html>
