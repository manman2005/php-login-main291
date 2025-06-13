<?php
// Set timezone to Thailand
date_default_timezone_set('Asia/Bangkok');
// Set secure session parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();
include_once("includes/db_connection.php");
include_once("includes/function.php");

// ตรวจสอบว่ามีการเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_login'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user_login'];
$objCon = connectDB();

// ตรวจสอบการเลือกตั้งที่กำลังดำเนินการอยู่
$now = date('Y-m-d H:i:s');

// Debug information
error_log("Current time: " . $now);

$sql_active = "SELECT v.*, 
              (SELECT COUNT(*) FROM votes vt WHERE vt.user_id = ? AND vt.vote_id = v.vote_id) as has_voted
              FROM voting v 
              WHERE STR_TO_DATE(CONCAT(v.date, ' ', v.start_time), '%Y-%m-%d %H:%i:%s') <= ?
              AND STR_TO_DATE(CONCAT(v.date, ' ', v.end_time), '%Y-%m-%d %H:%i:%s') >= ?
              AND v.status = 'active'";

$stmt = $objCon->prepare($sql_active);
$stmt->bind_param("iss", $user['id'], $now, $now);
$stmt->execute();
$active_election = $stmt->get_result()->fetch_assoc();

// Debug information
if ($active_election) {
    error_log("Found active election: " . print_r($active_election, true));
    error_log("Start time: " . $active_election['date'] . " " . $active_election['start_time']);
    error_log("End time: " . $active_election['date'] . " " . $active_election['end_time']);
} else {
    error_log("No active election found");
    // Check all elections for debugging
    $debug_sql = "SELECT vote_id, date, start_time, end_time, status FROM voting";
    $debug_result = $objCon->query($debug_sql);
    while ($row = $debug_result->fetch_assoc()) {
        error_log("Election found: " . print_r($row, true));
    }
}

// ถ้าไม่มีการเลือกตั้งที่กำลังดำเนินการ
if (!$active_election) {
    echo "<!DOCTYPE html>
    <html lang='th'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>ระบบเลือกตั้งออนไลน์</title>
        <link href='bootstrap523/css/bootstrap.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        <link href='https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap' rel='stylesheet'>
    </head>
    <body class='bg-light' style='font-family: Kanit, sans-serif;'>
        <div class='container py-5'>
            <div class='text-center'>
                <i class='fas fa-clock fa-5x text-warning mb-3'></i>
                <h2>ไม่มีการเลือกตั้งที่กำลังดำเนินการอยู่</h2>
                <p class='lead'>กรุณากลับมาใหม่ในช่วงเวลาที่มีการเลือกตั้ง</p>
                <a href='index.php' class='btn btn-primary mt-3'>
                    <i class='fas fa-home me-2'></i>กลับหน้าหลัก
                </a>
            </div>
        </div>
        <script src='bootstrap523/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    exit;
}

// ตรวจสอบว่าผู้ใช้โหวตไปแล้วหรือยัง
if ($active_election['has_voted'] > 0) {
    echo "<!DOCTYPE html>
    <html lang='th'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>ระบบเลือกตั้งออนไลน์</title>
        <link href='bootstrap523/css/bootstrap.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        <link href='https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap' rel='stylesheet'>
    </head>
    <body class='bg-light' style='font-family: Kanit, sans-serif;'>
        <div class='container py-5'>
            <div class='text-center'>
                <i class='fas fa-check-circle fa-5x text-success mb-3'></i>
                <h2>คุณได้ลงคะแนนเสียงแล้ว</h2>
                <p class='lead'>ขอบคุณที่ร่วมแสดงความคิดเห็นผ่านการเลือกตั้ง</p>
                <a href='index.php' class='btn btn-primary mt-3'>
                    <i class='fas fa-home me-2'></i>กลับหน้าหลัก
                </a>
            </div>
        </div>
        <script src='bootstrap523/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    exit;
}

// ดึงข้อมูลผู้สมัครสำหรับการเลือกตั้งที่กำลังดำเนินการ
$sql = "SELECT c.*, u.fullname 
        FROM candidates c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.vote_id = ? 
        ORDER BY c.candidate_number";
