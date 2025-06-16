<?php
session_start(); // เปิดใช้งาน session

// ถ้าล็อกอินแล้วให้ไปหน้าหลัก
if (isset($_SESSION['user_login'])) {
    header("Location: index.php");
    exit;
}

// สร้าง CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// รวมไฟล์เชื่อมต่อฐานข้อมูล (สำหรับใช้ในฟอร์มนี้ถ้าต้องการ)
include_once("includes/db_connection.php");
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน - ระบบเลือกตั้งออนไลน์</title>
    <!-- Bootstrap 5 -->
    <link href="bootstrap523/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8f9fa;
        }
        .register-form {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .password-toggle {
            cursor: pointer;
        }
        .error-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="register-form">
        <h2 class="text-center mb-4">ลงทะเบียนผู้ใช้งาน</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form id="registerForm" action="register_action.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="firstname" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="firstname" name="firstname" required
                           pattern="^[ก-์A-Za-z\s]+$" minlength="2">
                    <div class="invalid-feedback">
                        กรุณากรอกชื่อให้ถูกต้อง (ภาษาไทยหรืออังกฤษเท่านั้น)
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="lastname" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="lastname" name="lastname" required
                           pattern="^[ก-์A-Za-z\s]+$" minlength="2">
                    <div class="invalid-feedback">
                        กรุณากรอกนามสกุลให้ถูกต้อง (ภาษาไทยหรืออังกฤษเท่านั้น)
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">
                    กรุณากรอกอีเมลให้ถูกต้อง
                </div>
            </div>

            <div class="mb-3">
                <label for="student_id" class="form-label">รหัสนักศึกษา <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="student_id" name="student_id" required
                       pattern="^\d{11}$">
                <div class="invalid-feedback">
                    กรุณากรอกรหัสนักศึกษา 11 หลัก
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required
                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$">
                    <span class="input-group-text password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="invalid-feedback">
                    รหัสผ่านต้องมีอย่างน้อย 8 ตัว ประกอบด้วยตัวอักษรและตัวเลข
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="invalid-feedback">
                    กรุณายืนยันรหัสผ่านให้ตรงกัน
                </div>
            </div>

            <div class="mb-3">
                <label for="faculty" class="form-label">คณะ <span class="text-danger">*</span></label>
                <select class="form-select" id="faculty" name="faculty" required>
                    <option value="">เลือกคณะ</option>
                    <option value="วิศวกรรมศาสตร์">วิศวกรรมศาสตร์</option>
                    <option value="วิทยาศาสตร์">วิทยาศาสตร์</option>
                    <option value="ครุศาสตร์อุตสาหกรรม">ครุศาสตร์อุตสาหกรรม</option>
                    <option value="เทคโนโลยีการเกษตร">เทคโนโลยีการเกษตร</option>
                    <option value="เทคโนโลยีสารสนเทศ">เทคโนโลยีสารสนเทศ</option>
                    <option value="การบริหารและการจัดการ">การบริหารและการจัดการ</option>
                    <option value="ศิลปศาสตร์">ศิลปศาสตร์</option>
                    <option value="สถาปัตยกรรมศาสตร์">สถาปัตยกรรมศาสตร์</option>
                    <option value="การแพทย์">การแพทย์</option>
                </select>
                <div class="invalid-feedback">
                    กรุณาเลือกคณะ
                </div>
            </div>

            <div class="mb-3">
                <label for="department" class="form-label">สาขาวิชา <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="department" name="department" required>
                <div class="invalid-feedback">
                    กรุณากรอกสาขาวิชา
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">ลงทะเบียน</button>
                <a href="login.php" class="btn btn-secondary">กลับไปหน้าเข้าสู่ระบบ</a>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="bootstrap523/js/bootstrap.bundle.min.js"></script>

<script>
// ตรวจสอบการกรอกข้อมูล
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            // ตรวจสอบรหัสผ่านตรงกัน
            var password = document.getElementById('password')
            var confirm_password = document.getElementById('confirm_password')
            if (password.value !== confirm_password.value) {
                confirm_password.setCustomValidity('รหัสผ่านไม่ตรงกัน')
                event.preventDefault()
                event.stopPropagation()
            } else {
                confirm_password.setCustomValidity('')
            }

            form.classList.add('was-validated')
        }, false)
    })
})()

// สลับการแสดงรหัสผ่าน
function togglePassword(id) {
    var input = document.getElementById(id);
    var icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

</body>
</html>