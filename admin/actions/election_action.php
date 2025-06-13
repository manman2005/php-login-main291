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
    header("Location: ../pages/elections.php");
    exit;
}

// เพิ่มการเลือกตั้งใหม่
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $title = mysqli_real_escape_string($objCon, $_POST['title']);
    $description = mysqli_real_escape_string($objCon, $_POST['description']);
    $start_date = mysqli_real_escape_string($objCon, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($objCon, $_POST['end_date']);

    // ตรวจสอบวันที่
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $now = new DateTime();

    if ($start < $now) {
        $_SESSION['error'] = "ไม่สามารถกำหนดวันที่เริ่มต้นย้อนหลังได้";
        header("Location: ../pages/elections.php");
        exit;
    }

    if ($end <= $start) {
        $_SESSION['error'] = "วันที่สิ้นสุดต้องมากกว่าวันที่เริ่มต้น";
        header("Location: ../pages/elections.php");
        exit;
    }

    $sql = "INSERT INTO elections (title, description, start_date, end_date) VALUES (?, ?, ?, ?)";
    
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssss", $title, $description, $start_date, $end_date);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "เพิ่มการเลือกตั้งใหม่สำเร็จ";
        } else {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มการเลือกตั้ง";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($objCon));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }

    header("Location: ../pages/elections.php");
    exit;
}

// แก้ไขการเลือกตั้ง
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = mysqli_real_escape_string($objCon, $_POST['id']);
    $title = mysqli_real_escape_string($objCon, $_POST['title']);
    $description = mysqli_real_escape_string($objCon, $_POST['description']);
    $start_date = mysqli_real_escape_string($objCon, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($objCon, $_POST['end_date']);

    // ตรวจสอบว่าการเลือกตั้งเริ่มแล้วหรือยัง
    $sql = "SELECT start_date FROM elections WHERE id = ?";
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $election = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $election_start = new DateTime($election['start_date']);
        $now = new DateTime();

        if ($now > $election_start) {
            $_SESSION['error'] = "ไม่สามารถแก้ไขการเลือกตั้งที่เริ่มแล้วได้";
            header("Location: ../pages/elections.php");
            exit;
        }
    }

    // ตรวจสอบวันที่
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $now = new DateTime();

    if ($start < $now) {
        $_SESSION['error'] = "ไม่สามารถกำหนดวันที่เริ่มต้นย้อนหลังได้";
        header("Location: ../pages/election_edit.php?id=" . $id);
        exit;
    }

    if ($end <= $start) {
        $_SESSION['error'] = "วันที่สิ้นสุดต้องมากกว่าวันที่เริ่มต้น";
        header("Location: ../pages/election_edit.php?id=" . $id);
        exit;
    }

    $sql = "UPDATE elections SET title = ?, description = ?, start_date = ?, end_date = ? WHERE id = ?";
    
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssi", $title, $description, $start_date, $end_date, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "แก้ไขการเลือกตั้งสำเร็จ";
        } else {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการแก้ไขการเลือกตั้ง";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($objCon));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }

    header("Location: ../pages/elections.php");
    exit;
}

// ลบการเลือกตั้ง
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($objCon, $_GET['id']);

    // ตรวจสอบว่าการเลือกตั้งเริ่มแล้วหรือยัง
    $sql = "SELECT start_date FROM elections WHERE id = ?";
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $election = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $election_start = new DateTime($election['start_date']);
        $now = new DateTime();

        if ($now > $election_start) {
            $_SESSION['error'] = "ไม่สามารถลบการเลือกตั้งที่เริ่มแล้วได้";
            header("Location: ../pages/elections.php");
            exit;
        }
    }

    $sql = "DELETE FROM elections WHERE id = ?";
    if ($stmt = mysqli_prepare($objCon, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "ลบการเลือกตั้งสำเร็จ";
        } else {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบการเลือกตั้ง";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($objCon));
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }

    header("Location: ../pages/elections.php");
    exit;
}

// หากไม่มี action ที่ถูกต้อง ให้ redirect กลับไปหน้าการเลือกตั้ง
header("Location: ../pages/elections.php");
exit; 