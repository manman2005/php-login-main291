<?php
session_start();
require_once("../includes/auth.php");

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

$objCon = connectDB();

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$objCon) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
}

// ดึงการตั้งค่าระบบ
$sql = "SELECT * FROM system_settings";
$result = mysqli_query($objCon, $sql);

if (!$result) {
    error_log("Error in settings query: " . mysqli_error($objCon));
    die("เกิดข้อผิดพลาดในการดึงข้อมูลการตั้งค่า");
}

$settings = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่าระบบ - ระบบเลือกตั้งออนไลน์</title>
    <!-- Bootstrap 5 -->
    <link href="../../bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,.75);
        }
        .nav-link:hover {
            color: white;
        }
        .nav-link.active {
            background: rgba(255,255,255,.1);
        }
        .stats-card {
            transition: all 0.3s ease;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .settings-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .settings-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding: 20px;
        }
        .settings-card .card-body {
            padding: 20px;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .btn-save {
            padding: 10px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include_once("../includes/sidebar.php"); ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-cog me-2"></i>ตั้งค่าระบบ</h1>
            </div>

            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>
            
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <form action="../actions/settings_action.php" method="POST">
                <!-- ตั้งค่าทั่วไป -->
                <div class="settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>ตั้งค่าทั่วไป</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="site_name" class="form-label">ชื่อเว็บไซต์</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                       value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="site_description" class="form-label">คำอธิบายเว็บไซต์</label>
                                <input type="text" class="form-control" id="site_description" name="site_description" 
                                       value="<?php echo htmlspecialchars($settings['site_description'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="admin_email" class="form-label">อีเมลผู้ดูแลระบบ</label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                       value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="items_per_page" class="form-label">จำนวนรายการต่อหน้า</label>
                                <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                                       value="<?php echo htmlspecialchars($settings['items_per_page'] ?? '10'); ?>" 
                                       min="5" max="100" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ตั้งค่าการเลือกตั้ง -->
                <div class="settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-vote-yea me-2"></i>ตั้งค่าการเลือกตั้ง</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_candidates" class="form-label">จำนวนผู้สมัครสูงสุดต่อการเลือกตั้ง</label>
                                <input type="number" class="form-control" id="max_candidates" name="max_candidates" 
                                       value="<?php echo htmlspecialchars($settings['max_candidates'] ?? '10'); ?>" 
                                       min="1" max="100" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="voting_duration" class="form-label">ระยะเวลาการเลือกตั้งเริ่มต้น (นาที)</label>
                                <input type="number" class="form-control" id="voting_duration" name="voting_duration" 
                                       value="<?php echo htmlspecialchars($settings['voting_duration'] ?? '60'); ?>" 
                                       min="5" max="1440" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="allow_multiple_votes" class="form-label">อนุญาตให้เลือกได้หลายครั้ง</label>
                                <select class="form-select" id="allow_multiple_votes" name="allow_multiple_votes">
                                    <option value="0" <?php echo (!isset($settings['allow_multiple_votes']) || $settings['allow_multiple_votes'] == 0) ? 'selected' : ''; ?>>ไม่อนุญาต</option>
                                    <option value="1" <?php echo (isset($settings['allow_multiple_votes']) && $settings['allow_multiple_votes'] == 1) ? 'selected' : ''; ?>>อนุญาต</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="require_verification" class="form-label">ต้องยืนยันตัวตนก่อนลงคะแนน</label>
                                <select class="form-select" id="require_verification" name="require_verification">
                                    <option value="0" <?php echo (!isset($settings['require_verification']) || $settings['require_verification'] == 0) ? 'selected' : ''; ?>>ไม่ต้องยืนยัน</option>
                                    <option value="1" <?php echo (isset($settings['require_verification']) && $settings['require_verification'] == 1) ? 'selected' : ''; ?>>ต้องยืนยัน</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ตั้งค่าการแจ้งเตือน -->
                <div class="settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bell me-2"></i>ตั้งค่าการแจ้งเตือน</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="enable_email_notifications" class="form-label">เปิดใช้งานการแจ้งเตือนทางอีเมล</label>
                                <select class="form-select" id="enable_email_notifications" name="enable_email_notifications">
                                    <option value="0" <?php echo (!isset($settings['enable_email_notifications']) || $settings['enable_email_notifications'] == 0) ? 'selected' : ''; ?>>ปิด</option>
                                    <option value="1" <?php echo (isset($settings['enable_email_notifications']) && $settings['enable_email_notifications'] == 1) ? 'selected' : ''; ?>>เปิด</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="notification_before_end" class="form-label">แจ้งเตือนก่อนสิ้นสุดการเลือกตั้ง (นาที)</label>
                                <input type="number" class="form-control" id="notification_before_end" name="notification_before_end" 
                                       value="<?php echo htmlspecialchars($settings['notification_before_end'] ?? '30'); ?>" 
                                       min="5" max="120">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mb-4">
                    <button type="submit" class="btn btn-primary btn-save">
                        <i class="fas fa-save me-2"></i>บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>
</body>
</html> 