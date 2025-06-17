<?php
include_once("../includes/auth.php");
$objCon = connectDB();

// ตรวจสอบ ID ที่ส่งมา
if (!isset($_GET['id'])) {
    header("Location: candidates.php");
    exit;
}

$id = mysqli_real_escape_string($objCon, $_GET['id']);

// ดึงข้อมูลผู้สมัคร
$sql = "SELECT c.*, v.vote_name, v.date, u.username, u.fullname 
        FROM candidates c
        LEFT JOIN voting v ON c.vote_id = v.vote_id
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.candidate_id = '$id'";
$result = mysqli_query($objCon, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: candidates.php");
    exit;
}

$candidate = mysqli_fetch_assoc($result);

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $candidate_number = mysqli_real_escape_string($objCon, $_POST['candidate_number']);
    $description = mysqli_real_escape_string($objCon, $_POST['description']);

    // ตรวจสอบข้อมูล
    $errors = [];
    if (empty($candidate_number)) $errors[] = "กรุณากรอกหมายเลขผู้สมัคร";

    // ตรวจสอบหมายเลขซ้ำ
    $sql = "SELECT * FROM candidates 
            WHERE vote_id = '{$candidate['vote_id']}' 
            AND candidate_number = '$candidate_number' 
            AND candidate_id != '$id'";
    $check = mysqli_query($objCon, $sql);
    if (mysqli_num_rows($check) > 0) {
        $errors[] = "หมายเลขผู้สมัครนี้ถูกใช้แล้วในการเลือกตั้งนี้";
    }

    if (empty($errors)) {
        $image_path = $candidate['image_path'];
        
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
                    // ลบรูปเก่า
                    if (!empty($image_path) && file_exists('../../' . $image_path)) {
                        unlink('../../' . $image_path);
                    }
                    $image_path = 'uploads/candidates/' . $new_filename;
                } else {
                    $errors[] = "เกิดข้อผิดพลาดในการอัพโหลดรูปภาพ";
                }
            } else {
                $errors[] = "กรุณาอัพโหลดไฟล์รูปภาพเท่านั้น (jpg, jpeg, png, gif)";
            }
        }

        if (empty($errors)) {
            $sql = "UPDATE candidates SET 
                    candidate_number = '$candidate_number',
                    description = '$description',
                    image_path = '$image_path'
                    WHERE candidate_id = '$id'";
            
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
    <title>แก้ไขข้อมูลผู้สมัคร - ระบบเลือกตั้งออนไลน์</title>
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
        <?php include_once("../includes/sidebar.php"); ?>
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">แก้ไขข้อมูลผู้สมัคร</h1>
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
                                <label class="form-label">การเลือกตั้ง</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($candidate['vote_name']); ?>" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">ผู้สมัคร</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo htmlspecialchars($candidate['username']); ?><?php echo !empty($candidate['fullname']) ? ' (' . htmlspecialchars($candidate['fullname']) . ')' : ''; ?>" 
                                       readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="candidate_number" class="form-label">หมายเลขผู้สมัคร <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="candidate_number" name="candidate_number" required min="1"
                                       value="<?php echo htmlspecialchars($candidate['candidate_number']); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">รูปภาพ</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this);">
                                <div class="mt-2">
                                    <?php if (!empty($candidate['image_path'])): ?>
                                        <img src="../../<?php echo htmlspecialchars($candidate['image_path']); ?>" 
                                             alt="รูปผู้สมัคร" class="preview-image" id="preview">
                                    <?php else: ?>
                                        <img id="preview" class="preview-image d-none">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">รายละเอียด</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($candidate['description']); ?></textarea>
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
    }
}
</script>

</body>
</html> 