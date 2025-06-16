<?php
// Set timezone to Thailand
date_default_timezone_set('Asia/Bangkok');
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

$now = date('Y-m-d H:i:s');
$user_id = $user['id'];

// ดึงข้อมูลเลือกตั้งล่าสุดที่ status = 'active'
$sql_election = "SELECT * FROM voting WHERE status = 'active' ORDER BY date DESC, start_time DESC LIMIT 1";
$election_result = $objCon->query($sql_election);
$active_election = $election_result->fetch_assoc();

$can_vote = true;
$vote_message = "";

if ($active_election) {
    $start_datetime = $active_election['date'] . ' ' . $active_election['start_time'];
    $end_datetime = $active_election['date'] . ' ' . $active_election['end_time'];

    if ($now < $start_datetime) {
        $can_vote = false;
        $vote_message = "ยังไม่ถึงเวลาเลือกตั้ง";
    } elseif ($now > $end_datetime) {
        $can_vote = false;
        $vote_message = "หมดเวลาเลือกตั้งแล้ว";
    }

    // ตรวจสอบว่าโหวตไปแล้วหรือยัง
    $check_stmt = $objCon->prepare("SELECT * FROM votes WHERE user_id = ? AND vote_id = ?");
    $check_stmt->bind_param("ii", $user_id, $active_election['vote_id']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    if ($result->num_rows > 0) {
        $can_vote = false;
        $vote_message = "คุณได้โหวตไปแล้ว";
    }
    $check_stmt->close();

    // ดึงข้อมูลผู้สมัคร + join users เพื่อ fullname และ email
    $sql = "SELECT c.*, u.fullname, u.email 
            FROM candidates c 
            LEFT JOIN users u ON c.user_id = u.id 
            WHERE c.vote_id = ? 
            ORDER BY c.candidate_number";
    $stmt = $objCon->prepare($sql);
    $stmt->bind_param("i", $active_election['vote_id']);
    $stmt->execute();
    $candidates = $stmt->get_result();
} else {
    $can_vote = false;
    $vote_message = "ยังไม่มีการเลือกตั้งที่เปิดอยู่";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบเลือกตั้งออนไลน์</title>
    <link href="bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%); box-shadow: 0 2px 4px rgba(0,0,0,0.1);}
        .card { border: none; border-radius: 15px; transition: transform 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.1);}
        .card:hover { transform: translateY(-5px);}
        .vote-radio { display: none;}
        .vote-label { display: block; padding: 1rem; margin: 0.5rem; border: 2px solid #dee2e6; border-radius: 10px; cursor: pointer; transition: all 0.3s;}
        .vote-radio:checked + .vote-label { border-color: #0d6efd; background-color: #e7f1ff;}
        .candidate-img { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; margin: 1rem auto; border: 3px solid #fff; box-shadow: 0 3px 10px rgba(0,0,0,0.1);}
        .countdown { background: rgba(13, 110, 253, 0.1); padding: 1rem; border-radius: 10px; text-align: center; margin-bottom: 2rem;}
        .btn-vote { padding: 1rem 3rem; font-size: 1.2rem; border-radius: 50px; box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);}
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
            <div class="d-flex align-items-center text-white">
                 <i class="fas fa-user me-2 text-white"></i>
                        <?php 
                            if (!empty($user['fullname'])) {
                                echo htmlspecialchars($user['fullname']);
                            } else if (!empty($user['username'])) {
                                echo htmlspecialchars($user['username']);
                            } else {
                                echo htmlspecialchars($user['email']);
                            }
                        ?>
                    </div>
                <a href="logout_action.php" class="btn btn-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container py-5">
    <?php if ($active_election): ?>
        <div class="text-center mb-5">
            <h1 class="display-4 mb-3"><?php echo htmlspecialchars($active_election['vote_name']); ?></h1>
            <p class="lead text-muted">
                <?php
                $thai_month_arr = [
                    "0"=>"", "1"=>"มกราคม", "2"=>"กุมภาพันธ์", "3"=>"มีนาคม", "4"=>"เมษายน",
                    "5"=>"พฤษภาคม", "6"=>"มิถุนายน", "7"=>"กรกฎาคม", "8"=>"สิงหาคม",
                    "9"=>"กันยายน", "10"=>"ตุลาคม", "11"=>"พฤศจิกายน", "12"=>"ธันวาคม"
                ];
                $thai_date = date('j', strtotime($active_election['date']));
                $thai_month = $thai_month_arr[date('n', strtotime($active_election['date']))];
                $thai_year = date('Y', strtotime($active_election['date'])) + 543;
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

        <?php if (!$can_vote): ?>
            <div class="alert alert-warning text-center mb-4"><?php echo $vote_message; ?></div>
        <?php endif; ?>

        <form action="vote_action.php" method="POST" id="voteForm">
            <input type="hidden" name="vote_id" value="<?php echo $active_election['vote_id']; ?>">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php while ($row = $candidates->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <?php if (!empty($row['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="candidate-img" alt="<?php
                                        if (!empty($row['fullname'])) {
                                            echo htmlspecialchars($row['fullname']);
                                        } elseif (!empty($row['email'])) {
                                            echo htmlspecialchars($row['email']);
                                        } else {
                                            echo '-';
                                        }
                                    ?>">
                                <?php else: ?>
                                    <img src="vote_img/default.jpg" class="candidate-img" alt="Default Image">
                                <?php endif; ?>
                                <h5 class="card-title mt-3">
                                    <i class="fas fa-user me-2"></i>
                                    <?php
                                    if (!empty($row['fullname'])) {
                                        echo htmlspecialchars($row['fullname']);
                                    } elseif (!empty($row['email'])) {
                                        echo htmlspecialchars($row['email']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </h5>
                                <p class="card-text text-muted"><i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($row['description']); ?></p>
                                <input type="radio" class="vote-radio" name="candidate_id" id="candidate<?php echo $row['candidate_id']; ?>" value="<?php echo $row['candidate_id']; ?>" <?php if (!$can_vote) echo 'disabled'; ?>>
                                <label class="vote-label" for="candidate<?php echo $row['candidate_id']; ?>"><i class="fas fa-check-circle me-2"></i>เลือก</label>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-primary btn-vote" <?php if (!$can_vote) echo 'disabled'; ?>>
                    <i class="fas fa-paper-plane me-2"></i>บันทึกการลงคะแนน
                </button>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info text-center">
            ยังไม่มีการเลือกตั้งที่เปิดอยู่ในขณะนี้
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="bootstrap523/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Countdown Timer
function updateTimer() {
    <?php if ($active_election): ?>
    const now = new Date();
    const end = new Date('<?php echo $active_election['date'] . ' ' . $active_election['end_time']; ?>');
    const diff = end - now;
    if (diff <= 0) {
        document.getElementById('timer').textContent = 'หมดเวลาลงคะแนน';
        return;
    }
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
    document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
    document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
    <?php endif; ?>
}
setInterval(updateTimer, 1000);
updateTimer();

// Form Validation
document.getElementById('voteForm')?.addEventListener('submit', function(e) {
    <?php if ($can_vote): ?>
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
    <?php else: ?>
    e.preventDefault();
    <?php endif; ?>
});
</script>

</body>
</html>
