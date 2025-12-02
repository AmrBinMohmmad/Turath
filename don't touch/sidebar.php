<?php
// sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
// تحديد هل الصفحة للأدمن أم للمستخدم
$isAdminPage = (strpos($current_page, 'admin') === 0 || strpos($current_page, 'create') === 0);
?>

<aside class="sidebar" id="sidebar">
    <div class="logo">
        <i class='bx <?= $isAdminPage ? "bxs-lock-alt" : "bxl-flutter" ?>'></i>
        <?= $isAdminPage ? "Admin<span>Panel</span>" : "EduPro" ?>
    </div>
    
    <nav>
        <?php if ($isAdminPage): ?>
            <a href="admin_page.php" class="nav-item <?= $current_page == 'admin_page.php' ? 'active' : '' ?>">
                <i class='bx bxs-dashboard'></i> الرئيسية
            </a>
            
            <a href="admin_cards_list.php" class="nav-item <?= ($current_page == 'admin_cards_list.php' || $current_page == 'admin_card_users.php' || $current_page == 'admin_user_analysis.php') ? 'active' : '' ?>">
                <i class='bx bxs-graduation'></i> نتائج الطلاب
            </a>

            <a href="admin_project_answers.php" class="nav-item <?= $current_page == 'admin_project_answers.php' ? 'active' : '' ?>">
                <i class='bx bxs-data'></i> تحليل الإجابات
            </a>
            <a href="admin_user_answers_detail.php" class="nav-item <?= $current_page == 'admin_user_answers_detail.php' ? 'active' : '' ?>">
                <i class='bx bxs-user-detail'></i> تفاصيل الطلاب
            </a>
            <a href="create_card_admin_site.php" class="nav-item <?= $current_page == 'create_card_admin_site.php' ? 'active' : '' ?>">
                <i class='bx bxs-plus-square'></i> إضافة كارد
            </a>

        <?php else: ?>
            <a href="user_page.php" class="nav-item <?= $current_page == 'user_page.php' ? 'active' : '' ?>">
                <i class='bx bxs-dashboard'></i> لوحة التحكم
            </a>
            <a href="#" class="nav-item">
                <i class='bx bxs-folder'></i> المشاريع
            </a>
            <a href="#" class="nav-item">
                <i class='bx bxs-trophy'></i> الإنجازات
            </a>
        <?php endif; ?>
    </nav>

    <div style="margin-top: auto;">
        <a href="logout.php" class="nav-item" style="color: #ef4444;">
            <i class='bx bx-log-out'></i> تسجيل خروج
        </a>
    </div>
</aside>

<div id="overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:900;" onclick="toggleMenu()"></div>

<script>
    function toggleMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mainContent = document.getElementById('mainContent');
        
        if (window.innerWidth <= 1100) {
            sidebar.classList.toggle('active');
            overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
        } else {
            sidebar.classList.toggle('close');
            if(mainContent) mainContent.classList.toggle('expand');
        }
    }
    
    // تأكد من وجود الزر قبل إضافة الحدث لتجنب الأخطاء
    document.addEventListener("DOMContentLoaded", function() {
        const menuBtn = document.getElementById('menuBtn');
        if(menuBtn) menuBtn.addEventListener('click', toggleMenu);
    });
</script>