<?php
// ... (نفس كود PHP الخاص بك في الأعلى بدون تغيير) ...
$current_page = basename($_SERVER['PHP_SELF']);
$isAdminPage = (strpos($current_page, 'admin') === 0 || strpos($current_page, 'create') === 0);
?>

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<style>
    /* المتغيرات العامة للألوان */
    :root {
        --sidebar-width: 260px;
        --sidebar-bg: #fff;
        --text-color: #333;
        --active-color: #4f46e5;
        --hover-bg: #f3f4f6;
        --transition: all 0.3s ease;
    }

    /* تنسيق القائمة الجانبية الأساسي */
    .sidebar {
        position: fixed;
        top: 0;
        right: 0; /* لأن المحتوى عربي، القائمة يمين */
        height: 100%;
        width: var(--sidebar-width);
        background: var(--sidebar-bg);
        padding: 20px 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: -2px 0 5px rgba(0,0,0,0.1);
        z-index: 1000;
        transition: var(--transition);
        overflow-y: auto;
    }

    /* الشعار */
    .sidebar .logo {
        font-size: 24px;
        font-weight: bold;
        color: var(--active-color);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 30px;
        padding: 0 10px;
    }

    /* عناصر القائمة */
    .nav-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 15px;
        text-decoration: none;
        color: var(--text-color);
        border-radius: 8px;
        margin-bottom: 5px;
        transition: var(--transition);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .nav-item:hover {
        background: var(--hover-bg);
    }

    .nav-item.active {
        background: var(--active-color);
        color: #fff;
    }

    .nav-item i {
        font-size: 22px;
    }

    /* زر القائمة (يظهر فقط في الجوال) */
    .menu-toggle-btn {
        display: none; /* مخفي في الكمبيوتر */
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--active-color);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        font-size: 24px;
        cursor: pointer;
        z-index: 1100;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* --- Media Queries (نقطة التجاوب) --- */
    
    /* للشاشات الصغيرة (أقل من 1100px حسب الكود الخاص بك) */
    @media (max-width: 1100px) {
        .sidebar {
            right: calc(var(--sidebar-width) * -1); /* إخفاء القائمة خارج الشاشة */
        }

        /* كلاس active يضاف بالجافاسكربت لإظهار القائمة */
        .sidebar.active {
            right: 0; 
        }

        /* إظهار زر القائمة في الجوال */
        .menu-toggle-btn {
            display: block;
        }
        
        /* تعديل وضع الزر إذا كانت القائمة مفتوحة */
        .menu-toggle-btn.moved {
            right: var(--sidebar-width); 
            background: transparent;
            color: var(--text-color);
        }
    }

    /* للشاشات الكبيرة (أكبر من 1100px) - حالة الانكماش */
    @media (min-width: 1101px) {
        .sidebar.close {
            width: 80px;
        }
        
        .sidebar.close .logo span, 
        .sidebar.close .nav-item span,
        .sidebar.close .logo {
            justify-content: center;
        }
        
        /* إخفاء النصوص عند الانكماش */
        .sidebar.close .nav-item {
            justify-content: center;
        }
        
        /* خدعة لإخفاء النص مع الإبقاء على الأيقونة */
        .sidebar.close .logo {
            font-size: 0; /* إخفاء نص الشعار */
        }
        .sidebar.close .logo i {
            font-size: 24px; /* إعادة حجم الأيقونة */
        }
        
        .sidebar.close .nav-item {
            font-size: 0;
        }
        .sidebar.close .nav-item i {
            font-size: 24px;
        }
    }
</style>

<button class="menu-toggle-btn" id="menuBtn">
    <i class='bx bx-menu'></i>
</button>

