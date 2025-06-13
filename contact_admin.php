<?php
session_start();
require_once("includes/config.php");
require_once("includes/auth.php");

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนใช้งาน';
    header("Location: login.php");
    exit;
}

$objCon = connectDB();

// เมื่อมีการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_login']['id'];
    $subject = mysqli_real_escape_string($objCon, $_POST['subject']);
    $message = mysqli_real_escape_string($objCon, $_POST['message']);
    $created_at = date('Y-m-d H:i:s');
    
    // เพิ่มข้อความลงในฐานข้อมูล
    $sql = "INSERT INTO admin_messages (user_id, subject, message, created_at, status) 
            VALUES (?, ?, ?, ?, 'unread')";
    
    $stmt = $objCon->prepare($sql);
    $stmt->bind_param("isss", $user_id, $subject, $message, $created_at);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'ส่งข้อความถึงแอดมินเรียบร้อยแล้ว';
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการส่งข้อความ';
    }
    
    header("Location: contact_admin.php");
    exit;
}

// ดึงประวัติการติดต่อของผู้ใช้
$user_id = $_SESSION['user_login']['id'];
$sql = "SELECT am.*, u.fullname as admin_name, 
        CASE 
            WHEN am.admin_reply IS NOT NULL THEN 'ตอบกลับแล้ว'
            WHEN am.status = 'read' THEN 'อ่านแล้ว'
            ELSE 'รอการตอบกลับ'
        END as status_text
        FROM admin_messages am
        LEFT JOIN users u ON am.admin_id = u.id
        WHERE am.user_id = ?
        ORDER BY am.created_at DESC";

$stmt = $objCon->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อแอดมิน - ระบบเลือกตั้งออนไลน์</title>
    <!-- Bootstrap 5 -->
    <link href="bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f5f5f5;
        }
        .message-card {
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .message-header {
            background-color: #f8f9fa;
            border-radius: 15px 15px 0 0;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .message-body {
            padding: 20px;
        }
        .message-footer {
            background-color: #f8f9fa;
            border-radius: 0 0 15px 15px;
            padding: 15px;
            border-top: 1px solid #dee2e6;
        }
        .admin-reply {
            background-color: #e8f4ff;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-envelope me-2"></i>ติดต่อแอดมิน</h2>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>กลับหน้าหลัก
                </a>
            </div>

            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>
            
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <!-- ฟอร์มส่งข้อความ -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">ส่งข้อความถึงแอดมิน</h5>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="subject" class="form-label">หัวข้อ</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">ข้อความ</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>ส่งข้อความ
                        </button>
                    </form>
                </div>
            </div>

            <!-- ประวัติการติดต่อ -->
            <h4 class="mb-4">ประวัติการติดต่อ</h4>
            <?php if (mysqli_num_rows($messages) > 0): ?>
                <?php while ($msg = $messages->fetch_assoc()): ?>
                    <div class="card message-card">
                        <div class="message-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($msg['subject']); ?></h5>
                                <span class="badge <?php echo $msg['admin_reply'] ? 'bg-success' : ($msg['status'] == 'read' ? 'bg-info' : 'bg-warning'); ?>">
                                    <?php echo $msg['status_text']; ?>
                                </span>
                            </div>
                            <small class="text-muted">
                                วันที่: <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                            </small>
                        </div>
                        <div class="message-body">
                            <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            <?php if ($msg['admin_reply']): ?>
                                <div class="admin-reply">
                                    <div class="d-flex justify-content-between">
                                        <strong>การตอบกลับจากแอดมิน</strong>
                                        <small class="text-muted">
                                            ตอบโดย: <?php echo htmlspecialchars($msg['admin_name']); ?>
                                            (<?php echo date('d/m/Y H:i', strtotime($msg['reply_at'])); ?>)
                                        </small>
                                    </div>
                                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($msg['admin_reply'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>ยังไม่มีประวัติการติดต่อ
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="bootstrap523/js/bootstrap.bundle.min.js"></script>
</body>
</html> 