<?php
session_start();
require_once("../includes/auth.php");
include_once("../../includes/db_connection.php");

if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: elections.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote_id'], $_POST['set_status'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'CSRF Token ไม่ถูกต้อง';
        header("Location: elections.php");
        exit;
    }

    $vote_id = intval($_POST['vote_id']);
    $set_status = $_POST['set_status'];
    $allowed = ['draft', 'active', 'completed', 'cancelled'];

    if (!in_array($set_status, $allowed)) {
        $_SESSION['error'] = 'สถานะไม่ถูกต้อง';
        header("Location: elections.php");
        exit;
    }

    $objCon = connectDB();
    $stmt = $objCon->prepare("UPDATE voting SET status = ? WHERE vote_id = ?");
    $stmt->bind_param("si", $set_status, $vote_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'เปลี่ยนสถานะการเลือกตั้งเรียบร้อยแล้ว';
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการเปลี่ยนสถานะ';
    }
    $stmt->close();
    mysqli_close($objCon);
    header("Location: elections.php");
    exit;
} else {
    $_SESSION['error'] = 'ข้อมูลไม่ครบถ้วน';
    header("Location: elections.php");
    exit;
}
?>