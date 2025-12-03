<?php
session_start();
require_once 'db.php';

// إذا كان مسجلاً مسبقاً، نتحقق من دوره ونوجهه
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin_page.php");
    } else {
        header("Location: user_page.php");
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
            // تسجيل بيانات الجلسة
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // حفظ الدور في الجلسة

            // --- التوجيه الذكي ---
            if ($row['role'] === 'admin') {
                header("Location: admin_page.php");
            } else {
                header("Location: regions.php"); // أو user_page.php حسب تفضيلك
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
    <title>تسجيل الدخول | تراث المملكة</title>
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;800&family=Outfit:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-main: #020617; --bg-secondary: #0f172a; --text-main: #f8fafc; --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 255, 255, 0.05);
            --saudi-green: #006C35; --gold-light: #F2D06B; --gold-dark: #C69320;
            --accent-red: #ef4444;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; transition: all 0.3s ease; }
        body { font-family: 'Cairo', sans-serif; background-color: var(--bg-main); color: var(--text-main); overflow-x: hidden; min-height: 100vh; display: flex; flex-direction: column; }

        /* Navbar */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; position: fixed; width: 100%; top: 0; z-index: 1000; background: rgba(var(--bg-main), 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid var(--glass-border); }
        .logo { font-size: 26px; font-weight: 800; color: var(--text-main); text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .nav-actions { display: flex; align-items: center; gap: 15px; }
        .btn-gold { background: linear-gradient(135deg, var(--gold-light), var(--gold-dark)); color: #000; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 14px; }

        /* Main Container */
        .main-container { 
            flex-grow: 1; display: flex; align-items: center; justify-content: center; padding: 100px 20px 50px;
            background: radial-gradient(circle at 50% 50%, rgba(0, 108, 53, 0.1), transparent 70%);
            position: relative;
        }
        .main-container::before { content:''; position:absolute; top:0; left:0; width:100%; height:100%; background: url('https://www.transparenttextures.com/patterns/arabesque.png'); opacity: 0.04; pointer-events: none; }

        /* Auth Card */
        .auth-card {
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            padding: 40px; border-radius: 25px; width: 100%; max-width: 450px;
            backdrop-filter: blur(20px); box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            text-align: center; position: relative; overflow: hidden;
        }
        
        .auth-header { margin-bottom: 30px; }
        .auth-header i { font-size: 50px; color: var(--saudi-green); margin-bottom: 10px; }
        .auth-header h1 { font-size: 28px; margin-bottom: 5px; color: var(--text-main); }
        .auth-header p { color: var(--text-muted); font-size: 14px; }

        /* Form */
        .form-group { margin-bottom: 20px; text-align: right; }
        label { display: block; margin-bottom: 8px; font-size: 14px; color: #cbd5e1; font-weight: 600; }
        
        .input-box { position: relative; }
        .input-box i { position: absolute; top: 50%; right: 15px; transform: translateY(-50%); color: var(--text-muted); font-size: 20px; }
        
        input {
            width: 100%; padding: 14px 50px 14px 15px; background: var(--bg-secondary);
            border: 1px solid var(--glass-border); border-radius: 12px; color: white;
            font-family: inherit; font-size: 15px; outline: none;
        }
        input:focus { border-color: var(--gold-light); box-shadow: 0 0 0 4px rgba(242, 208, 107, 0.1); }

        .btn-submit {
            width: 100%; padding: 14px; border: none; border-radius: 12px;
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            color: #000; font-weight: 800; font-size: 16px; cursor: pointer;
            margin-top: 10px; box-shadow: 0 5px 15px rgba(198, 147, 32, 0.2);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(198, 147, 32, 0.4); }

        .footer-text { margin-top: 25px; font-size: 14px; color: var(--text-muted); }
        .footer-text a { color: var(--gold-light); text-decoration: none; font-weight: bold; }
        .footer-text a:hover { text-decoration: underline; }

        /* Alerts */
        .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; text-align: right; }
        .error { background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
        
        @media (max-width: 500px) { .auth-card { padding: 30px 20px; } }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo"><i class='bx bxl-flutter' style="color:var(--saudi-green)"></i> تراث المملكة</a>
        <div class="nav-actions">
            <a href="index.php" style="color:var(--text-muted); text-decoration:none; font-size:14px; margin-left:15px;">الرئيسية</a>
            <a href="signup.php" class="btn-gold">حساب جديد</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class='bx bx-user-circle'></i>
                <h1>أهلاً بك مجدداً</h1>
                <p>سجل دخولك لمتابعة رحلة التحدي</p>
            </div>

            <?= $message ?>

            <form method="POST">
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <div class="input-box">
                        <i class='bx bx-envelope'></i>
                        <input type="email" name="email" placeholder="example@mail.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>كلمة المرور</label>
                    <div class="input-box">
                        <i class='bx bx-lock-alt'></i>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">تسجيل الدخول</button>
            </form>

            <div class="footer-text">
                ليس لديك حساب؟ <a href="signup.php">انضم إلينا الآن</a>
            </div>
        </div>
    </div>

</body>
</html>