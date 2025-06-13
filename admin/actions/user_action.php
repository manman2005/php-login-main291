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
    header("Location: ../pages/users.php");
    exit;
}

// เพิ่มผู้ใช้ใหม่
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $username = mysqli_real_escape_string($objCon, $_POST['username']);
    $email = mysqli_real_escape_string($objCon, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = mysqli_real_escape_string($objCon, $_POST['role_id']);
    $status = mysqli_real_escape_string($objCon, $_POST['status']);

    // ตรวจสอบว่ามีชื่อผู้ใช้นี้แล้วหรือไม่
    $check_sql = "SELECT id FROM users WHERE username = ?";
    if ($stmt = mysqli_prepare($objCon, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['error'] = "มีชื่อผู้ใช้นี้ในระบบแล้ว";
            header("Location: ../pages/users.php");
            exit;
        }
        mysqli_stmt_close($stmt);
    }

    // เพิ่มผู้ใช้ใหม่
    $sql = "INSERT INTO users (username, email, password, role_id, status) VALUES (?, ?, ?, ?, ?)";
    
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssis", $username, $email, $password, $role_id, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "เพิ่มผู้ใช้ใหม่สำเร็จ";
        } else {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มผู้ใช้";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($objCon));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }

    header("Location: ../pages/users.php");
    exit;
}

// แก้ไขผู้ใช้
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = mysqli_real_escape_string($objCon, $_POST['id']);
    $username = mysqli_real_escape_string($objCon, $_POST['username']);
    $email = mysqli_real_escape_string($objCon, $_POST['email']);
    $role_id = mysqli_real_escape_string($objCon, $_POST['role_id']);
    $status = mysqli_real_escape_string($objCon, $_POST['status']);

    // ตรวจสอบว่ามีชื่อผู้ใช้นี้แล้วหรือไม่ (ยกเว้นผู้ใช้ปัจจุบัน)
    $check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
    if ($stmt = mysqli_prepare($objCon, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "si", $username, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['error'] = "มีชื่อผู้ใช้นี้ในระบบแล้ว";
            header("Location: ../pages/users.php");
            exit;
        }
        mysqli_stmt_close($stmt);
    }

    // อัพเดทข้อมูลผู้ใช้
    if (!empty($_POST['password'])) {
        // ถ้ามีการเปลี่ยนรหัสผ่าน
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, email = ?, password = ?, role_id = ?, status = ? WHERE id = ?";
        $stmt = mysqli_prepare($objCon, $sql);
        mysqli_stmt_bind_param($stmt, "sssisi", $username, $email, $password, $role_id, $status, $id);
    } else {
        // ถ้าไม่มีการเปลี่ยนรหัสผ่าน
        $sql = "UPDATE users SET username = ?, email = ?, role_id = ?, status = ? WHERE id = ?";
        $stmt = mysqli_prepare($objCon, $sql);
        mysqli_stmt_bind_param($stmt, "ssisi", $username, $email, $role_id, $status, $id);
    }

    if ($stmt) {
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "แก้ไขข้อมูลผู้ใช้สำเร็จ";
        } else {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการแก้ไขข้อมูลผู้ใช้";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($objCon));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }

    header("Location: ../pages/users.php");
    exit;
}

// ลบผู้ใช้
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($objCon, $_GET['id']);

    // ป้องกันการลบตัวเอง
    if ($id == $_SESSION['user_login']['id']) {
        $_SESSION['error'] = "ไม่สามารถลบบัญชีของตัวเองได้";
        header("Location: ../pages/users.php");
        exit;
    }

    $sql = "DELETE FROM users WHERE id = ?";
    
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "ลบผู้ใช้สำเร็จ";
        } else {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบผู้ใช้";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($objCon));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }

    header("Location: ../pages/users.php");
    exit;
}

// หากไม่มี action ที่ถูกต้อง ให้ redirect กลับไปหน้าผู้ใช้
header("Location: ../pages/users.php");
exit; 