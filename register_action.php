<?php
session_start();
require_once("includes/function.php");

// ตรวจสอบว่าส่งข้อมูลผ่าน POST มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'กรุณาส่งข้อมูลผ่านแบบฟอร์มเท่านั้น';
    header("Location: register.php");
    exit;
}

// ตรวจสอบ CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'การยืนยันตัวตนไม่ถูกต้อง';
    header("Location: register.php");
    exit;
}

// ตรวจสอบข้อมูลที่จำเป็น
$required_fields = ['firstname', 'lastname', 'email', 'student_id', 'password', 'confirm_password', 'faculty', 'department'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $_SESSION['error'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        header("Location: register.php");
        exit;
    }
}

// รับค่าจากฟอร์ม
$firstname = trim($_POST['firstname']);
$lastname = trim($_POST['lastname']);
$email = trim($_POST['email']);
$student_id = trim($_POST['student_id']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$faculty = trim($_POST['faculty']);
$department = trim($_POST['department']);

// ตรวจสอบความถูกต้องของข้อมูล
if (!preg_match('/^[ก-์A-Za-z\s]+$/', $firstname) || !preg_match('/^[ก-์A-Za-z\s]+$/', $lastname)) {
    $_SESSION['error'] = 'ชื่อและนามสกุลต้องเป็นภาษาไทยหรืออังกฤษเท่านั้น';
    header("Location: register.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'รูปแบบอีเมลไม่ถูกต้อง';
    header("Location: register.php");
    exit;
}

if (!preg_match('/^\d{11}$/', $student_id)) {
    $_SESSION['error'] = 'รหัสนักศึกษาต้องเป็นตัวเลข 11 หลัก';
    header("Location: register.php");
    exit;
}

if (strlen($password) < 8 || !preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
    $_SESSION['error'] = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัว และประกอบด้วยตัวอักษรและตัวเลข';
    header("Location: register.php");
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'รหัสผ่านไม่ตรงกัน';
    header("Location: register.php");
    exit;
}

try {
    // เชื่อมต่อฐานข้อมูล
    $objCon = connectDB();
    
    if (!$objCon) {
        throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
    }

    // ตรวจสอบว่าอีเมลหรือรหัสนักศึกษาซ้ำหรือไม่
    $check_sql = "SELECT id FROM users WHERE email = ? OR student_id = ?";
    $check_stmt = mysqli_prepare($objCon, $check_sql);
    
    if ($check_stmt === false) {
        throw new Exception("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . mysqli_error($objCon));
    }
    
    mysqli_stmt_bind_param($check_stmt, "ss", $email, $student_id);
    
    if (!mysqli_stmt_execute($check_stmt)) {
        throw new Exception("เกิดข้อผิดพลาดในการตรวจสอบข้อมูล: " . mysqli_stmt_error($check_stmt));
    }
    
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        throw new Exception("อีเมลหรือรหัสนักศึกษานี้มีในระบบแล้ว");
    }

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // เพิ่มข้อมูลลงในฐานข้อมูล
    $insert_sql = "INSERT INTO users (firstname, lastname, email, student_id, password, faculty, department, role_id) VALUES (?, ?, ?, ?, ?, ?, ?, 2)";
    $insert_stmt = mysqli_prepare($objCon, $insert_sql);
    
    if ($insert_stmt === false) {
        throw new Exception("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . mysqli_error($objCon));
    }
    
    mysqli_stmt_bind_param($insert_stmt, "sssssss", $firstname, $lastname, $email, $student_id, $hashed_password, $faculty, $department);
    
    if (!mysqli_stmt_execute($insert_stmt)) {
        throw new Exception("เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_stmt_error($insert_stmt));
    }

    $_SESSION['success'] = 'ลงทะเบียนสำเร็จ กรุณาเข้าสู่ระบบ';
    header("Location: login.php");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: register.php");
    exit;
} finally {
    // ปิดการเชื่อมต่อ statement และ database
    if (isset($check_stmt)) mysqli_stmt_close($check_stmt);
    if (isset($insert_stmt)) mysqli_stmt_close($insert_stmt);
    if (isset($objCon)) mysqli_close($objCon);
}
?>