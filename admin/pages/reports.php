<?php
session_start();
require_once("../includes/auth.php");
// function.php is already included in auth.php

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

$objCon = connectDB();

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$objCon) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
}

// ตัวแปรสำหรับเก็บข้อมูล
$stats_data = [
    'total_elections' => 0,
    'total_votes' => 0,
    'total_users' => 0,
    'total_candidates' => 0
];

// ดึงข้อมูลสถิติรวม
$sql_stats = "SELECT 
    (SELECT COUNT(*) FROM voting) as total_elections,
    (SELECT COUNT(*) FROM votes) as total_votes,
    (SELECT COUNT(*) FROM users WHERE role_id = 2) as total_users,
    (SELECT COUNT(*) FROM candidates) as total_candidates";
$stats = mysqli_query($objCon, $sql_stats);

if (!$stats) {
    error_log("Error in stats query: " . mysqli_error($objCon));
    die("เกิดข้อผิดพลาดในการดึงข้อมูลสถิติ");
}

$stats_data = mysqli_fetch_assoc($stats);

// Debug information
error_log("Stats data: " . print_r($stats_data, true));

// ดึงข้อมูลการเลือกตั้งทั้งหมด
$sql = "SELECT v.*, 
        (SELECT COUNT(*) FROM votes WHERE vote_id = v.vote_id) as total_votes,
        (SELECT COUNT(DISTINCT user_id) FROM votes WHERE vote_id = v.vote_id) as unique_voters
        FROM voting v 
        ORDER BY v.date DESC, v.start_time DESC";

$result = mysqli_query($objCon, $sql);

if (!$result) {
    error_log("Error in elections query: " . mysqli_error($objCon));
    die("เกิดข้อผิดพลาดในการดึงข้อมูลการเลือกตั้ง");
}

// Debug information
error_log("Number of elections found: " . mysqli_num_rows($result));
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานผลการเลือกตั้ง - ระบบเลือกตั้งออนไลน์</title>
    <!-- Bootstrap 5 -->
    <link href="../../bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <?php include_once("../includes/sidebar.php"); ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-chart-bar me-2"></i>รายงานผลการเลือกตั้ง</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-2"></i>ส่งออก PDF
                    </button>
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

            <!-- Overall Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">การเลือกตั้งทั้งหมด</h5>
                            <h2><?php echo number_format($stats_data['total_elections']); ?></h2>
                            <small>รายการ</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-success text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">จำนวนการลงคะแนน</h5>
                            <h2><?php echo number_format($stats_data['total_votes']); ?></h2>
                            <small>คะแนน</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-info text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">ผู้ใช้งานทั้งหมด</h5>
                            <h2><?php echo number_format($stats_data['total_users']); ?></h2>
                            <small>คน</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h5 class="card-title">ผู้สมัครทั้งหมด</h5>
                            <h2><?php echo number_format($stats_data['total_candidates']); ?></h2>
                            <small>คน</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Elections List -->
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($election = mysqli_fetch_assoc($result)): ?>
                    <div class="card stats-card mb-4">
                        <div class="card-header bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <?php echo htmlspecialchars($election['vote_name']); ?>
                                    <span class="badge <?php 
                                        if ($election['status'] == 'active') echo 'bg-success';
                                        elseif ($election['status'] == 'upcoming') echo 'bg-warning';
                                        else echo 'bg-secondary';
                                    ?>">
                                        <?php 
                                        switch($election['status']) {
                                            case 'active':
                                                echo 'กำลังดำเนินการ';
                                                break;
                                            case 'upcoming':
                                                echo 'กำลังจะมาถึง';
                                                break;
                                            case 'ended':
                                                echo 'สิ้นสุดแล้ว';
                                                break;
                                            default:
                                                echo $election['status'];
                                        }
                                        ?>
                                    </span>
                                </h5>
                                <div>
                                    <span class="text-muted">draft</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p>
                                        <i class="far fa-calendar me-2"></i>วันที่: <?php echo thai_date($election['date']); ?><br>
                                        <i class="far fa-clock me-2"></i>เวลา: <?php echo thai_time($election['start_time']) . ' - ' . thai_time($election['end_time']); ?><br>
                                        <i class="fas fa-users me-2"></i>ผู้ลงคะแนน: <?php echo number_format($election['unique_voters']); ?> คน<br>
                                        <i class="fas fa-vote-yea me-2"></i>จำนวนโหวต: <?php echo number_format($election['total_votes']); ?> ครั้ง
                                    </p>
                                </div>
                                <div class="col-md-8">
                                    <div class="chart-container">
                                        <canvas id="chart_<?php echo $election['vote_id']; ?>"></canvas>
                                    </div>
                                    <?php
                                    // ดึงข้อมูลผู้สมัครและคะแนน
                                    $sql_candidates = "SELECT c.candidates_name, 
                                                    COUNT(v.id) as vote_count 
                                                    FROM candidates c 
                                                    LEFT JOIN votes v ON c.candidates_id = v.candidate_id 
                                                    WHERE c.vote_id = ? 
                                                    GROUP BY c.candidates_id";
                                    
                                    $labels = [];
                                    $data = [];
                                    
                                    if ($stmt = mysqli_prepare($objCon, $sql_candidates)) {
                                        mysqli_stmt_bind_param($stmt, "i", $election['vote_id']);
                                        if (mysqli_stmt_execute($stmt)) {
                                            $candidates_result = mysqli_stmt_get_result($stmt);
                                            while ($candidate = mysqli_fetch_assoc($candidates_result)) {
                                                $labels[] = $candidate['candidates_name'];
                                                $data[] = $candidate['vote_count'];
                                            }
                                        } else {
                                            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
                                        }
                                        mysqli_stmt_close($stmt);
                                    } else {
                                        error_log("Error preparing statement: " . mysqli_error($objCon));
                                    }
                                    ?>
                                    <script>
                                    new Chart(document.getElementById('chart_<?php echo $election['vote_id']; ?>'), {
                                        type: 'bar',
                                        data: {
                                            labels: <?php echo json_encode($labels); ?>,
                                            datasets: [{
                                                label: 'จำนวนคะแนน',
                                                data: <?php echo json_encode($data); ?>,
                                                backgroundColor: [
                                                    'rgba(54, 162, 235, 0.8)',
                                                    'rgba(255, 99, 132, 0.8)',
                                                    'rgba(75, 192, 192, 0.8)',
                                                    'rgba(255, 206, 86, 0.8)',
                                                    'rgba(153, 102, 255, 0.8)'
                                                ],
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            scales: {
                                                y: {
                                                    beginAtZero: true,
                                                    ticks: {
                                                        stepSize: 1
                                                    }
                                                }
                                            }
                                        }
                                    });
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    ยังไม่มีข้อมูลการเลือกตั้ง
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>
<!-- html2pdf -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportToPDF() {
    const element = document.querySelector('main');
    const opt = {
        margin: 1,
        filename: 'election_report.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save();
}
</script>

</body>
</html> 