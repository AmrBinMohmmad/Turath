<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

session_start();
require_once 'db.php';
// ... (كود PHP الخاص بإنشاء الحساب كما هو) ...
if (isset($_SESSION['user_id'])) {
    header("Location: user_page.php");
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
    <style>
        /* ... نفس الستايل السابق مع إضافة كلاسات الوضع الفاتح ... */
        :root {
            --bg-main: #020617;
            --bg-secondary: #0f172a;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.05);
            --saudi-green: #006C35;
            --gold-light: #F2D06B;
            --gold-dark: #C69320;
            --accent-red: #ef4444;
            --accent-green: #10b981;
        }

        body.light-mode {
            --bg-main: #f8fafc;
            --bg-secondary: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: all 0.3s ease;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            background: rgba(var(--bg-main), 0.9);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--glass-border);
        }

        .logo {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            color: #000;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        /* زر الأيقونة */
        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--glass-border);
            background: var(--glass-bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            font-size: 18px;
        }

        .icon-btn:hover {
            background: var(--saudi-green);
            color: white;
            border-color: var(--saudi-green);
        }

        .main-container {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 50px;
            background: radial-gradient(circle at 50% 50%, rgba(0, 108, 53, 0.1), transparent 70%);
            position: relative;
        }

        .main-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.transparenttextures.com/patterns/arabesque.png');
            opacity: 0.04;
            pointer-events: none;
        }

        .auth-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 25px;
            width: 100%;
            max-width: 500px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .auth-header {
            margin-bottom: 30px;
        }

        .auth-header i {
            font-size: 50px;
            color: var(--gold-light);
            margin-bottom: 10px;
        }

        .auth-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            color: var(--text-main);
        }

        .auth-header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: right;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .input-box {
            position: relative;
        }

        .input-box i {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 20px;
        }

        /* تعديل اتجاه الأيقونة في الإنجليزي */
        body[dir="ltr"] .input-box i {
            right: auto;
            left: 15px;
        }

        body[dir="ltr"] input {
            padding: 14px 15px 14px 50px;
        }

        input {
            width: 100%;
            padding: 14px 50px 14px 15px;
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-main);
            font-family: inherit;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: var(--gold-light);
            box-shadow: 0 0 0 4px rgba(242, 208, 107, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            color: #000;
            font-weight: 800;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
            box-shadow: 0 5px 15px rgba(198, 147, 32, 0.2);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(198, 147, 32, 0.4);
        }

        .footer-text {
            margin-top: 25px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .footer-text a {
            color: var(--gold-light);
            text-decoration: none;
            font-weight: bold;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: right;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .success {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        @media (max-width: 500px) {
            .auth-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="logo"><i class='bx bxl-flutter' style="color:var(--saudi-green)"></i> <span
                id="nav-logo">تراث المملكة</span></a>
        <div class="nav-actions">
            <a href="index.php" style="color:var(--text-muted); text-decoration:none; font-size:14px; margin-left:15px;"
                id="nav-home">الرئيسية</a>
            <a href="login.php" class="btn-gold" id="nav-login">دخول</a>
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
                navLogo: "تراث المملكة", navHome: "الرئيسية", navLogin: "دخول",
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