<?php
session_start();
require_once("../includes/auth.php");

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['user_login']) || $_SESSION['user_login']['role_id'] != 1) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ';
    header("Location: ../../login.php");
    exit;
}

// ตรวจสอบว่าส่งข้อมูลผ่าน POST มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'กรุณาส่งข้อมูลผ่านแบบฟอร์มเท่านั้น';
    header("Location: ../pages/settings.php");
    exit;
}

try {
    $objCon = connectDB();
    
    if (!$objCon) {
        throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
    }

    // รับค่าจากฟอร์ม
    $site_name = mysqli_real_escape_string($objCon, $_POST['site_name']);
    $site_description = mysqli_real_escape_string($objCon, $_POST['site_description']);
    $admin_email = mysqli_real_escape_string($objCon, $_POST['admin_email']);
    $items_per_page = intval($_POST['items_per_page']);
    $max_candidates = intval($_POST['max_candidates']);
    $voting_duration = intval($_POST['voting_duration']);
    $allow_multiple_votes = intval($_POST['allow_multiple_votes']);
    $require_verification = intval($_POST['require_verification']);
    $enable_email_notifications = intval($_POST['enable_email_notifications']);
    $notification_before_end = intval($_POST['notification_before_end']);

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($site_name)) {
        throw new Exception("กรุณากรอกชื่อเว็บไซต์");
    }

    if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("รูปแบบอีเมลไม่ถูกต้อง");
    }

    if ($items_per_page < 5 || $items_per_page > 100) {
        throw new Exception("จำนวนรายการต่อหน้าต้องอยู่ระหว่าง 5-100");
    }

    if ($max_candidates < 1 || $max_candidates > 100) {
        throw new Exception("จำนวนผู้สมัครสูงสุดต้องอยู่ระหว่าง 1-100");
    }

    if ($voting_duration < 5 || $voting_duration > 1440) {
        throw new Exception("ระยะเวลาการเลือกตั้งต้องอยู่ระหว่าง 5-1440 นาที");
    }

    if ($notification_before_end < 5 || $notification_before_end > 120) {
        throw new Exception("เวลาแจ้งเตือนก่อนสิ้นสุดต้องอยู่ระหว่าง 5-120 นาที");
    }

    // ตรวจสอบว่ามีการตั้งค่าอยู่แล้วหรือไม่
    $check_sql = "SELECT COUNT(*) as count FROM system_settings";
    $check_result = mysqli_query($objCon, $check_sql);
    $row = mysqli_fetch_assoc($check_result);
    
    if ($row['count'] > 0) {
        // อัพเดตการตั้งค่า
        $sql = "UPDATE system_settings SET 
                site_name = ?, 
                site_description = ?, 
                admin_email = ?,
                items_per_page = ?,
                max_candidates = ?,
                voting_duration = ?,
                allow_multiple_votes = ?,
                require_verification = ?,
                enable_email_notifications = ?,
                notification_before_end = ?,
                updated_at = NOW()";
    } else {
        // สร้างการตั้งค่าใหม่
        $sql = "INSERT INTO system_settings (
                site_name, 
                site_description, 
                admin_email,
                items_per_page,
                max_candidates,
                voting_duration,
                allow_multiple_votes,
                require_verification,
                enable_email_notifications,
                notification_before_end,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    }

    $stmt = mysqli_prepare($objCon, $sql);
    
    if ($stmt === false) {
        throw new Exception("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . mysqli_error($objCon));
    }
    
    mysqli_stmt_bind_param($stmt, "sssiiiiiii", 
        $site_name,
        $site_description,
        $admin_email,
        $items_per_page,
        $max_candidates,
        $voting_duration,
        $allow_multiple_votes,
        $require_verification,
        $enable_email_notifications,
        $notification_before_end
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_stmt_error($stmt));
    }

    $_SESSION['success'] = 'บันทึกการตั้งค่าเรียบร้อยแล้ว';
    header("Location: ../pages/settings.php");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../pages/settings.php");
    exit;
} finally {
    // ปิดการเชื่อมต่อ statement และ database
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($objCon)) mysqli_close($objCon);
} 