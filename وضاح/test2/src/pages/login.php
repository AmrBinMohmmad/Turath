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
    <title id="page-title">تسجيل الدخول | تراث المملكة</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap"
        rel="stylesheet">
    <style>
        /* ... نفس الستايل الموحد مع كلاسات اللايت مود ... */
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

        /* Navbar */
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
            padding: 100px 20px 50px;
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
            max-width: 450px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header {
            margin-bottom: 30px;
        }

        .auth-header i {
            font-size: 50px;
            color: var(--saudi-green);
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
            margin-bottom: 20px;
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
            margin-top: 10px;
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

        @media (max-width: 500px) {
            .auth-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="../../index.php" class="logo"><i class='bx bxl-flutter' style="color:var(--saudi-green)"></i> <span
                id="nav-logo">تراث المملكة</span></a>
        <div class="nav-actions">
            <a href="../../index.php" style="color:var(--text-muted); text-decoration:none; font-size:14px; margin-left:15px;"
                id="nav-home">الرئيسية</a>
            <a href="signup.php" class="btn-gold" id="nav-signup">حساب جديد</a>
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
                navLogo: "تراث المملكة", navHome: "الرئيسية", navSignup: "حساب جديد",
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
