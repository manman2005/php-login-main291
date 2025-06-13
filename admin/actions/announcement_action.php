<?php
session_start();
require_once("../includes/auth.php");

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

$objCon = connectDB();

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$objCon) {
    error_log("Database connection failed: " . mysqli_connect_error());
    $_SESSION['error'] = "ไม่สามารถเชื่อมต่อฐานข้อมูลได้";
    header("Location: ../pages/announcements.php");
    exit;
}

// เพิ่มประกาศใหม่
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $title = mysqli_real_escape_string($objCon, $_POST['title']);
    $content = mysqli_real_escape_string($objCon, $_POST['content']);
    $status = mysqli_real_escape_string($objCon, $_POST['status']);
    $created_by = $_SESSION['user_login']['id'];

    $sql = "INSERT INTO announcements (title, content, status, created_by) 
            VALUES (?, ?, ?, ?)";
    
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssi", $title, $content, $status, $created_by);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "เพิ่มประกาศใหม่สำเร็จ";
        } else {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มประกาศ";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($objCon));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }

    header("Location: ../pages/announcements.php");
    exit;
}

// แก้ไขประกาศ
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = mysqli_real_escape_string($objCon, $_POST['id']);
    $title = mysqli_real_escape_string($objCon, $_POST['title']);
    $content = mysqli_real_escape_string($objCon, $_POST['content']);
    $status = mysqli_real_escape_string($objCon, $_POST['status']);
    $updated_by = $_SESSION['user_login']['id'];

    $sql = "UPDATE announcements SET title = ?, content = ?, status = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssii", $title, $content, $status, $updated_by, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "แก้ไขประกาศสำเร็จ";
        } else {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการแก้ไขประกาศ";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($objCon));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }

    header("Location: ../pages/announcements.php");
    exit;
}

// ลบประกาศ
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($objCon, $_GET['id']);

    $sql = "DELETE FROM announcements WHERE id = ?";
    
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "ลบประกาศสำเร็จ";
        } else {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบประกาศ";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($objCon));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }

    header("Location: ../pages/announcements.php");
    exit;
}

// หากไม่มี action ที่ถูกต้อง ให้ redirect กลับไปหน้าประกาศ
header("Location: ../pages/announcements.php");
exit; 