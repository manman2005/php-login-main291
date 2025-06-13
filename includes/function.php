<?php
// ฟังก์ชันสำหรับการเข้ารหัสรหัสผ่าน
function passwordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// ฟังก์ชันสำหรับการตรวจสอบรหัสผ่าน
function passwordVerify($password, $hash) {
    return password_verify($password, $hash);
}

// ฟังก์ชันสำหรับการแสดงข้อความแจ้งเตือน
function alert($type, $message) {
    switch ($type) {
        case 'success':
            $icon = 'check-circle';
            $color = 'success';
            break;
        case 'error':
            $icon = 'exclamation-triangle';
            $color = 'danger';
            break;
        case 'warning':
            $icon = 'exclamation-circle';
            $color = 'warning';
            break;
        default:
            $icon = 'info-circle';
            $color = 'info';
    }
    
    return "<div class='alert alert-{$color} alert-dismissible fade show' role='alert'>
                <i class='fas fa-{$icon} me-2'></i>{$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}

// ฟังก์ชันสำหรับการตรวจสอบการเข้าสู่ระบบ
function checkLogin() {
    if (!isset($_SESSION['user_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
        header("Location: login.php");
        exit;
    }
}

// ฟังก์ชันสำหรับการแปลงวันที่เป็นภาษาไทย
function thai_date($date) {
    $thai_month_arr = array(
        "0" => "",
        "1" => "มกราคม",
        "2" => "กุมภาพันธ์",
        "3" => "มีนาคม",
        "4" => "เมษายน",
        "5" => "พฤษภาคม",
        "6" => "มิถุนายน",
        "7" => "กรกฎาคม",
        "8" => "สิงหาคม",
        "9" => "กันยายน",
        "10" => "ตุลาคม",
        "11" => "พฤศจิกายน",
        "12" => "ธันวาคม"
    );

    $thai_date_return = date("j", strtotime($date));
    $thai_date_return .= " " . $thai_month_arr[date("n", strtotime($date))];
    $thai_date_return .= " " . (date("Y", strtotime($date)) + 543);
    
    return $thai_date_return;
}

// ฟังก์ชันสำหรับการแปลงเวลาเป็นรูปแบบไทย
function thai_time($time) {
    return date("H:i", strtotime($time)) . " น.";
}

// ฟังก์ชันสร้าง CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// ฟังก์ชันตรวจสอบ CSRF token
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// ฟังก์ชันทำความสะอาดข้อมูล
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// ฟังก์ชันตรวจสอบนามสกุลไฟล์
function allowedImageType($filename) {
    $allowed = array('jpg', 'jpeg', 'png', 'gif');
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed);
}

// ฟังก์ชันสร้างชื่อไฟล์ใหม่
function generateNewFileName($originalName) {
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return uniqid() . '_' . time() . '.' . $ext;
}
?>