$stmt = $objCon->prepare($sql);
$stmt->bind_param("i", $active_election['vote_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<!DOCTYPE html>
    <html lang='th'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>ระบบเลือกตั้งออนไลน์</title>
        <link href='bootstrap523/css/bootstrap.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        <link href='https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap' rel='stylesheet'>
    </head>
    <body class='bg-light' style='font-family: Kanit, sans-serif;'>
        <div class='container py-5'>
            <div class='text-center'>
                <i class='fas fa-exclamation-triangle fa-5x text-warning mb-3'></i>
                <h2>ไม่พบข้อมูลผู้สมัคร</h2>
                <p class='lead'>กรุณาติดต่อผู้ดูแลระบบ</p>
                <a href='index.php' class='btn btn-primary mt-3'>
                    <i class='fas fa-home me-2'></i>กลับหน้าหลัก
                </a>
            </div>
        </div>
        <script src='bootstrap523/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบเลือกตั้งออนไลน์</title>
    <!-- Bootstrap 5 -->
    <link href="bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Kanit -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .vote-radio {
            display: none;
        }
        .vote-label {
            display: block;
            padding: 1rem;
            margin: 0.5rem;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .vote-radio:checked + .vote-label {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .candidate-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin: 1rem auto;
            border: 3px solid #fff;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .countdown {
            background: rgba(13, 110, 253, 0.1);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn-vote {
            padding: 1rem 3rem;
            font-size: 1.2rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-vote-yea me-2"></i>ระบบเลือกตั้งออนไลน์
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-1"></i>หน้าแรก
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="vote.php">
                        <i class="fas fa-check-square me-1"></i>ลงคะแนน
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['fullname']); ?>
                </span>
                <a href="logout_action.php" class="btn btn-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container py-5">
    <!-- Election Info -->
    <div class="text-center mb-5">
        <h1 class="display-4 mb-3"><?php echo htmlspecialchars($active_election['vote_name']); ?></h1>
        <p class="lead text-muted">
            <?php
            // แปลงวันที่เป็นภาษาไทย
            $thai_month_arr=array(
                "0"=>"",
                "1"=>"มกราคม",
                "2"=>"กุมภาพันธ์",
                "3"=>"มีนาคม",
                "4"=>"เมษายน",
                "5"=>"พฤษภาคม",
                "6"=>"มิถุนายน", 
                "7"=>"กรกฎาคม",
                "8"=>"สิงหาคม",
                "9"=>"กันยายน",
                "10"=>"ตุลาคม",
                "11"=>"พฤศจิกายน",
                "12"=>"ธันวาคม"
            );

            $thai_date = date('j', strtotime($active_election['date']));
            $thai_month = $thai_month_arr[date('n', strtotime($active_election['date']))];
            $thai_year = date('Y', strtotime($active_election['date'])) + 543;

            // แปลงเวลาให้อยู่ในรูปแบบ 24 ชั่วโมง
            $start_time = date('H:i', strtotime($active_election['start_time']));
            $end_time = date('H:i', strtotime($active_election['end_time']));

            echo "วันที่ {$thai_date} {$thai_month} พ.ศ. {$thai_year}";
            ?>
            <br>
            เวลา <?php echo $start_time . ' น. - ' . $end_time; ?> น.
        </p>
        <div class="countdown" id="countdown">
            <h5 class="mb-2">เวลาที่เหลือในการลงคะแนน</h5>
            <div id="timer" class="h4 mb-0 text-primary">
                <i class="fas fa-clock me-2"></i>
                <span id="hours">00</span> ชั่วโมง 
                <span id="minutes">00</span> นาที 
                <span id="seconds">00</span> วินาที
            </div>
        </div>
    </div>

    <form action="vote_action.php" method="POST" id="voteForm">
        <?php 
        // Validate vote_id
        $vote_id = filter_var($active_election['vote_id'], FILTER_VALIDATE_INT);
        if($vote_id === false) {
            die("Invalid vote ID");
        }
        ?>
        <input type="hidden" name="vote_id" value="<?php echo $vote_id; ?>">
        
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <?php if (!empty($row['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" 
                                     class="candidate-img" 
                                     alt="<?php echo htmlspecialchars($row['fullname']); ?>">
                            <?php else: ?>
                                <img src="vote_img/default.jpg" 
                                     class="candidate-img" 
                                     alt="Default Image">
                            <?php endif; ?>
                            
                            <h5 class="card-title mt-3">
                                <i class="fas fa-user me-2"></i>
                                <?php echo htmlspecialchars($row['fullname']); ?>
                            </h5>
                            
                            <p class="card-text text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                <?php echo htmlspecialchars($row['description']); ?>
                            </p>

                            <input type="radio" 
                                   class="vote-radio" 
                                   name="candidate_id" 
                                   id="candidate<?php echo $row['candidate_id']; ?>" 
                                   value="<?php echo $row['candidate_id']; ?>">
                            <label class="vote-label" for="candidate<?php echo $row['candidate_id']; ?>">
                                <i class="fas fa-check-circle me-2"></i>เลือก
                            </label>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="text-center mt-5">
            <button type="submit" class="btn btn-primary btn-vote">
                <i class="fas fa-paper-plane me-2"></i>บันทึกการลงคะแนน
            </button>
        </div>
    </form>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="bootstrap523/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Countdown Timer
function updateTimer() {
    const now = new Date();
    const end = new Date('<?php echo $active_election['date'] . ' ' . $active_election['end_time']; ?>');
    const diff = end - now;

    if (diff <= 0) {
        Swal.fire({
            title: 'หมดเวลาลงคะแนน',
            text: 'ระบบจะพาท่านไปยังหน้าแสดงผลการเลือกตั้ง',
            icon: 'info',
            confirmButtonText: 'ตกลง'
        }).then(() => {
            window.location.href = 'index.php';
        });
        return;
    }

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    // แสดงผลในรูปแบบภาษาไทย
    document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
    document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
    document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
}

setInterval(updateTimer, 1000);
updateTimer();

// Form Validation
document.getElementById('voteForm').addEventListener('submit', function(e) {
    const selectedCandidate = document.querySelector('input[name="candidate_id"]:checked');
    
    if (!selectedCandidate) {
        e.preventDefault();
        Swal.fire({
            title: 'กรุณาเลือกผู้สมัคร',
            text: 'โปรดเลือกผู้สมัครที่ท่านต้องการก่อนบันทึกการลงคะแนน',
            icon: 'warning',
            confirmButtonText: 'ตกลง'
        });
        return;
    }

    // Confirm vote
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยันการลงคะแนน',
        text: 'การลงคะแนนไม่สามารถเปลี่ยนแปลงได้ในภายหลัง',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});
</script>

</body>
</html>
