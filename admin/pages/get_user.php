<?php
include_once("../includes/auth.php");
$objCon = connectDB();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user ID']);
    exit;
}

$id = mysqli_real_escape_string($objCon, $_GET['id']);
$sql = "SELECT * FROM users WHERE id = '$id'";
$result = mysqli_query($objCon, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    // ไม่ส่งรหัสผ่านกลับไป
    unset($user['password']);
    echo json_encode($user);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
}
?> 