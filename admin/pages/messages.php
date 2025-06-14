<?php
require_once(__DIR__ . "/../../includes/config.php");
session_start();
require_once(__DIR__ . "/../includes/auth.php");

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

$objCon = connectDB();

// จัดการการตอบกลับ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_id'])) {
    $message_id = (int)$_POST['message_id'];
    $reply = mysqli_real_escape_string($objCon, $_POST['reply']);
    $admin_id = $_SESSION['user_login']['id'];
    $reply_at = date('Y-m-d H:i:s');

    $sql = "UPDATE admin_messages 
            SET admin_reply = ?, 
                admin_id = ?, 
                reply_at = ?, 
                status = 'replied' 
            WHERE id = ?";
    $stmt = $objCon->prepare($sql);
    $stmt->bind_param("sisi", $reply, $admin_id, $reply_at, $message_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'ตอบกลับข้อความเรียบร้อยแล้ว';
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการตอบกลับ';
    }
    $stmt->close();
    header("Location: messages.php");
    exit;
}

// อัพเดทสถานะเป็นอ่านแล้ว เฉพาะข้อความที่ยังไม่ได้ตอบกลับ
$sql = "UPDATE admin_messages SET status = 'read' WHERE status = 'unread' AND admin_reply IS NULL";
mysqli_query($objCon, $sql);

// ดึงข้อความทั้งหมด
$sql = "SELECT am.*, u.fullname as user_name
        FROM admin_messages am
        LEFT JOIN users u ON am.user_id = u.id
        ORDER BY am.created_at DESC";
$messages = mysqli_query($objCon, $sql);

if ($messages === false) {
    die("Query failed: " . mysqli_error($objCon));
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อความ - ระบบเลือกตั้งออนไลน์</title>
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
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include_once(__DIR__ . "/../includes/sidebar.php"); ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-envelope me-2"></i>จัดการข้อความ</h1>
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

            <?php if (mysqli_num_rows($messages) > 0): ?>
                <?php while ($msg = mysqli_fetch_assoc($messages)): ?>
                    <div class="card message-card">
                        <div class="message-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($msg['subject']); ?></h5>
                                <span class="badge <?php echo $msg['admin_reply'] ? 'bg-success' : ($msg['status'] == 'read' ? 'bg-info' : 'bg-warning'); ?>">
                                    <?php
                                        if ($msg['admin_reply']) {
                                            echo "ตอบกลับแล้ว";
                                        } elseif ($msg['status'] == 'read') {
                                            echo "อ่านแล้ว";
                                        } else {
                                            echo "ยังไม่ได้อ่าน";
                                        }
                                    ?>
                                </span>
                            </div>
                            <div class="text-muted mt-2">
                                <small>
                                    จาก: <?php echo htmlspecialchars($msg['user_name']); ?> |
                                    วันที่: <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="message-body">
                            <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            
                            <?php if ($msg['admin_reply']): ?>
                                <div class="alert alert-info mt-3">
                                    <strong>การตอบกลับ:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($msg['admin_reply'])); ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            ตอบกลับเมื่อ: <?php echo date('d/m/Y H:i', strtotime($msg['reply_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form action="" method="POST" class="mt-3">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <div class="mb-3">
                                        <label for="reply<?php echo $msg['id']; ?>" class="form-label">ตอบกลับ</label>
                                        <textarea class="form-control" id="reply<?php echo $msg['id']; ?>" name="reply" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-reply me-2"></i>ส่งการตอบกลับ
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="d-flex justify-content-center align-items-center" style="height:60vh;">
                    <div class="card shadow-sm p-4" style="min-width:350px; border-radius:16px;">
                        <div class="text-center">
                            <i class="fas fa-inbox fa-4x text-secondary mb-3"></i>
                            <h5 class="mb-2 text-muted">ไม่มีข้อความใหม่</h5>
                            <p class="mb-0 text-secondary">เมื่อมีผู้ใช้ส่งข้อความถึงแอดมิน ข้อความจะแสดงที่นี่</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// ปิด connection
mysqli_close($objCon);
?>