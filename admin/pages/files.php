<?php
session_start();
require_once("../includes/auth.php");
require_once("../includes/file_manager.php");

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

$fileManager = new FileManager('../../uploads');

// จัดการการอัพโหลดไฟล์
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $result = $fileManager->upload($_FILES['file']);
    if ($result['success']) {
        $_SESSION['success'] = 'อัพโหลดไฟล์สำเร็จ';
    } else {
        $_SESSION['error'] = $result['error'];
    }
    header("Location: files.php");
    exit;
}

// จัดการการลบไฟล์
if (isset($_GET['delete'])) {
    $filename = $_GET['delete'];
    if ($fileManager->delete($filename)) {
        $_SESSION['success'] = 'ลบไฟล์สำเร็จ';
    } else {
        $_SESSION['error'] = 'ไม่สามารถลบไฟล์ได้';
    }
    header("Location: files.php");
    exit;
}

// ดึงรายการไฟล์
$files = $fileManager->getFileList();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการไฟล์ - ระบบเลือกตั้งออนไลน์</title>
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
        .btn-upload {
            background-color: #3498db;
            color: white;
            transition: all 0.3s;
        }
        .btn-upload:hover {
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
                    <h1 class="h2">จัดการไฟล์</h1>
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

                <!-- อัพโหลดไฟล์ -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">อัพโหลดไฟล์</h5>
                        <form action="" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                            <div class="col-auto">
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-upload">
                                    <i class="fas fa-upload me-2"></i>อัพโหลด
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- รายการไฟล์ -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">รายการไฟล์</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ชื่อไฟล์</th>
                                        <th>ประเภท</th>
                                        <th>ขนาด</th>
                                        <th>แก้ไขล่าสุด</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($file['name']); ?></td>
                                        <td><?php echo htmlspecialchars($file['type']); ?></td>
                                        <td><?php echo number_format($file['size'] / 1024, 2); ?> KB</td>
                                        <td><?php echo date('d/m/Y H:i', $file['modified']); ?></td>
                                        <td>
                                            <a href="../../uploads/<?php echo urlencode($file['name']); ?>" 
                                               class="btn btn-sm btn-info" 
                                               target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?delete=<?php echo urlencode($file['name']); ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('ยืนยันการลบไฟล์?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($files)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">ไม่พบไฟล์</td>
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