<aside class="sidebar" id="sidebar">
    <div class="logo">
        <i class='bx <?= $isAdminPage ? "bxs-lock-alt" : "bxl-flutter" ?>'></i>
        <span><?= $isAdminPage ? "Admin Panel" : "EduPro" ?></span>
    </div>

    <nav>
        <?php if ($isAdminPage): ?>
            <a href="admin_page.php" class="nav-item <?= $current_page == 'admin_page.php' ? 'active' : '' ?>">
                <i class='bx bxs-dashboard'></i> <span>الرئيسية</span>
            </a>
            <a href="admin_cards_list.php" class="nav-item <?= ($current_page == 'admin_cards_list.php' || $current_page == 'admin_card_users.php' || $current_page == 'admin_user_analysis.php') ? 'active' : '' ?>">
                <i class='bx bxs-graduation'></i> <span>نتائج الطلاب</span>
            </a>
            <a href="admin_project_answers.php" class="nav-item <?= $current_page == 'admin_project_answers.php' ? 'active' : '' ?>">
                <i class='bx bxs-data'></i> <span>تحليل الإجابات</span>
            </a>
            <a href="admin_user_answers_detail.php" class="nav-item <?= $current_page == 'admin_user_answers_detail.php' ? 'active' : '' ?>">
                <i class='bx bxs-user-detail'></i> <span>تفاصيل الطلاب</span>
            </a>
            <a href="create_card_admin_site.php" class="nav-item <?= $current_page == 'create_card_admin_site.php' ? 'active' : '' ?>">
                <i class='bx bxs-plus-square'></i> <span>إضافة كارد</span>
            </a>
            <a href="admin_import_data.php" class="nav-item <?= $current_page == 'admin_import_questions.php' ? 'active' : '' ?>">
                <i class='bx bx-import'></i> <span>استيراد أسئلة</span>
            </a>
        <?php else: ?>
            <a href="user_page.php" class="nav-item <?= $current_page == 'user_page.php' ? 'active' : '' ?>">
                <i class='bx bxs-dashboard'></i> <span>لوحة التحكم</span>
            </a>
            <a href="#" class="nav-item">
                <i class='bx bxs-folder'></i> <span>المشاريع</span>
            </a>
            <a href="#" class="nav-item">
                <i class='bx bxs-trophy'></i> <span>الإنجازات</span>
            </a>
        <?php endif; ?>
    </nav>

    <div style="margin-top: auto;">
        <a href="logout.php" class="nav-item" style="color: #ef4444;">
            <i class='bx bx-log-out'></i> <span>تسجيل خروج</span>
        </a>
    </div>
</aside>

<div id="overlay" 
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:900;" 
     onclick="toggleMenu()">
</div>

<script>
    function toggleMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const menuBtn = document.getElementById('menuBtn');
        
        // التحقق من حجم الشاشة
        if (window.innerWidth <= 1100) {
            // منطق الجوال: إضافة كلاس active لإظهار القائمة
            sidebar.classList.toggle('active');
            
            // إظهار/إخفاء الخلفية السوداء
            overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
            
            // تحريك زر القائمة (اختياري)
            if (sidebar.classList.contains('active')) {
                menuBtn.classList.add('moved');
                menuBtn.innerHTML = "<i class='bx bx-x'></i>"; // تغيير الأيقونة إلى X
            } else {
                menuBtn.classList.remove('moved');
                menuBtn.innerHTML = "<i class='bx bx-menu'></i>"; // إعادة الأيقونة
            }
            
        } else {
            // منطق الكمبيوتر: تصغير/تكبير القائمة (Mini Sidebar)
            sidebar.classList.toggle('close');
            
            // التعامل مع المحتوى الرئيسي (Main Content) لتوسيع مساحته
            // ملاحظة: يجب أن يحتوي ملف الصفحة الرئيسي على div بالآيدي mainContent
            const mainContent = document.getElementById('mainContent');
            if (mainContent) {
                mainContent.classList.toggle('expand');
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const menuBtn = document.getElementById('menuBtn');
        if (menuBtn) {
            menuBtn.addEventListener('click', toggleMenu);
        }
    });
</script>
