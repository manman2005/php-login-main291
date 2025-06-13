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

// ตรวจสอบว่ามี id ที่ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ไม่พบรหัสประกาศที่ต้องการแก้ไข";
    header("Location: announcements.php");
    exit;
}

$id = mysqli_real_escape_string($objCon, $_GET['id']);

// ดึงข้อมูลประกาศ
$sql = "SELECT * FROM announcements WHERE id = ?";
if ($stmt = mysqli_prepare($objCon, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $announcement = mysqli_fetch_assoc($result);
        
        if (!$announcement) {
            $_SESSION['error'] = "ไม่พบประกาศที่ต้องการแก้ไข";
            header("Location: announcements.php");
            exit;
        }
    } else {
        error_log("Error executing statement: " . mysqli_stmt_error($stmt));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการดึงข้อมูลประกาศ";
        header("Location: announcements.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Error preparing statement: " . mysqli_error($objCon));
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    header("Location: announcements.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขประกาศ - ระบบเลือกตั้งออนไลน์</title>
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
                <h1 class="h2"><i class="fas fa-edit me-2"></i>แก้ไขประกาศ</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="announcements.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>กลับ
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success" role="alert">
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php } ?>
            
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php } ?>

            <!-- Edit Form -->
            <div class="card stats-card">
                <div class="card-body">
                    <form action="../actions/announcement_action.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $announcement['id']; ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">หัวข้อ</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($announcement['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">เนื้อหา</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required><?php 
                                echo htmlspecialchars($announcement['content']); 
                            ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">สถานะ</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo $announcement['status'] == 'active' ? 'selected' : ''; ?>>
                                    แสดง
                                </option>
                                <option value="inactive" <?php echo $announcement['status'] == 'inactive' ? 'selected' : ''; ?>>
                                    ซ่อน
                                </option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="action" value="edit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>บันทึก
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>

</body>
</html> 