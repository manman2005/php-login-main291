<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/../../includes/function.php");
require_once(__DIR__ . "/../../includes/db_connection.php");

// ฟังก์ชันตรวจสอบสิทธิ์ผู้ใช้
function checkAdmin() {
    if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
        header("Location: ../../login.php");
        exit;
    }
}

// ฟังก์ชันตรวจสอบการเชื่อมต่อฐานข้อมูล
function checkConnection($conn) {
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
    }
    return $conn;
}
?> 