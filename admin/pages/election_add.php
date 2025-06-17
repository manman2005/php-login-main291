<?php
include_once("../includes/auth.php");
$objCon = connectDB();

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vote_name = mysqli_real_escape_string($objCon, $_POST['vote_name']);
    $date = mysqli_real_escape_string($objCon, $_POST['date']);
    $start_time = mysqli_real_escape_string($objCon, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($objCon, $_POST['end_time']);
    $description = mysqli_real_escape_string($objCon, $_POST['description']);

    // ตรวจสอบข้อมูล
    $errors = [];
    if (empty($vote_name)) $errors[] = "กรุณากรอกชื่อการเลือกตั้ง";
    if (empty($date)) $errors[] = "กรุณาเลือกวันที่";
    if (empty($start_time)) $errors[] = "กรุณาเลือกเวลาเริ่มต้น";
    if (empty($end_time)) $errors[] = "กรุณาเลือกเวลาสิ้นสุด";
    if ($start_time >= $end_time) $errors[] = "เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้น";

    if (empty($errors)) {
        $created_by = $_SESSION['user_login']['id']; // เพิ่มการดึง ID ของผู้ใช้ที่กำลัง login
        $sql = "INSERT INTO voting (vote_name, date, start_time, end_time, description, created_by) 
                VALUES ('$vote_name', '$date', '$start_time', '$end_time', '$description', '$created_by')";
        
        if (mysqli_query($objCon, $sql)) {
            $vote_id = mysqli_insert_id($objCon);
            
            // สร้างการตั้งค่าเริ่มต้น
            $sql = "INSERT INTO voting_settings (vote_id, max_votes) VALUES ($vote_id, 1)";
            mysqli_query($objCon, $sql);
            
            header("Location: elections.php?success=1");
            exit;
        } else {
            $errors[] = "เกิดข้อผิดพลาด: " . mysqli_error($objCon);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มการเลือกตั้ง - ระบบเลือกตั้งออนไลน์</title>
    <!-- Bootstrap 5 -->
    <link href="../../bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
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

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">เพิ่มการเลือกตั้ง</h1>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>พบข้อผิดพลาด:</strong>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="vote_name" class="form-label">ชื่อการเลือกตั้ง <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vote_name" name="vote_name" required
                                   value="<?php echo isset($_POST['vote_name']) ? htmlspecialchars($_POST['vote_name']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">รายละเอียด</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="date" class="form-label">วันที่ <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date" name="date" required
                                       value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="start_time" class="form-label">เวลาเริ่มต้น <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required
                                       value="<?php echo isset($_POST['start_time']) ? htmlspecialchars($_POST['start_time']) : ''; ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="end_time" class="form-label">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required
                                       value="<?php echo isset($_POST['end_time']) ? htmlspecialchars($_POST['end_time']) : ''; ?>">
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="elections.php" class="btn btn-secondary">ยกเลิก</a>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
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