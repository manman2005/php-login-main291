<?php
// ตั้งค่า session security ก่อนเริ่ม session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();

// ตรวจสอบ session timeout (30 นาที)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['last_activity'] = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once("includes/db_connection.php");

    try {
        $objCon = connectDB();
        if (!$objCon) {
            throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
        }

        // รับข้อมูลจากฟอร์ม
        $login = mysqli_real_escape_string($objCon, trim($_POST['username']));
        $password = $_POST['password'];

        // Query หา user โดยใช้ email หรือ username
        $strSQL = "SELECT * FROM users WHERE email = ? OR username = ?";
        $stmt = mysqli_prepare($objCon, $strSQL);
        if ($stmt === false) {
            throw new Exception("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL");
        }
        mysqli_stmt_bind_param($stmt, "ss", $login, $login);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("เกิดข้อผิดพลาดในการค้นหาผู้ใช้");
        }

        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // ตรวจสอบรหัสผ่าน
            if (password_verify($password, $user['password'])) {
                // สร้าง session เก็บข้อมูลผู้ใช้
                $_SESSION['user_login'] = [
                    'id' => $user['id'],
                    'firstname' => $user['firstname'],
                    'lastname' => $user['lastname'],
                    'email' => $user['email'],
                    'role_id' => $user['role_id']
                ];

                // Redirect ตาม role_id
                if ($user['role_id'] == 1) {
                    header("Location: admin/pages/index.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $_SESSION['error'] = "ไม่พบชื่อผู้ใช้ในระบบ";
        }

        mysqli_stmt_close($stmt);
        mysqli_close($objCon);

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    // ถ้ามี error ให้กลับมาที่หน้า login
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ระบบเข้าสู่ระบบ</title>
    <!-- Bootstrap 5 -->
    <link href="bootstrap523/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <!-- Google Fonts - Kanit -->
    <link
        href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    />
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

        .form-signin h1 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .form-floating {
            margin-bottom: 1rem;
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

        .input-group-text {
            background: transparent;
            border: none;
            color: #666;
        }

        .password-toggle {
            cursor: pointer;
            padding: 0.5rem;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #666;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
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

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="text-center">
    <main class="form-signin">
        <?php if (isset($_SESSION['error'])): ?>
            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
            >
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>
            </div>
        <?php endif; ?>

        <form
            method="post"
            action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>"
            class="needs-validation"
            novalidate
        >
            <img
                src="vote_img/man05.jpg"
                alt="Logo"
                class="brand-logo"
            />
            <h1 class="mb-4">เข้าสู่ระบบ</h1>

            <div class="form-floating mb-3">
                <input
                    type="text"
                    class="form-control"
                    id="username"
                    name="username"
                    placeholder="อีเมลหรือชื่อผู้ใช้"
                    required
                />
                <label for="username">
                    <i class="fas fa-user me-2"></i>อีเมลหรือชื่อผู้ใช้
                </label>
                <div class="invalid-feedback">กรุณากรอกชื่อผู้ใช้</div>
            </div>

            <div class="form-floating mb-3 position-relative">
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    placeholder="รหัสผ่าน"
                    required
                />
                <label for="password">
                    <i class="fas fa-lock me-2"></i>รหัสผ่าน
                </label>
                <span class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </span>
                <div class="invalid-feedback">กรุณากรอกรหัสผ่าน</div>
            </div>

            <button class="w-100 btn btn-primary mb-3" type="submit">
                <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
            </button>

            <a href="register.php" class="register-link d-block"
                ><i class="fas fa-user-plus me-2"></i>ลงทะเบียนสมาชิกใหม่</a
            >
        </form>
    </main>

    <!-- Bootstrap Bundle with Popper -->
    <script src="bootstrap523/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const toggleIcon = document.getElementById("toggleIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }

        // Form validation
        (function () {
            "use strict";
            const forms = document.querySelectorAll(".needs-validation");
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener(
                    "submit",
                    function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add("was-validated");
                    },
                    false
                );
            });
        })();
    </script>
</body>

</html>
