<?php
include_once("../includes/auth.php");
$objCon = connectDB();

// จัดการการลบผู้สมัคร
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($objCon, $_GET['delete']);
    // ลบข้อมูลที่เกี่ยวข้องก่อน
    mysqli_query($objCon, "DELETE FROM votes WHERE candidate_id = $id");
    mysqli_query($objCon, "DELETE FROM candidates WHERE candidate_id = $id");
    header("Location: candidates.php?success=1");
    exit;
}

// ดึงข้อมูลผู้สมัครทั้งหมด
$sql = "SELECT c.*, v.vote_name, u.fullname,
        (SELECT COUNT(*) FROM votes vt WHERE vt.candidate_id = c.candidate_id) as vote_count
        FROM candidates c
        LEFT JOIN voting v ON c.vote_id = v.vote_id
        LEFT JOIN users u ON c.user_id = u.id
        ORDER BY v.vote_id DESC, c.candidate_number ASC";

$result = mysqli_query($objCon, $sql);

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . mysqli_error($objCon));
}

// ดึงข้อมูลสถิติ
$stats = [
    'total_candidates' => mysqli_num_rows($result),
    'total_elections' => 0,
    'total_votes' => 0
];

// จำนวนการเลือกตั้งที่มีผู้สมัคร
$sql = "SELECT COUNT(DISTINCT vote_id) as total_elections FROM candidates";
$election_result = mysqli_query($objCon, $sql);
if ($election_result) {
    $row = mysqli_fetch_assoc($election_result);
    $stats['total_elections'] = $row['total_elections'];
}

// จำนวนคะแนนโหวตทั้งหมด
$sql = "SELECT COUNT(*) as total_votes FROM votes";
$votes_result = mysqli_query($objCon, $sql);
if ($votes_result) {
    $row = mysqli_fetch_assoc($votes_result);
    $stats['total_votes'] = $row['total_votes'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้สมัคร - ระบบเลือกตั้งออนไลน์</title>
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
        .table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .btn-toolbar .btn {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .btn-toolbar .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .img-thumbnail {
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .img-thumbnail:hover {
            transform: scale(1.1);
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
                <h1 class="h2">
                    <i class="fas fa-users me-2"></i>จัดการผู้สมัคร
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="candidate_add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>เพิ่มผู้สมัคร
                    </a>
                </div>
            </div>

            <!-- สถิติ -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stats-card bg-primary bg-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">จำนวนผู้สมัครทั้งหมด</h6>
                                    <h2 class="my-2"><?php echo number_format($stats['total_candidates']); ?></h2>
                                    <p class="card-text mb-0">คน</p>
                                </div>
                                <div class="fs-1 opacity-75">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card bg-success bg-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">จำนวนการเลือกตั้ง</h6>
                                    <h2 class="my-2"><?php echo number_format($stats['total_elections']); ?></h2>
                                    <p class="card-text mb-0">รายการ</p>
                                </div>
                                <div class="fs-1 opacity-75">
                                    <i class="fas fa-vote-yea"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card bg-info bg-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">คะแนนโหวตทั้งหมด</h6>
                                    <h2 class="my-2"><?php echo number_format($stats['total_votes']); ?></h2>
                                    <p class="card-text mb-0">คะแนน</p>
                                </div>
                                <div class="fs-1 opacity-75">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>ดำเนินการเรียบร้อยแล้ว
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>การเลือกตั้ง</th>
                                    <th>หมายเลข</th>
                                    <th>ชื่อผู้สมัคร</th>
                                    <th>รายละเอียด</th>
                                    <th>รูปภาพ</th>
                                    <th>คะแนนโหวต</th>
                                    <th width="200">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['vote_name']); ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo $row['candidate_number']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td>
                                            <?php if ($row['image_path']): ?>
                                                <img src="../../<?php echo htmlspecialchars($row['image_path']); ?>" 
                                                     alt="รูปผู้สมัคร" class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo number_format($row['vote_count']); ?> คะแนน
                                            </span>
                                        </td>
                                        <td>
                                            <a href="candidate_edit.php?id=<?php echo $row['candidate_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i> แก้ไข
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDelete(<?php echo $row['candidate_id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>

<script>
function confirmDelete(id) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบผู้สมัครนี้?')) {
        window.location.href = `?delete=${id}`;
    }
}
</script>

</body>
</html> 