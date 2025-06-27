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

    // รับค่าจากฟอร์ม
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ตรวจสอบค่าที่จำเป็น
    if ($username === '' || $fullname === '' || $email === '' || $password === '') {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("Location: register.php");
        exit;
    }

    // ตรวจสอบรูปแบบข้อมูล
    if (!preg_match('/^[A-Za-z0-9_.-]+$/', $username)) {
        $_SESSION['error'] = "ชื่อผู้ใช้ใช้ได้เฉพาะ a-z, 0-9, _, . หรือ - เท่านั้น";
        header("Location: register.php");
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "รูปแบบอีเมลไม่ถูกต้อง";
        header("Location: register.php");
        exit;
    }
    if (strlen($password) < 8 || !preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $_SESSION['error'] = "รหัสผ่านต้องมีอย่างน้อย 8 ตัว และประกอบด้วยตัวอักษรและตัวเลข";
        header("Location: register.php");
        exit;
    }
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "รหัสผ่านไม่ตรงกัน";
        header("Location: register.php");
        exit;
    }

    // ตรวจสอบชื่อผู้ใช้หรืออีเมลซ้ำ (ใช้ prepared statement)
    $check_stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        $_SESSION['error'] = "ชื่อผู้ใช้หรืออีเมลนี้มีผู้ใช้งานแล้ว";
        $check_stmt->close();
        header("Location: register.php");
        exit;
    }
    $check_stmt->close();

    // แฮชรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // เพิ่มผู้ใช้ใหม่ลงในฐานข้อมูล (ใช้ prepared statement)
    $insert_stmt = $db->prepare("INSERT INTO users (fullname, username, email, password, role_id) VALUES (?, ?, ?, ?, 2)");
    $insert_stmt->bind_param("ssss", $fullname, $username, $email, $hashed_password);

    if ($insert_stmt->execute()) {
        $_SESSION['success'] = "ลงทะเบียนสำเร็จ! กรุณาเข้าสู่ระบบ";
        $insert_stmt->close();
        header("Location: register.php");
        exit;
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการลงทะเบียน";
        $insert_stmt->close();
        header("Location: register.php");
        exit;
    }
} else {
    header("Location: register.php");
    exit;
}