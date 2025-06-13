<?php
require_once(__DIR__ . "/../../includes/config.php");
session_start();
require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/backup.php");

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

$backup = new BackupSystem();

// จัดการการสำรองข้อมูล
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'backup_db':
            $result = $backup->backupDatabase();
            if ($result['success']) {
                $_SESSION['success'] = 'สำรองฐานข้อมูลสำเร็จ';
            } else {
                $_SESSION['error'] = $result['error'];
            }
            break;
            
        case 'backup_files':
            $result = $backup->backupFiles();
            if ($result['success']) {
                $_SESSION['success'] = 'สำรองไฟล์สำเร็จ';
            } else {
                $_SESSION['error'] = $result['error'];
            }
            break;
            
        case 'restore_db':
            if (isset($_FILES['backup_file'])) {
                $result = $backup->restoreDatabase($_FILES['backup_file']['tmp_name']);
                if ($result['success']) {
                    $_SESSION['success'] = 'กู้คืนฐานข้อมูลสำเร็จ';
                } else {
                    $_SESSION['error'] = $result['error'];
                }
            }
            break;
            
        case 'restore_files':
            if (isset($_FILES['backup_file'])) {
                $result = $backup->restoreFiles($_FILES['backup_file']['tmp_name']);
                if ($result['success']) {
                    $_SESSION['success'] = 'กู้คืนไฟล์สำเร็จ';
                } else {
                    $_SESSION['error'] = $result['error'];
                }
            }
            break;
    }
    
    header("Location: backup.php");
    exit;
}

// จัดการการลบไฟล์สำรอง
if (isset($_GET['delete'])) {
    $filename = $_GET['delete'];
    if ($backup->deleteBackup($filename)) {
        $_SESSION['success'] = 'ลบไฟล์สำรองสำเร็จ';
    } else {
        $_SESSION['error'] = 'ไม่สามารถลบไฟล์สำรองได้';
    }
    header("Location: backup.php");
    exit;
}

// ดึงรายการไฟล์สำรอง
$backups = $backup->getBackupList();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการสำรองข้อมูล - ระบบเลือกตั้งออนไลน์</title>
    <!-- Bootstrap 5 -->
    <link href="../../bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f5f5f5;
        }
        .sidebar {
            background-color: #2c3e50;
            min-height: 100vh;
        }
        .nav-link {
            color: #ecf0f1;
            transition: all 0.3s;
        }
        .nav-link:hover {
            background-color: #34495e;
            color: #ffffff;
        }
        .nav-link.active {
            background-color: #3498db;
            color: #ffffff;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn-backup {
            background-color: #3498db;
            color: white;
            transition: all 0.3s;
        }
        .btn-backup:hover {
            background-color: #2980b9;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include_once("../includes/sidebar.php"); ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">จัดการการสำรองข้อมูล</h1>
                </div>

                <!-- แสดงข้อความแจ้งเตือน -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- การสำรองข้อมูล -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">สำรองข้อมูล</h5>
                                <form action="" method="POST" class="mb-3">
                                    <input type="hidden" name="action" value="backup_db">
                                    <button type="submit" class="btn btn-backup">
                                        <i class="fas fa-database me-2"></i>สำรองฐานข้อมูล
                                    </button>
                                </form>
                                <form action="" method="POST">
                                    <input type="hidden" name="action" value="backup_files">
                                    <button type="submit" class="btn btn-backup">
                                        <i class="fas fa-file-archive me-2"></i>สำรองไฟล์
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">กู้คืนข้อมูล</h5>
                                <form action="" method="POST" enctype="multipart/form-data" class="mb-3">
                                    <input type="hidden" name="action" value="restore_db">
                                    <div class="mb-3">
                                        <label class="form-label">กู้คืนฐานข้อมูล</label>
                                        <input type="file" name="backup_file" class="form-control" accept=".zip" required>
                                    </div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-undo me-2"></i>กู้คืนฐานข้อมูล
                                    </button>
                                </form>
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="restore_files">
                                    <div class="mb-3">
                                        <label class="form-label">กู้คืนไฟล์</label>
                                        <input type="file" name="backup_file" class="form-control" accept=".zip" required>
                                    </div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-undo me-2"></i>กู้คืนไฟล์
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- รายการไฟล์สำรอง -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">รายการไฟล์สำรอง</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ชื่อไฟล์</th>
                                        <th>ประเภท</th>
                                        <th>ขนาด</th>
                                        <th>วันที่</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backups as $file): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($file['name']); ?></td>
                                        <td><?php echo strtoupper($file['type']); ?></td>
                                        <td><?php echo number_format($file['size'] / 1024 / 1024, 2); ?> MB</td>
                                        <td><?php echo date('d/m/Y H:i', $file['date']); ?></td>
                                        <td>
                                            <a href="../../backups/<?php echo urlencode($file['name']); ?>" 
                                               class="btn btn-sm btn-info" 
                                               download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="?delete=<?php echo urlencode($file['name']); ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('ยืนยันการลบไฟล์สำรอง?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($backups)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">ไม่พบไฟล์สำรอง</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>
</body>
</html> 