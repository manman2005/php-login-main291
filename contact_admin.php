<?php
require_once("includes/config.php"); // ini_set อยู่ในนี้
require_once("includes/db_connection.php"); // ฟังก์ชัน connectDB()
session_start();
// require_once("includes/auth.php"); // ถ้ามีไฟล์นี้ค่อยเปิด

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนใช้งาน';
    header("Location: login.php");
    exit;
}

$objCon = connectDB();

// เมื่อมีการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_login']['id'];
    $created_at = date('Y-m-d H:i:s');

    if ($subject && $message) {
        $sql = "INSERT INTO admin_messages (user_id, subject, message, status, created_at) VALUES (?, ?, ?, 'unread', ?)";
        $stmt = $objCon->prepare($sql);
        $stmt->bind_param("isss", $user_id, $subject, $message, $created_at);
        if ($stmt->execute()) {
            $_SESSION['success'] = "ส่งข้อความถึงแอดมินเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการส่งข้อความ";
        }
        $stmt->close();
        header("Location: contact_admin.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}

// ดึงประวัติการติดต่อของผู้ใช้
$user_id = $_SESSION['user_login']['id'];
$sql = "SELECT am.*, u.fullname as admin_name
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
    <title>ติดต่อแอดมิน</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 -->
    <link href="bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background: #f5f5f5; }
        .card { border-radius: 15px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm p-4 mb-4">
                <h2 class="mb-4"><i class="fas fa-envelope me-2"></i>ติดต่อแอดมิน</h2>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
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

            <div class="card shadow-sm p-4">
                <h4 class="mb-3"><i class="fas fa-history me-2"></i>ประวัติการติดต่อ</h4>
                <?php if ($messages->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>หัวข้อ</th>
                                    <th>ข้อความ</th>
                                    <th>สถานะ</th>
                                    <th>แอดมินตอบกลับ</th>
                                    <th>วันที่</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($msg = $messages->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                                        <td>
                                            <?php
                                                if ($msg['admin_reply']) {
                                                    echo '<span class="badge bg-success">ตอบกลับแล้ว</span>';
                                                } elseif ($msg['status'] == 'read') {
                                                    echo '<span class="badge bg-info">อ่านแล้ว</span>';
                                                } else {
                                                    echo '<span class="badge bg-warning text-dark">รอการตอบกลับ</span>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                if ($msg['admin_reply']) {
                                                    echo nl2br(htmlspecialchars($msg['admin_reply']));
                                                    if ($msg['reply_at']) {
                                                        echo '<br><small class="text-muted">เมื่อ: ' . date('d/m/Y H:i', strtotime($msg['reply_at'])) . '</small>';
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center mb-0">
                        <i class="fas fa-info-circle me-2"></i>ยังไม่มีประวัติการติดต่อ
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="bootstrap523/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
$objCon->close();
?>