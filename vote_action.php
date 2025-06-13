<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "php_login";

$db = new mysqli($servername, $username, $password, $dbname);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if (!isset($_SESSION['user_login'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user_login'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $candidate_id = intval($_POST['candidate_id']);
    $user_id = intval($user['id']); // ใช้ id จาก array user

    // ดึง vote_id ที่กำลังใช้งานอยู่
    $vote_query = "SELECT vote_id FROM voting WHERE status = 'active' LIMIT 1";
    $vote_result = $db->query($vote_query);
    
    if ($vote_result && $vote_result->num_rows > 0) {
        $vote_row = $vote_result->fetch_assoc();
        $vote_id = $vote_row['vote_id'];

        // ตรวจสอบว่าผู้ใช้ได้โหวตไปแล้วหรือยัง
        $check_stmt = $db->prepare("SELECT * FROM votes WHERE user_id = ? AND vote_id = ?");
        $check_stmt->bind_param("ii", $user_id, $vote_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // ถ้าผู้ใช้โหวตแล้วให้แสดงข้อความว่าโหวตแล้ว
            echo "<script>alert('คุณได้โหวตไปแล้ว!'); window.location.href='index.php';</script>";
        } else {
            // ถ้าผู้ใช้ยังไม่ได้โหวตให้บันทึกการโหวตลงในฐานข้อมูล
            $insert_stmt = $db->prepare("INSERT INTO votes (user_id, candidate_id, vote_id) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iii", $user_id, $candidate_id, $vote_id);
            
            if ($insert_stmt->execute()) {
                // แสดงข้อความว่าโหวตสำเร็จ
                echo "<script>alert('โหวตสำเร็จ'); window.location.href='index.php';</script>";
            } else {
                echo "Error: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        echo "<script>alert('ไม่พบการเลือกตั้งที่กำลังดำเนินการอยู่'); window.location.href='index.php';</script>";
    }
}

$db->close();
?>
