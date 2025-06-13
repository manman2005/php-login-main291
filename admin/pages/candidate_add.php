<?php
include_once("../includes/auth.php");
$objCon = connectDB();

// ดึงข้อมูลการเลือกตั้งทั้งหมด
$sql = "SELECT * FROM voting ORDER BY date DESC, start_time DESC";
$elections = mysqli_query($objCon, $sql);

// ดึงข้อมูลผู้ใช้ที่เป็นผู้สมัครได้
$sql = "SELECT * FROM users WHERE role_id = 2 ORDER BY username";
$users = mysqli_query($objCon, $sql);

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vote_id = mysqli_real_escape_string($objCon, $_POST['vote_id']);
    $user_id = mysqli_real_escape_string($objCon, $_POST['user_id']);
    $candidate_number = mysqli_real_escape_string($objCon, $_POST['candidate_number']);
    $description = mysqli_real_escape_string($objCon, $_POST['description']);

    // ตรวจสอบข้อมูล
    $errors = [];
    if (empty($vote_id)) $errors[] = "กรุณาเลือกการเลือกตั้ง";
    if (empty($user_id)) $errors[] = "กรุณาเลือกผู้สมัคร";
    if (empty($candidate_number)) $errors[] = "กรุณากรอกหมายเลขผู้สมัคร";

    // ตรวจสอบหมายเลขซ้ำ
    $sql = "SELECT * FROM candidates WHERE vote_id = '$vote_id' AND candidate_number = '$candidate_number'";
    $check = mysqli_query($objCon, $sql);
    if (mysqli_num_rows($check) > 0) {
        $errors[] = "หมายเลขผู้สมัครนี้ถูกใช้แล้วในการเลือกตั้งนี้";
    }

    // ตรวจสอบผู้สมัครซ้ำ
    $sql = "SELECT * FROM candidates WHERE vote_id = '$vote_id' AND user_id = '$user_id'";
    $check = mysqli_query($objCon, $sql);
    if (mysqli_num_rows($check) > 0) {
        $errors[] = "ผู้สมัครนี้ได้ลงสมัครในการเลือกตั้งนี้แล้ว";
    }

    if (empty($errors)) {
        $image_path = '';
        
        // จัดการอัพโหลดรูปภาพ
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $temp_name = $_FILES['image']['tmp_name'];
                $new_filename = uniqid() . '.' . $filetype;
                $upload_path = '../../uploads/candidates/';
                
                // สร้างโฟลเดอร์ถ้ายังไม่มี
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                if (move_uploaded_file($temp_name, $upload_path . $new_filename)) {
                    $image_path = 'uploads/candidates/' . $new_filename;
                } else {
                    $errors[] = "เกิดข้อผิดพลาดในการอัพโหลดรูปภาพ";
                }
            } else {
                $errors[] = "กรุณาอัพโหลดไฟล์รูปภาพเท่านั้น (jpg, jpeg, png, gif)";
            }
        }

        if (empty($errors)) {
            $sql = "INSERT INTO candidates (vote_id, user_id, candidate_number, description, image_path) 
                    VALUES ('$vote_id', '$user_id', '$candidate_number', '$description', '$image_path')";
            
            if (mysqli_query($objCon, $sql)) {
                header("Location: candidates.php?success=1");
                exit;
            } else {
                $errors[] = "เกิดข้อผิดพลาด: " . mysqli_error($objCon);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มผู้สมัคร - ระบบเลือกตั้งออนไลน์</title>
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
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <h5>Admin Panel</h5>
                    <small class="text-muted">ระบบจัดการเลือกตั้ง</small>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-home me-2"></i>แดชบอร์ด
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="elections.php">
                            <i class="fas fa-vote-yea me-2"></i>จัดการการเลือกตั้ง
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="candidates.php">
                            <i class="fas fa-users me-2"></i>จัดการผู้สมัคร
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-user-cog me-2"></i>จัดการผู้ใช้
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>รายงาน
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog me-2"></i>ตั้งค่า
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link text-danger" href="../../logout_action.php">
                            <i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">เพิ่มผู้สมัคร</h1>
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
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vote_id" class="form-label">การเลือกตั้ง <span class="text-danger">*</span></label>
                                <select class="form-select" id="vote_id" name="vote_id" required>
                                    <option value="">เลือกการเลือกตั้ง</option>
                                    <?php while ($row = mysqli_fetch_assoc($elections)): ?>
                                        <option value="<?php echo $row['vote_id']; ?>"
                                                <?php echo (isset($_POST['vote_id']) && $_POST['vote_id'] == $row['vote_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['vote_name']); ?>
                                            (<?php echo date('d/m/Y', strtotime($row['date'])); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">ผู้สมัคร <span class="text-danger">*</span></label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">เลือกผู้สมัคร</option>
                                    <?php while ($row = mysqli_fetch_assoc($users)): ?>
                                        <option value="<?php echo $row['id']; ?>"
                                                <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $row['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['username']); ?>
                                            <?php echo !empty($row['fullname']) ? '(' . htmlspecialchars($row['fullname']) . ')' : ''; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="candidate_number" class="form-label">หมายเลขผู้สมัคร <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="candidate_number" name="candidate_number" required min="1"
                                       value="<?php echo isset($_POST['candidate_number']) ? htmlspecialchars($_POST['candidate_number']) : ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">รูปภาพ</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this);">
                                <div class="mt-2">
                                    <img id="preview" class="preview-image d-none">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">รายละเอียด</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="text-end">
                            <a href="candidates.php" class="btn btn-secondary">ยกเลิก</a>
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

<script>
function previewImage(input) {
    var preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        preview.classList.add('d-none');
    }
}
</script>

</body>
</html> 