<?php
session_start();
require_once("includes/db_connection.php");
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['user_login'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user_login'];
$db = connectDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $candidate_id = intval($_POST['candidate_id']);
    $vote_id = intval($_POST['vote_id']);
    $user_id = intval($user['id']);

    // ดึงข้อมูลเลือกตั้ง
    $voting_stmt = $db->prepare("SELECT * FROM voting WHERE vote_id = ? LIMIT 1");
    $voting_stmt->bind_param("i", $vote_id);
    $voting_stmt->execute();
    $voting_result = $voting_stmt->get_result();
    $voting = $voting_result->fetch_assoc();
    $voting_stmt->close();

    if (!$voting) {
        echo "<script>alert('ไม่พบข้อมูลเลือกตั้ง'); window.location.href='vote.php';</script>";
        exit;
    }

    // ประกอบ datetime
    $start_datetime = $voting['date'] . ' ' . $voting['start_time'];
    $end_datetime = $voting['date'] . ' ' . $voting['end_time'];
    $now = date('Y-m-d H:i:s');

    // ตรวจสอบสิทธิ์โหวต
    if ($now < $start_datetime) {
        echo "<script>alert('ยังไม่ถึงเวลาเลือกตั้ง'); window.location.href='vote.php';</script>";
        exit;
    }
    if ($now > $end_datetime) {
        echo "<script>alert('หมดเวลาเลือกตั้งแล้ว'); window.location.href='vote.php';</script>";
        exit;
    }

    // ตรวจสอบว่าผู้สมัครอยู่ในการเลือกตั้งนี้จริงหรือไม่
    $check_candidate = $db->prepare("SELECT * FROM candidates WHERE candidate_id = ? AND vote_id = ?");
    $check_candidate->bind_param("ii", $candidate_id, $vote_id);
    $check_candidate->execute();
    $candidate_result = $check_candidate->get_result();

    if ($candidate_result->num_rows == 0) {
        echo "<script>alert('ข้อมูลผู้สมัครไม่ถูกต้อง'); window.location.href='vote.php';</script>";
        exit;
    }

    // ตรวจสอบว่าผู้ใช้ได้โหวตไปแล้วหรือยัง
    $check_stmt = $db->prepare("SELECT * FROM votes WHERE user_id = ? AND vote_id = ?");
    $check_stmt->bind_param("ii", $user_id, $vote_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('คุณได้โหวตไปแล้ว!'); window.location.href='vote.php';</script>";
    } else {
        $insert_stmt = $db->prepare("INSERT INTO votes (user_id, candidate_id, vote_id) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iii", $user_id, $candidate_id, $vote_id);

        if ($insert_stmt->execute()) {
            echo "<script>alert('โหวตสำเร็จ'); window.location.href='vote.php';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล'); window.location.href='vote.php';</script>";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
    $check_candidate->close();
}


$db->close();
?>
