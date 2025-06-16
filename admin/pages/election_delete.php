<?php
session_start();
include_once("../includes/auth.php");
include_once("../../includes/db_connection.php");

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

// ตรวจสอบว่าการเลือกตั้งมีอยู่จริงหรือไม่
$sql = "SELECT * FROM voting WHERE vote_id = '$vote_id'";
$result = mysqli_query($objCon, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'ไม่พบข้อมูลการเลือกตั้ง';
    header("Location: elections.php");
    exit;
}

// เริ่ม transaction
mysqli_begin_transaction($objCon);

try {
    // ลบข้อมูลที่เกี่ยวข้อง (ลำดับ: votes -> candidates -> voting)
    $tables = ['votes', 'candidates', 'voting'];
    foreach ($tables as $table) {
        $sql = "DELETE FROM $table WHERE vote_id = ?";
        $stmt = mysqli_prepare($objCon, $sql);
        if (!$stmt) {
            throw new Exception("ไม่สามารถเตรียม statement สำหรับตาราง $table ได้: " . mysqli_error($objCon));
        }
        mysqli_stmt_bind_param($stmt, "s", $vote_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("ไม่สามารถลบข้อมูลจากตาราง $table ได้: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
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