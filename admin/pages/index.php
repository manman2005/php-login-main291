<?php
require_once(__DIR__ . "/../../includes/config.php");
session_start();
require_once(__DIR__ . "/../includes/auth.php");

// ตรวจสอบว่ามี session และเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

$objCon = connectDB();

// ดึงข้อมูลสถิติ
$sql_stats = "SELECT 
    (SELECT COUNT(*) FROM voting) as total_elections,
    (SELECT COUNT(*) FROM votes) as total_votes,
    (SELECT COUNT(*) FROM users WHERE role_id = 2) as total_users,
    (SELECT COUNT(*) FROM candidates) as total_candidates";
$stats = mysqli_query($objCon, $sql_stats);
$stats_data = mysqli_fetch_assoc($stats);

// ดึงการเลือกตั้งล่าสุด
$sql_recent = "SELECT v.*, 
               (SELECT COUNT(DISTINCT user_id) FROM votes WHERE vote_id = v.vote_id) as unique_voters,
               (SELECT COUNT(*) FROM votes WHERE vote_id = v.vote_id) as vote_count
               FROM voting v 
               ORDER BY v.date DESC, v.start_time DESC 
               LIMIT 5";

$recent_elections = mysqli_query($objCon, $sql_recent);

// เช็คว่ามี error หรือไม่
$has_error = false;
if (!$recent_elections) {
    // บันทึก error log
    error_log("Error in recent elections query: " . mysqli_error($objCon));
    $has_error = true;
}

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$objCon) {
    error_log("Database connection failed: " . mysqli_connect_error());
    $has_error = true;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ระบบเลือกตั้งออนไลน์</title>
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
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            width: 100%;
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
                <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>แผงควบคุม</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="election_add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>สร้างการเลือกตั้ง
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

            <!-- Stats cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <div class="display-4 text-primary mb-2">
                                <?php echo number_format($stats_data['total_elections']); ?>
                            </div>
                            <h5 class="card-title">การเลือกตั้งทั้งหมด</h5>
                            <small>รายการ</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <div class="display-4 text-success mb-2">
                                <?php echo number_format($stats_data['total_votes']); ?>
                            </div>
                            <h5 class="card-title">จำนวนการลงคะแนน</h5>
                            <small>คะแนน</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <div class="display-4 text-info mb-2">
                                <?php echo number_format($stats_data['total_users']); ?>
                            </div>
                            <h5 class="card-title">ผู้ใช้งานทั้งหมด</h5>
                            <small>คน</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <div class="display-4 text-warning mb-2">
                                <?php echo number_format($stats_data['total_candidates']); ?>
                            </div>
                            <h5 class="card-title">ผู้สมัครทั้งหมด</h5>
                            <small>คน</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent elections -->
            <h3 class="mb-4">การเลือกตั้งล่าสุด</h3>
            <?php if ($has_error): ?>
                <div class='alert alert-danger'>
                    <i class="fas fa-exclamation-circle me-2"></i>
                    เกิดข้อผิดพลาดในการดึงข้อมูลการเลือกตั้งล่าสุด กรุณาลองใหม่อีกครั้ง
                </div>
            <?php elseif (mysqli_num_rows($recent_elections) == 0): ?>
                <div class='alert alert-info'>
                    <i class="fas fa-info-circle me-2"></i>
                    ยังไม่มีข้อมูลการเลือกตั้ง
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ชื่อการเลือกตั้ง</th>
                                <th>วันที่</th>
                                <th>เวลา</th>
                                <th>จำนวนผู้ลงคะแนน</th>
                                <th>คะแนนทั้งหมด</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($recent_elections)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['vote_name']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['date'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($row['start_time'])) . ' - ' . date('H:i', strtotime($row['end_time'])); ?></td>
                                    <td><?php echo number_format($row['unique_voters']); ?></td>
                                    <td><?php echo number_format($row['vote_count']); ?></td>
                                    <td>
                                        <?php
                                        $now = new DateTime();
                                        $date = new DateTime($row['date'] . ' ' . $row['end_time']);
                                        if ($now > $date) {
                                            echo '<span class="badge bg-secondary">สิ้นสุดแล้ว</span>';
                                        } else {
                                            echo '<span class="badge bg-success">กำลังดำเนินการ</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="election_view.php?id=<?php echo $row['vote_id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="election_edit.php?id=<?php echo $row['vote_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="election_delete.php?id=<?php echo $row['vote_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('ยืนยันการลบการเลือกตั้งนี้?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>
</body>
</html> 