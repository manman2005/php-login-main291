<?php
session_start();
require_once("../includes/auth.php");

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ไม่พบข้อมูลการเลือกตั้ง';
    header("Location: elections.php");
    exit;
}

$id = $_GET['id'];

// เชื่อมต่อฐานข้อมูล
$objCon = connectDB();

// ดึงข้อมูลการเลือกตั้ง
$sql = "SELECT * FROM elections WHERE id = ?";
$stmt = mysqli_prepare($objCon, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$election = mysqli_fetch_assoc($result);

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (!$election) {
    $_SESSION['error'] = 'ไม่พบข้อมูลการเลือกตั้ง';
    header("Location: elections.php");
    exit;
}

// ตรวจสอบว่าการเลือกตั้งเริ่มแล้วหรือยัง
$now = new DateTime();
$start = new DateTime($election['start_date']);
if ($now > $start) {
    $_SESSION['error'] = 'ไม่สามารถแก้ไขการเลือกตั้งที่เริ่มแล้วได้';
    header("Location: elections.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขการเลือกตั้ง - ระบบเลือกตั้งออนไลน์</title>
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
                <h1 class="h2"><i class="fas fa-edit me-2"></i>แก้ไขการเลือกตั้ง</h1>
            </div>

            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php } ?>

            <div class="card">
                <div class="card-body">
                    <form action="../actions/election_action.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $election['id']; ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">ชื่อการเลือกตั้ง</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($election['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">รายละเอียด</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($election['description']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">วันที่เริ่ม</label>
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d\TH:i', strtotime($election['start_date'])); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">วันที่สิ้นสุด</label>
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?php echo date('Y-m-d\TH:i', strtotime($election['end_date'])); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="elections.php" class="btn btn-secondary me-md-2">ยกเลิก</a>
                            <button type="submit" name="action" value="edit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>
<script>
// ตรวจสอบวันที่เริ่มและสิ้นสุด
document.getElementById('start_date').addEventListener('change', function() {
    document.getElementById('end_date').min = this.value;
});

document.getElementById('end_date').addEventListener('change', function() {
    document.getElementById('start_date').max = this.value;
});
</script>

</body>
</html> 