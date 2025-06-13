<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h5 class="text-white">Admin Panel</h5>
            <small class="text-muted">ระบบจัดการเลือกตั้ง</small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/index.php">
                    <i class="fas fa-home me-2"></i>แดชบอร์ด
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'elections.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/elections.php">
                    <i class="fas fa-vote-yea me-2"></i>จัดการการเลือกตั้ง
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'candidates.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/candidates.php">
                    <i class="fas fa-users me-2"></i>จัดการผู้สมัคร
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/users.php">
                    <i class="fas fa-user me-2"></i>จัดการผู้ใช้
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/reports.php">
                    <i class="fas fa-chart-bar me-2"></i>รายงาน
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/announcements.php">
                    <i class="fas fa-bullhorn me-2"></i>ประกาศ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'messages.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/messages.php">
                    <i class="fas fa-envelope me-2"></i>ข้อความ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'files.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/files.php">
                    <i class="fas fa-folder me-2"></i>จัดการไฟล์
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'backup.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/backup.php">
                    <i class="fas fa-database me-2"></i>สำรองข้อมูล
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>" 
                   href="/php-login-main291/admin/pages/settings.php">
                    <i class="fas fa-cog me-2"></i>ตั้งค่า
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="/php-login-main291/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.sidebar {
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