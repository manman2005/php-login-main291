<?php
session_start();
require_once("includes/db_connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = connectDB();

    // ตรวจสอบ CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "CSRF Token ไม่ถูกต้อง";
        header("Location: register.php");
        exit;
    }

    // รับค่าจากฟอร์ม และป้องกัน SQL Injection
    $username = trim(mysqli_real_escape_string($db, $_POST['username']));
    $fullname = trim(mysqli_real_escape_string($db, $_POST['fullname']));
    $email = trim(mysqli_real_escape_string($db, $_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบค่าที่จำเป็น
    if (empty($username) || empty($fullname) || empty($email) || empty($password)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("Location: register.php");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "รหัสผ่านไม่ตรงกัน";
        header("Location: register.php");
        exit;
    }

    // ตรวจสอบชื่อผู้ใช้หรืออีเมลซ้ำ
    $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email' LIMIT 1";
    $result = mysqli_query($db, $check_query);

    if ($result && mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "ชื่อผู้ใช้หรืออีเมลนี้มีผู้ใช้งานแล้ว";
        header("Location: register.php");
        exit;
    }

    // แฮชรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // เพิ่มผู้ใช้ใหม่ลงในฐานข้อมูล
    $insert_sql = "INSERT INTO users (fullname, username, email, password, role_id)
                   VALUES ('$fullname', '$username', '$email', '$hashed_password', 2)";

    if (mysqli_query($db, $insert_sql)) {
        $_SESSION['success'] = "ลงทะเบียนสำเร็จ! กรุณาเข้าสู่ระบบ";
        header("Location: register.php");
        exit;
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการลงทะเบียน";
        header("Location: register.php");
        exit;
    }
} else {
    // ไม่อนุญาตให้เข้าถึงโดยตรง
    header("Location: register.php");
    exit;
}
