    <?php
    session_start();
    include_once("includes/db_connection.php");
    include_once("includes/function.php");

    // ตรวจสอบการล็อกอิน
    if (!isset($_SESSION['user_login'])) {
        header("Location: login.php");
        exit;
    }

    $user = $_SESSION['user_login'];
    $objCon = connectDB();

    // ดึงข้อมูลการเลือกตั้ง
    $search = isset($_GET['search']) ? mysqli_real_escape_string($objCon, $_GET['search']) : '';
    $sql = "SELECT v.*, COUNT(DISTINCT vt.id) as vote_count
            FROM voting v 
            LEFT JOIN votes vt ON v.vote_id = vt.candidate_id
            WHERE v.vote_name LIKE '%$search%'
            GROUP BY v.vote_id, v.vote_name, v.date, v.start_time, v.end_time, v.created_at
            ORDER BY v.date DESC, v.start_time DESC";
    $result = mysqli_query($objCon, $sql);

    if (!$result) {
        die("Error fetching voting data: " . mysqli_error($objCon));
    }

    // ดึงสถิติ
    $sql_stats = "SELECT 
        (SELECT COUNT(*) FROM voting WHERE DATE(date) >= CURDATE()) as upcoming_votes,
        (SELECT COUNT(*) FROM voting WHERE DATE(date) = CURDATE()) as today_votes,
        (SELECT COUNT(*) FROM votes) as total_participants,
        (SELECT COUNT(DISTINCT user_id) FROM votes) as unique_voters";
    $stats = mysqli_query($objCon, $sql_stats);

    if (!$stats) {
        $stats_data = [
            'upcoming_votes' => 0,
            'today_votes' => 0,
            'total_participants' => 0,
            'unique_voters' => 0
        ];
    } else {
        $stats_data = mysqli_fetch_assoc($stats);
    }

    // ดึงประกาศล่าสุด - ตรวจสอบว่าตารางมีอยู่หรือไม่
    $table_exists = mysqli_query($objCon, "SHOW TABLES LIKE 'announcements'");
    if (mysqli_num_rows($table_exists) > 0) {
        $sql_announcements = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3";
        $announcements = mysqli_query($objCon, $sql_announcements);
    } else {
        $announcements = false;
    }
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ระบบเลือกตั้งออนไลน์</title>
        <!-- Bootstrap 5 -->
        <link href="bootstrap523/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <!-- Google Fonts - Kanit -->
        <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
        <!-- AOS Animation -->
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Kanit', sans-serif;
                background-color: #f8f9fa;
            }

            .navbar {
                background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
                padding: 1rem 0;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .navbar-brand {
                color: white !important;
                font-weight: 600;
                font-size: 1.5rem;
            }

            .navbar-brand img {
                width: 40px;
                height: 40px;
                object-fit: cover;
                border: 2px solid white;
                margin-right: 10px;
            }

            .nav-link {
                color: rgba(255,255,255,0.9) !important;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .nav-link:hover {
                color: white !important;
                transform: translateY(-2px);
            }

            .user-info {
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 50px;
                background: rgba(255,255,255,0.1);
            }

            .carousel {
                margin-bottom: 3rem;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            .carousel-inner {
                height: 400px;
                border-radius: 15px;
            }

            .carousel-item {
                height: 400px;
            }

            .carousel-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .page-header {
                background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
                color: white;
                padding: 3rem 0;
                margin-bottom: 2rem;
                text-align: center;
            }

            .voting-card {
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                margin-bottom: 2rem;
                transition: all 0.3s ease;
            }

            .voting-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            }

            .voting-header {
                background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
                color: white;
                padding: 1rem;
            }

            .voting-body {
                padding: 1.5rem;
            }

            .btn-vote {
                background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
                border: none;
                padding: 0.5rem 1.5rem;
                font-size: 1rem;
                font-weight: 500;
                border-radius: 50px;
                color: white;
                transition: all 0.3s ease;
            }

            .btn-vote:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
                color: white;
            }

            .status-badge {
                padding: 0.5rem 1rem;
                border-radius: 50px;
                font-size: 0.9rem;
                font-weight: 500;
            }

            .status-active {
                background-color: #28a745;
                color: white;
            }

            .status-upcoming {
                background-color: #ffc107;
                color: #000;
            }

            .status-ended {
                background-color: #dc3545;
                color: white;
            }

            .stats-card {
                background: white;
                border-radius: 15px;
                padding: 1.5rem;
                margin-bottom: 1rem;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
            }

            .stats-card:hover {
                transform: translateY(-5px);
            }

            .stats-icon {
                width: 48px;
                height: 48px;
                background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }

            .announcement-card {
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                border-left: 4px solid #0d6efd;
                padding: 1rem;
                margin-bottom: 1rem;
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .announcement-card:hover {
                transform: translateX(5px);
            }

            .search-box {
                position: relative;
                margin-bottom: 2rem;
            }

            .search-box input {
                padding-left: 3rem;
                border-radius: 50px;
                border: 2px solid #e0e0e0;
                transition: all 0.3s ease;
            }

            .search-box input:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            }

            .search-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: #666;
            }

            .theme-switch {
                position: fixed;
                bottom: 2rem;
                right: 2rem;
                z-index: 1000;
            }

            .theme-btn {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
                color: white;
                border: none;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
            }

            .theme-btn:hover {
                transform: rotate(180deg);
            }
        </style>
    </head>

    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="vote_img/man05.jpg" class="rounded-circle" alt="Logo">
                    ระบบเลือกตั้งออนไลน์
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-home me-1"></i>หน้าแรก
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="vote.php">
                                <i class="fas fa-check-square me-1"></i>ลงคะแนน
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact_admin.php">
                                <i class="fas fa-envelope me-1"></i>ติดต่อแอดมิน
                            </a>
                        </li>
                    </ul>
                    <div class="user-info me-3">
                        <i class="fas fa-user me-2"></i>
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
                    <a href="logout_action.php" class="btn btn-light">
                        <i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ
                    </a>
                </div>
            </div>
        </nav>

        <!-- Carousel -->
        <div class="container mt-4">
            <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="vote_img/man06.png" class="d-block w-100" alt="Slide 1">
                    </div>
                    <div class="carousel-item">
                        <img src="vote_img/man07.png" class="d-block w-100" alt="Slide 2">
                    </div>
                    <div class="carousel-item">
                        <img src="vote_img/man08.png" class="d-block w-100" alt="Slide 3">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="stats-card text-center">
                        <div class="stats-icon mx-auto">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3><?php echo $stats_data['upcoming_votes']; ?></h3>
                        <p class="text-muted">การเลือกตั้งที่กำลังจะมาถึง</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-card text-center">
                        <div class="stats-icon mx-auto">
                            <i class="fas fa-vote-yea"></i>
                        </div>
                        <h3><?php echo $stats_data['today_votes']; ?></h3>
                        <p class="text-muted">การเลือกตั้งวันนี้</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="stats-card text-center">
                        <div class="stats-icon mx-auto">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3><?php echo $stats_data['total_participants']; ?></h3>
                        <p class="text-muted">จำนวนการลงคะแนนทั้งหมด</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="stats-card text-center">
                        <div class="stats-icon mx-auto">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3><?php echo $stats_data['unique_voters']; ?></h3>
                        <p class="text-muted">ผู้มีสิทธิ์ลงคะแนน</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcements -->
        <div class="container mt-4">
            <h2 class="mb-4"><i class="fas fa-bullhorn me-2"></i>ประกาศล่าสุด</h2>
            <?php if ($announcements && mysqli_num_rows($announcements) > 0): ?>
                <?php while ($announcement = mysqli_fetch_assoc($announcements)): ?>
                    <div class="announcement-card" data-aos="fade-left">
                        <h5><?php echo htmlspecialchars($announcement['title']); ?></h5>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($announcement['content']); ?></p>
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i>
                            <?php echo date('d/m/Y H:i', strtotime($announcement['created_at'])); ?>
                        </small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>ไม่มีประกาศใหม่หรือตารางประกาศยังไม่ถูกสร้าง
                </div>
            <?php endif; ?>
        </div>

        <!-- Search Box -->
        <div class="container mt-4">
            <form action="" method="GET">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                        class="form-control form-control-lg" 
                        placeholder="ค้นหาการเลือกตั้ง..." 
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </form>
        </div>

        <!-- Page Header -->
        <div class="page-header mt-4">
            <div class="container">
                <h1><i class="fas fa-vote-yea me-2"></i>การเลือกตั้งที่กำลังดำเนินการ</h1>
                <p class="lead">เลือกรายการที่ต้องการลงคะแนน</p>
            </div>
        </div>

        <!-- Voting List -->
        <div class="container mb-5">
            <div class="row">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="col-md-6" data-aos="fade-up">
                            <div class="voting-card">
                                <div class="voting-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            <?php echo htmlspecialchars($row['date']); ?>
                                        </h5>
                                        <?php
                                        $now = new DateTime();
                                        $start = new DateTime($row['date'] . ' ' . $row['start_time']);
                                        $end = new DateTime($row['date'] . ' ' . $row['end_time']);
                                        
                                        if ($now < $start) {
                                            echo '<span class="status-badge status-upcoming">กำลังจะมาถึง</span>';
                                        } elseif ($now >= $start && $now <= $end) {
                                            echo '<span class="status-badge status-active">กำลังดำเนินการ</span>';
                                        } else {
                                            echo '<span class="status-badge status-ended">สิ้นสุดแล้ว</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="voting-body">
                                    <h4><?php echo htmlspecialchars($row['vote_name']); ?></h4>
                                    <p class="text-muted">
                                        <i class="far fa-clock me-2"></i>
                                        เวลา: <?php echo $row['start_time']; ?> - <?php echo $row['end_time']; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <i class="fas fa-info-circle me-2"></i>
                                            คลิกปุ่มเพื่อเข้าร่วมการเลือกตั้ง
                                        </span>
                                        <a href="vote.php?voting_id=<?php echo $row['vote_id']; ?>" class="btn btn-vote">
                                            <i class="fas fa-vote-yea me-2"></i>เข้าร่วม
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            ไม่มีการเลือกตั้งที่กำลังดำเนินการในขณะนี้
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Theme Switch -->
        <div class="theme-switch">
            <button class="theme-btn" id="themeToggle">
                <i class="fas fa-paint-brush"></i>
            </button>
        </div>

        <!-- Bootstrap Bundle with Popper -->
        <script src="bootstrap523/js/bootstrap.bundle.min.js"></script>
        <!-- AOS Animation -->
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        
        <script>
            // Initialize AOS
            AOS.init({
                duration: 800,
                once: true
            });

            // Auto slide carousel
            var myCarousel = document.querySelector('#mainCarousel');
            var carousel = new bootstrap.Carousel(myCarousel, {
                interval: 3000,
                wrap: true
            });

            // เพิ่ม Script
            document.getElementById('themeToggle').addEventListener('click', function() {
                // สร้างชุดสีสำหรับธีมต่างๆ
                const themes = [
                    {
                        primary: 'linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%)',
                        secondary: '#f8f9fa'
                    },
                    {
                        primary: 'linear-gradient(135deg, #ff6b6b 0%, #ffd93d 100%)',
                        secondary: '#fff5f5'
                    },
                    {
                        primary: 'linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%)',
                        secondary: '#f1f8e9'
                    }
                ];

                // สุ่มเลือกธีม
                const randomTheme = themes[Math.floor(Math.random() * themes.length)];

                // อัพเดทสีของ elements
                document.documentElement.style.setProperty('--theme-primary', randomTheme.primary);
                document.documentElement.style.setProperty('--theme-secondary', randomTheme.secondary);

                // อนิเมชั่นการเปลี่ยนธีม
                document.body.style.transition = 'background-color 0.3s ease';
                document.body.style.backgroundColor = randomTheme.secondary;
            });
        </script>
    </body>

    </html>
