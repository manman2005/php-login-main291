<?php
session_start();
include_once("../includes/auth.php");
include_once("../../includes/db_connection.php"); // แก้ path ตรงนี้

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

// ตรวจสอบ CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'การยืนยันตัวตนไม่ถูกต้อง';
    header("Location: elections.php");
    exit;
}

// ตรวจสอบ vote_id
if (!isset($_POST['vote_id'])) {
    $_SESSION['error'] = 'ไม่พบข้อมูลการเลือกตั้ง';
    header("Location: elections.php");
    exit;
}

$objCon = connectDB();
$vote_id = mysqli_real_escape_string($objCon, $_POST['vote_id']);

// ตรวจสอบว่าการเลือกตั้งเริ่มแล้วหรือไม่
$sql = "SELECT * FROM voting WHERE vote_id = '$vote_id'";
$result = mysqli_query($objCon, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'ไม่พบข้อมูลการเลือกตั้ง';
    header("Location: elections.php");
    exit;
}

$election = mysqli_fetch_assoc($result);
$current_datetime = new DateTime();
$election_start = new DateTime($election['date'] . ' ' . $election['start_time']);

if ($current_datetime >= $election_start) {
    $_SESSION['error'] = 'ไม่สามารถลบการเลือกตั้งที่เริ่มแล้วได้';
    header("Location: elections.php");
    exit;
}

// เริ่ม transaction
mysqli_begin_transaction($objCon);

try {
    // ลบข้อมูลที่เกี่ยวข้อง
    $tables = ['voting_settings', 'candidates', 'votes', 'voting'];
    
    foreach ($tables as $table) {
        if ($table == 'votes') {
            $sql = "DELETE FROM votes WHERE vote_id = ?";
        } else {
            $sql = "DELETE FROM $table WHERE vote_id = ?";
        }
        $stmt = mysqli_prepare($objCon, $sql);
        mysqli_stmt_bind_param($stmt, "s", $vote_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("ไม่สามารถลบข้อมูลจากตาราง $table ได้");
        }
    }

    // Commit transaction
    mysqli_commit($objCon);
    $_SESSION['success'] = 'ลบการเลือกตั้งเรียบร้อยแล้ว';

} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($objCon);
    $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

header("Location: elections.php");
exit;
?>