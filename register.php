<?php
session_start();

// ถ้าล็อกอินแล้วให้ไปหน้าหลัก
if (isset($_SESSION['user_login'])) {
    header("Location: index.php");
    exit;
}

// สร้าง CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);
            font-family: "Kanit", sans-serif;
        }
        .form-signin {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 380px;
            transition: transform 0.3s ease;
            animation: fadeIn 0.5s ease-out;
        }
        .form-signin:hover {
            transform: translateY(-5px);
        }
        .form-signin h2 {
            margin-bottom: 1.5rem;
            font-size: 1.6rem;
            color: #2c3e50;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
        }
        .input-group-text.password-toggle {
            background: transparent;
            border: none;
            color: #666;
            cursor: pointer;
        }
        .btn-primary {
            background: linear-gradient(45deg, #4a90e2, #5fb7ff);
            border: none;
            padding: 0.8rem;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #357abd, #4a90e2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
        }
        .btn-secondary {
            font-weight: 500;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .brand-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #4a90e2;
            background: #f8f9fa;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }
        .register-link {
            margin-top: 1rem;
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .register-link:hover {
            color: #4a90e2;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px);}
            to { opacity: 1; transform: translateY(0);}
        }
        @media (max-width: 576px) {
            .form-signin {
                padding: 1.5rem 0.5rem;
            }
        }
    </style>
</head>

<body>
    <main class="form-signin">
        <img src="vote_img/man05.jpg" alt="Logo" class="brand-logo" />
        <h2 class="mb-4">ลงทะเบียนผู้ใช้งาน</h2>
        
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
            
            <div class="mb-3">
                <label for="username" class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" required minlength="3" maxlength="50" pattern="^[A-Za-z0-9_.-]+$">
                <div class="invalid-feedback">
                    กรุณากรอกชื่อผู้ใช้ (a-z, 0-9, _, . หรือ - เท่านั้น)
                </div>
            </div>

            <div class="mb-3">
                <label for="fullname" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="fullname" name="fullname" required maxlength="100">
                <div class="invalid-feedback">
                    กรุณากรอกชื่อ-นามสกุล
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required maxlength="100">
                <div class="invalid-feedback">
                    กรุณากรอกอีเมลให้ถูกต้อง
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                <div class="input-group position-relative">
                    <input type="password" class="form-control" id="password" name="password" required
                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" maxlength="255" autocomplete="new-password">
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
                <div class="input-group position-relative">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required maxlength="255" autocomplete="new-password">
                    <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="invalid-feedback">
                    กรุณายืนยันรหัสผ่านให้ตรงกัน
                </div>
            </div>

            <button type="submit" class="w-100 btn btn-primary mb-3">
                <i class="fas fa-user-plus me-2"></i>ลงทะเบียน
            </button>
            <a href="login.php" class="register-link d-block">
                <i class="fas fa-sign-in-alt me-2"></i>กลับไปหน้าเข้าสู่ระบบ
            </a>
        </form>
    </main>

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