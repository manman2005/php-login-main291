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
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
}

// ตรวจสอบการลบผู้ใช้
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($objCon, $_GET['delete']);
    // ไม่อนุญาตให้ลบตัวเอง
    if ($id != $_SESSION['user_login']['id']) {
        $sql = "DELETE FROM users WHERE id = '$id'";
        if (mysqli_query($objCon, $sql)) {
            header("Location: users.php?success=1");
            exit;
        } else {
            $error = "ไม่สามารถลบผู้ใช้ได้: " . mysqli_error($objCon);
        }
    } else {
        $error = "ไม่สามารถลบบัญชีของตัวเองได้";
    }
}

// จัดการการเพิ่ม/แก้ไขผู้ใช้
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? mysqli_real_escape_string($objCon, $_POST['id']) : null;
    $username = mysqli_real_escape_string($objCon, $_POST['username']);
    $fullname = mysqli_real_escape_string($objCon, $_POST['fullname']);
    $email = mysqli_real_escape_string($objCon, $_POST['email']);
    $role_id = mysqli_real_escape_string($objCon, $_POST['role_id']);
    $status = isset($_POST['is_active']) ? 'active' : 'inactive';

    // ตรวจสอบความถูกต้องของข้อมูล
    if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        $error = "ชื่อผู้ใช้ต้องมีความยาว 4-20 ตัวอักษร และใช้ได้เฉพาะตัวอักษรภาษาอังกฤษ ตัวเลข และ _";
    } elseif (empty($fullname)) {
        $error = "กรุณากรอกชื่อ-นามสกุล";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    } else {
        // ตรวจสอบว่ามี username หรือ email ซ้ำหรือไม่
        $check_sql = "SELECT id FROM users WHERE (username = '$username' OR email = '$email')";
        if ($id) {
            $check_sql .= " AND id != '$id'";
        }
        $check_result = mysqli_query($objCon, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบแล้ว";
        } else {
            if ($id) {
                // แก้ไขผู้ใช้
                $sql = "UPDATE users SET 
                        username = '$username',
                        fullname = '$fullname',
                        email = '$email',
                        role_id = '$role_id',
                        status = '$status'
                        WHERE id = '$id'";
                
                // ถ้ามีการเปลี่ยนรหัสผ่าน
                if (!empty($_POST['password'])) {
                    if (strlen($_POST['password']) < 8) {
                        $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร";
                    } else {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET 
                                username = '$username',
                                fullname = '$fullname',
                                email = '$email',
                                password = '$password',
                                role_id = '$role_id',
                                status = '$status'
                                WHERE id = '$id'";
                    }
                }
            } else {
                // เพิ่มผู้ใช้ใหม่
                if (empty($_POST['password']) || strlen($_POST['password']) < 8) {
                    $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร";
                } else {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql = "INSERT INTO users (username, fullname, email, password, role_id, status) 
                            VALUES ('$username', '$fullname', '$email', '$password', '$role_id', '$status')";
                }
            }

            if (!isset($error) && mysqli_query($objCon, $sql)) {
                // บันทึก log การทำงาน
                $action = $id ? "edit_user" : "add_user";
                $log_sql = "INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)";
                $log_stmt = mysqli_prepare($objCon, $log_sql);
                if ($log_stmt) {
                    $admin_id = $_SESSION['user_login']['id'];
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    mysqli_stmt_bind_param($log_stmt, "isss", $admin_id, $action, $ip, $user_agent);
                    mysqli_stmt_execute($log_stmt);
                    mysqli_stmt_close($log_stmt);
                }
                
                header("Location: users.php?success=1");
                exit;
            } elseif (!isset($error)) {
                $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($objCon);
            }
        }
    }
}

// ดึงข้อมูลผู้ใช้ที่ต้องการแก้ไข
$edit_user = null;
if (isset($_GET['edit'])) {
    $id = mysqli_real_escape_string($objCon, $_GET['edit']);
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $result = mysqli_query($objCon, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $edit_user = mysqli_fetch_assoc($result);
    }
}

// ดึงข้อมูลบทบาท
$roles = [];
$sql = "SELECT * FROM user_roles ORDER BY role_id";
$result = mysqli_query($objCon, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $roles[] = $row;
    }
}

// ดึงข้อมูลผู้ใช้ทั้งหมด
$sql = "SELECT u.*, r.role_name 
        FROM users u 
        LEFT JOIN user_roles r ON u.role_id = r.role_id 
        ORDER BY u.created_at DESC";
$users = mysqli_query($objCon, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้ - ระบบเลือกตั้งออนไลน์</title>
    <!-- Bootstrap 5 -->
    <link href="../../bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,.75);
        }
        .nav-link:hover {
            color: white;
        }
        .nav-link.active {
            background: rgba(255,255,255,.1);
        }
        .stats-card {
            transition: all 0.3s ease;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include_once("../includes/sidebar.php"); ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-users me-2"></i>จัดการผู้ใช้</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus me-2"></i>เพิ่มผู้ใช้
                    </button>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success" role="alert">
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php } ?>
            
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php } ?>

            <!-- Users List -->
            <div class="card stats-card">
                <div class="card-body">
                    <?php if (mysqli_num_rows($users) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ชื่อผู้ใช้</th>
                                        <th>อีเมล</th>
                                        <th>สิทธิ์</th>
                                        <th>สถานะ</th>
                                        <th>วันที่สร้าง</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $user['role_id'] == 1 ? 'bg-danger' : 'bg-info'; ?>">
                                                    <?php echo htmlspecialchars($user['role_name']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $user['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $user['status'] == 'active' ? 'ใช้งาน' : 'ระงับ'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['user_login']['id']): ?>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            ยังไม่มีข้อมูลผู้ใช้
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มผู้ใช้</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/user_action.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">สิทธิ์</label>
                        <select class="form-select" id="role" name="role_id" required>
                            <option value="2">ผู้ใช้ทั่วไป</option>
                            <option value="1">ผู้ดูแลระบบ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">สถานะ</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">ใช้งาน</option>
                            <option value="inactive">ระงับ</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="action" value="add" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="../../bootstrap523/js/bootstrap.bundle.min.js"></script>
<script>
function editUser(id) {
    window.location.href = 'user_edit.php?id=' + id;
}

function deleteUser(id) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?')) {
        window.location.href = '../actions/user_action.php?action=delete&id=' + id;
    }
}
</script>

</body>
</html> 