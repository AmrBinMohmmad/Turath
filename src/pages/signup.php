<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db.php';
// ... (كود PHP الخاص بإنشاء الحساب كما هو) ...
if (isset($_SESSION['user_id'])) {
    header("Location: user/user_page.php");
    exit();
}
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (نفس المنطق) ...
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($pass !== $confirm_pass) {
        $message = "<div class='alert error'><i class='bx bx-error'></i> كلمات المرور غير متطابقة!</div>";
    } else {
        $check = $conn->query("SELECT id FROM if0_40458841_users_db.users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $message = "<div class='alert error'><i class='bx bx-user-x'></i> البريد الإلكتروني مسجل مسبقاً!</div>";
        } else {
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO if0_40458841_users_db.users (username, email, password, xp, level) VALUES ('$username', '$email', '$hashed_pass', 0, 1)";
            if ($conn->query($sql) === TRUE) {
                $message = "<div class='alert success'><i class='bx bx-check-circle'></i> تم إنشاء الحساب بنجاح! جاري التوجيه...</div>";
                echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
            } else {
                $message = "<div class='alert error'>حدث خطأ: " . $conn->error . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title">إنشاء حساب | تراث المملكة</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/signup_page.css">
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
            <a href="login.php" class="btn-gold" id="nav-login">دخول</a>
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
                <i class='bx bxs-user-plus'></i>
                <h1 id="header-title">إنشاء حساب جديد</h1>
                <p id="header-desc">كن جزءاً من مجتمعنا وابدأ رحلة التعلم</p>
            </div>

            <?= $message ?>

            <form method="POST">
                <div class="form-group">
                    <label id="lbl-user">اسم المستخدم</label>
                    <div class="input-box">
                        <i class='bx bx-user'></i>
                        <input type="text" name="username" id="inp-user" placeholder="الاسم الذي سيظهر في الموقع"
                            required>
                    </div>
                </div>

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

                <div class="form-group">
                    <label id="lbl-confirm">تأكيد كلمة المرور</label>
                    <div class="input-box">
                        <i class='bx bx-check-shield'></i>
                        <input type="password" name="confirm_password" id="inp-confirm" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btn-submit">تسجيل الحساب</button>
            </form>

            <div class="footer-text">
                <span id="footer-txt">لديك حساب بالفعل؟</span> <a href="login.php" id="footer-link">تسجيل الدخول</a>
            </div>
        </div>
    </div>

    <script>
        const txt = {
            ar: {
                navLogo: "منصة تراث", navHome: "الرئيسية", navLogin: "دخول",
                headerTitle: "إنشاء حساب جديد", headerDesc: "كن جزءاً من مجتمعنا وابدأ رحلة التعلم",
                lblUser: "اسم المستخدم", phUser: "الاسم الذي سيظهر في الموقع",
                lblEmail: "البريد الإلكتروني", phEmail: "example@mail.com",
                lblPass: "كلمة المرور", phPass: "••••••••",
                lblConfirm: "تأكيد كلمة المرور", phConfirm: "••••••••",
                btnSubmit: "تسجيل الحساب",
                footerTxt: "لديك حساب بالفعل؟", footerLink: "تسجيل الدخول"
            },
            en: {
                navLogo: "Torath Platform", navHome: "Home", navLogin: "Login",
                headerTitle: "Create New Account", headerDesc: "Join our community and start your journey",
                lblUser: "Username", phUser: "Display name on site",
                lblEmail: "Email Address", phEmail: "example@mail.com",
                lblPass: "Password", phPass: "••••••••",
                lblConfirm: "Confirm Password", phConfirm: "••••••••",
                btnSubmit: "Sign Up",
                footerTxt: "Already have an account?", footerLink: "Sign In"
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
            // Update Text
            document.getElementById('nav-logo').innerText = t.navLogo;
            document.getElementById('nav-home').innerText = t.navHome;
            document.getElementById('nav-login').innerText = t.navLogin;
            document.getElementById('header-title').innerText = t.headerTitle;
            document.getElementById('header-desc').innerText = t.headerDesc;
            document.getElementById('lbl-user').innerText = t.lblUser;
            document.getElementById('lbl-email').innerText = t.lblEmail;
            document.getElementById('lbl-pass').innerText = t.lblPass;
            document.getElementById('lbl-confirm').innerText = t.lblConfirm;
            document.getElementById('btn-submit').innerText = t.btnSubmit;
            document.getElementById('footer-txt').innerText = t.footerTxt;
            document.getElementById('footer-link').innerText = t.footerLink;

            // Update Placeholders
            document.getElementById('inp-user').placeholder = t.phUser;
            document.getElementById('inp-email').placeholder = t.phEmail;
            document.getElementById('inp-pass').placeholder = t.phPass;
            document.getElementById('inp-confirm').placeholder = t.phConfirm;

            // Fix Form alignment
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


