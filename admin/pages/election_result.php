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

// ตรวจสอบ ID ที่ส่งมา
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ไม่พบข้อมูลการเลือกตั้ง';
    header("Location: elections.php");
    exit;
}

$election_id = mysqli_real_escape_string($objCon, $_GET['id']);

// ดึงข้อมูลการเลือกตั้ง
$sql = "SELECT v.*, 
        (SELECT COUNT(*) FROM votes WHERE vote_id = v.vote_id) as total_votes
        FROM voting v 
        WHERE v.vote_id = $election_id";
$result = mysqli_query($objCon, $sql);
$election = mysqli_fetch_assoc($result);

if (!$election) {
    $_SESSION['error'] = 'ไม่พบข้อมูลการเลือกตั้ง';
    header("Location: elections.php");
    exit;
}

// ดึงข้อมูลผู้สมัครและคะแนน
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.candidate_id) as vote_count
        FROM candidates c
        WHERE c.vote_id = $election_id
        ORDER BY vote_count DESC, c.candidate_number ASC";
$candidates = mysqli_query($objCon, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลการเลือกตั้ง - <?php echo htmlspecialchars($election['vote_name']); ?></title>
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
        .card {
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .candidate-card {
            transition: transform 0.2s;
        }
        .candidate-card:hover {
            transform: translateY(-5px);
        }
        .winner {
            position: relative;
        }
        .winner::after {
            content: '🏆';
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 24px;
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
                    <i class="fas fa-chart-bar me-2"></i>
                    ผลการเลือกตั้ง: <?php echo htmlspecialchars($election['vote_name']); ?>
                </h1>
                <a href="elections.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>กลับ
                </a>
            </div>

            <!-- สรุปผลการเลือกตั้ง -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">จำนวนผู้มีสิทธิ์เลือกตั้ง</h5>
                            <h2 class="text-primary">
                                <?php
                                $sql = "SELECT COUNT(*) as total FROM users WHERE role_id = 2";
                                $result = mysqli_query($objCon, $sql);
                                $row = mysqli_fetch_assoc($result);
                                echo number_format($row['total']);
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">จำนวนผู้มาใช้สิทธิ์</h5>
                            <h2 class="text-success">
                                <?php echo number_format($election['total_votes']); ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">เปอร์เซ็นต์ผู้มาใช้สิทธิ์</h5>
                            <h2 class="text-info">
                                <?php
                                $percentage = ($row['total'] > 0) ? ($election['total_votes'] / $row['total']) * 100 : 0;
                                echo number_format($percentage, 2) . '%';
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ผลคะแนนแต่ละผู้สมัคร -->
            <div class="row">
                <?php 
                $highest_votes = 0;
                $first = true;
                while ($candidate = mysqli_fetch_assoc($candidates)): 
                    if ($first) {
                        $highest_votes = $candidate['vote_count'];
                        $first = false;
                    }
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card candidate-card <?php echo ($candidate['vote_count'] == $highest_votes && $highest_votes > 0) ? 'winner' : ''; ?>">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <?php if ($candidate['image_path']): ?>
                                        <img src="../../<?php echo htmlspecialchars($candidate['image_path']); ?>" 
                                             alt="รูปผู้สมัคร" 
                                             class="img-fluid rounded-circle" 
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                             style="width: 150px; height: 150px;">
                                            <i class="fas fa-user fa-4x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <h5 class="card-title text-center">
                                    หมายเลข <?php echo $candidate['candidate_number']; ?>
                                </h5>
                                <h6 class="text-center mb-3">
                                    <?php echo htmlspecialchars($candidate['fullname']); ?>
                                </h6>
                                <div class="text-center">
                                    <h3 class="text-primary mb-2">
                                        <?php echo number_format($candidate['vote_count']); ?> คะแนน
                                    </h3>
                                    <div class="progress">
                                        <?php 
                                        $vote_percentage = ($election['total_votes'] > 0) ? 
                                            ($candidate['vote_count'] / $election['total_votes']) * 100 : 0;
                                        ?>
                                        <div class="progress-bar" 
                                             role="progressbar" 
                                             style="width: <?php echo $vote_percentage; ?>%"
                                             aria-valuenow="<?php echo $vote_percentage; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo number_format($vote_percentage, 2); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>

</body>
</html> 