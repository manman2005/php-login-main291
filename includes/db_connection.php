<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "php_login";

function connectDB() {
    global $servername, $username, $password, $dbname;
    
    // สร้างการเชื่อมต่อ
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // ตรวจสอบการเชื่อมต่อ
    if (!$conn) {
        die("การเชื่อมต่อล้มเหลว: " . mysqli_connect_error());
    }
    
    // ตั้งค่า charset เป็น utf8
    mysqli_set_charset($conn, "utf8");
    
    return $conn;
}
?>
