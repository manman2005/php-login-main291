<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark text-white">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Admin Panel</h4>
            <div style="font-size: 0.95rem; color: #b0b0b0;">ระบบจัดการเลือกตั้ง</div>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'index.php') echo ' active'; ?>" href="index.php">
                    <i class="fas fa-home"></i> แดชบอร์ด
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'elections.php') echo ' active'; ?>" href="elections.php">
                    <i class="fas fa-ballot-check"></i> จัดการการเลือกตั้ง
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'candidates.php') echo ' active'; ?>" href="candidates.php">
                    <i class="fas fa-users"></i> จัดการผู้สมัคร
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'users.php') echo ' active'; ?>" href="users.php">
                    <i class="fas fa-user"></i> จัดการผู้ใช้
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'reports.php') echo ' active'; ?>" href="reports.php">
                    <i class="fas fa-chart-bar"></i> รายงาน
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'announcements.php') echo ' active'; ?>" href="announcements.php">
                    <i class="fas fa-bullhorn"></i> ประกาศ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'messages.php') echo ' active'; ?>" href="messages.php">
                    <i class="fas fa-envelope"></i> ข้อความ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'files.php') echo ' active'; ?>" href="files.php">
                    <i class="fas fa-folder"></i> จัดการไฟล์
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'backup.php') echo ' active'; ?>" href="backup.php">
                    <i class="fas fa-database"></i> สำรองข้อมูล
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php if($current_page == 'settings.php') echo ' active'; ?>" href="settings.php">
                    <i class="fas fa-cog"></i> ตั้งค่า
                </a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link" href="../../logout.php">
                    <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.sidebar {
    background: #23272b !important;
    color: #fff !important;
    min-height: 100vh;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.nav-link {
    color: rgba(255,255,255,.8) !important;
    padding: 0.7rem 1rem;
    border-radius: 5px;
    margin: 2px 0;
    transition: all 0.3s ease;
}

.nav-link:hover {
    color: white !important;
    background: rgba(255,255,255,.1);
    transform: translateX(5px);
}

.nav-link.active {
    color: white !important;
    background: linear-gradient(90deg, #0d6efd, #0dcaf0);
}

.nav-link i {
    width: 20px;
    text-align: center;
}
</style>