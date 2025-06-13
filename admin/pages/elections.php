<?php
session_start();
require_once("../includes/auth.php");

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

// เชื่อมต่อฐานข้อมูล
$objCon = connectDB();

// ตรวจสอบการเชื่อมต่อ
if (!$objCon) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}

// ดึงข้อมูลสถิติ
$stats = [
    'total_elections' => 0,
    'active_elections' => 0,
    'total_votes' => 0
];

// จำนวนการเลือกตั้งทั้งหมด
$sql = "SELECT COUNT(*) as total FROM voting";
$result = mysqli_query($objCon, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_elections'] = $row['total'];
}

// จำนวนการเลือกตั้งที่กำลังดำเนินการ
$sql = "SELECT COUNT(*) as active FROM voting WHERE status = 'active' AND NOW() BETWEEN start_time AND end_time";
$result = mysqli_query($objCon, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['active_elections'] = $row['active'];
}

// จำนวนผู้ลงคะแนนทั้งหมด
$sql = "SELECT COUNT(*) as total FROM votes";
$result = mysqli_query($objCon, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_votes'] = $row['total'];
}

// ดึงข้อมูลการเลือกตั้งทั้งหมด
$sql = "SELECT v.*, 
        (SELECT COUNT(*) FROM candidates c WHERE c.vote_id = v.vote_id) as candidate_count,
        (SELECT COUNT(*) FROM votes vt WHERE vt.vote_id = v.vote_id) as vote_count
        FROM voting v 
        ORDER BY v.created_at DESC";
$elections = mysqli_query($objCon, $sql);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการเลือกตั้ง - ระบบเลือกตั้งออนไลน์</title>
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
        .status-badge {
            padding: 0.5em 1em;
            border-radius: 20px;
            font-size: 0.85em;
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
                <h1 class="h2">
                    <i class="fas fa-vote-yea me-2"></i>จัดการการเลือกตั้ง
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="election_add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>เพิ่มการเลือกตั้ง
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
                                    <h6 class="card-title mb-0">การเลือกตั้งทั้งหมด</h6>
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
                    <div class="card stats-card bg-success bg-gradient text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">กำลังดำเนินการ</h6>
                                    <h2 class="my-2"><?php echo number_format($stats['active_elections']); ?></h2>
                                    <p class="card-text mb-0">รายการ</p>
                                </div>
                                <div class="fs-1 opacity-75">
                                    <i class="fas fa-clock"></i>
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
                                    <h6 class="card-title mb-0">จำนวนผู้ลงคะแนน</h6>
                                    <h2 class="my-2"><?php echo number_format($stats['total_votes']); ?></h2>
                                    <p class="card-text mb-0">คน</p>
                                </div>
                                <div class="fs-1 opacity-75">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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

            <!-- รายการเลือกตั้ง -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ชื่อการเลือกตั้ง</th>
                                    <th>วันที่เริ่ม</th>
                                    <th>วันที่สิ้นสุด</th>
                                    <th>จำนวนผู้สมัคร</th>
                                    <th>จำนวนผู้ลงคะแนน</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($elections)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['vote_name']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['start_time'])); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['end_time'])); ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo number_format($row['candidate_count']); ?> คน
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo number_format($row['vote_count']); ?> คะแนน
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $now = new DateTime();
                                            $start = new DateTime($row['start_time']);
                                            $end = new DateTime($row['end_time']);
                                            
                                            if ($row['status'] == 'active') {
                                                if ($now < $start) {
                                                    echo '<span class="badge bg-warning">รอเริ่มการเลือกตั้ง</span>';
                                                } elseif ($now >= $start && $now <= $end) {
                                                    echo '<span class="badge bg-success">กำลังดำเนินการ</span>';
                                                } else {
                                                    echo '<span class="badge bg-secondary">สิ้นสุดแล้ว</span>';
                                                }
                                            } else {
                                                echo '<span class="badge bg-danger">ยกเลิก</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="election_edit.php?id=<?php echo $row['vote_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="election_result.php?id=<?php echo $row['vote_id']; ?>" 
                                               class="btn btn-sm btn-outline-info me-1">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDelete(<?php echo $row['vote_id']; ?>)">
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
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบการเลือกตั้งนี้?')) {
        window.location.href = `election_delete.php?id=${id}`;
    }
}
</script>

</body>
</html> 