<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db.php';
// ... (كود PHP كما هو) ...
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin/admin_page.php");
    } else {
        header("Location: user/user_page.php");
    }
    exit();
}
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $pass = $_POST['password'];
    $sql = "SELECT * FROM if0_40458841_users_db.users WHERE email = '$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            if ($row['role'] === 'admin') {
                header("Location: admin/admin_page.php");
            } else {
                header("Location: user/regions.php");
            }
            exit();
        } else {
            $message = "<div class='alert error'><i class='bx bx-error-circle'></i> كلمة المرور غير صحيحة!</div>";
        }
    } else {
        $message = "<div class='alert error'><i class='bx bx-user-x'></i> البريد الإلكتروني غير مسجل!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title">تسجيل الدخول | منصة تراث</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/login_page.css">
    <link rel="icon" type="image/png" href="../assets/images/Favicon.png" />
    
</head>

<body>

    <nav class="navbar">
        <a href="../../index.php" class="logo">
            <img src="../assets/images/Favicon.png" alt="Logo" style="height:40px; margin-right:8px; ">
            <span id="nav-logo">منصة تراث</span>
        </a>
        <div class="nav-actions">
            <a href="../../index.php" style="color:var(--text-muted); text-decoration:none; font-size:14px; margin-left:15px;"
                id="nav-home">الرئيسية</a>
            <a href="signup.php" class="btn-gold" id="nav-signup">حساب جديد</a>
            <a href="about.php" class="icon-btn">
                <i class='bx bx-info-circle'></i>
            </a>
            <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
            <button class="icon-btn" onclick="toggleTheme()"><i class='bx bx-sun' id="theme-icon"></i></button>
        </div>
    </nav>

    <div class="main-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class='bx bx-user-circle'></i>
                <h1 id="header-title">أهلاً بك مجدداً</h1>
                <p id="header-desc">سجل دخولك لمتابعة رحلة التحدي</p>
            </div>

            <?= $message ?>

            <form method="POST">
                <div class="form-group">
                    <label id="lbl-email">البريد الإلكتروني</label>
                    <div class="input-box">
                        <i class='bx bx-envelope'></i>
                        <input type="email" name="email" id="inp-email" placeholder="example@mail.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label id="lbl-pass">كلمة المرور</label>
                    <div class="input-box">
                        <i class='bx bx-lock-alt'></i>
                        <input type="password" name="password" id="inp-pass" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btn-submit">تسجيل الدخول</button>
            </form>

            <div class="footer-text">
                <span id="footer-txt">ليس لديك حساب؟</span> <a href="signup.php" id="footer-link">انضم إلينا الآن</a>
            </div>
        </div>
    </div>

    <script>
        const txt = {
            ar: {
                navLogo: "منصة تراث", navHome: "الرئيسية", navSignup: "حساب جديد",
                headerTitle: "أهلاً بك مجدداً", headerDesc: "سجل دخولك لمتابعة رحلة التحدي",
                lblEmail: "البريد الإلكتروني", phEmail: "example@mail.com",
                lblPass: "كلمة المرور", phPass: "••••••••",
                btnSubmit: "تسجيل الدخول",
                footerTxt: "ليس لديك حساب؟", footerLink: "انضم إلينا الآن"
            },
            en: {
                navLogo: "Torath Platform", navHome: "Home", navSignup: "Sign Up",
                headerTitle: "Welcome Back", headerDesc: "Login to continue your challenge",
                lblEmail: "Email Address", phEmail: "example@mail.com",
                lblPass: "Password", phPass: "••••••••",
                btnSubmit: "Sign In",
                footerTxt: "Don't have an account?", footerLink: "Join Us Now"
            }
        };

        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            localStorage.setItem('theme', document.body.classList.contains('light-mode') ? 'light' : 'dark');
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const icon = document.getElementById('theme-icon');
            if (document.body.classList.contains('light-mode')) icon.classList.replace('bx-sun', 'bx-moon');
            else icon.classList.replace('bx-moon', 'bx-sun');
        }

        function toggleLanguage() {
            const currentLang = localStorage.getItem('lang') || 'ar';
            const newLang = currentLang === 'ar' ? 'en' : 'ar';
            localStorage.setItem('lang', newLang);
            applyLanguage(newLang);
        }

        function applyLanguage(lang) {
            document.documentElement.lang = lang;
            document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
            document.getElementById('lang-btn').innerText = lang === 'ar' ? 'EN' : 'عربي';
            document.body.style.fontFamily = lang === 'en' ? "'Outfit', sans-serif" : "'Cairo', sans-serif";

            const t = txt[lang];
            document.getElementById('nav-logo').innerText = t.navLogo;
            document.getElementById('nav-home').innerText = t.navHome;
            document.getElementById('nav-signup').innerText = t.navSignup;
            document.getElementById('header-title').innerText = t.headerTitle;
            document.getElementById('header-desc').innerText = t.headerDesc;
            document.getElementById('lbl-email').innerText = t.lblEmail;
            document.getElementById('lbl-pass').innerText = t.lblPass;
            document.getElementById('btn-submit').innerText = t.btnSubmit;
            document.getElementById('footer-txt').innerText = t.footerTxt;
            document.getElementById('footer-link').innerText = t.footerLink;

            document.getElementById('inp-email').placeholder = t.phEmail;
            document.getElementById('inp-pass').placeholder = t.phPass;

            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach(g => g.style.textAlign = lang === 'ar' ? 'right' : 'left');
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(a => a.style.textAlign = lang === 'ar' ? 'right' : 'left');
        }

        // Initial Load
        const storedTheme = localStorage.getItem('theme') || 'dark';
        if (storedTheme === 'light') document.body.classList.add('light-mode');
        updateThemeIcon();

        const storedLang = localStorage.getItem('lang') || 'ar';
        applyLanguage(storedLang);
    </script>
</body>

</html>

