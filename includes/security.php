<?php
class Security {
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    public static function sanitizeInput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
        } else {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function validateTime($time, $format = 'H:i:s') {
        $d = DateTime::createFromFormat($format, $time);
        return $d && $d->format($format) === $time;
    }

    public static function validateInteger($value, $min = null, $max = null) {
        $options = array("options" => array());
        if ($min !== null) {
            $options["options"]["min_range"] = $min;
        }
        if ($max !== null) {
            $options["options"]["max_range"] = $max;
        }
        return filter_var($value, FILTER_VALIDATE_INT, $options) !== false;
    }

    public static function validateUsername($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }

    public static function validatePassword($password) {
        // ตรวจสอบความยาวขั้นต่ำ 8 ตัวอักษร
        if (strlen($password) < 8) {
            return false;
        }
        
        // ต้องมีตัวอักษรพิมพ์ใหญ่อย่างน้อย 1 ตัว
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // ต้องมีตัวอักษรพิมพ์เล็กอย่างน้อย 1 ตัว
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // ต้องมีตัวเลขอย่างน้อย 1 ตัว
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // ต้องมีอักขระพิเศษอย่างน้อย 1 ตัว
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            return false;
        }
        
        return true;
    }

    public static function generateRandomString($length = 10) {
        return bin2hex(random_bytes(($length - ($length % 2)) / 2));
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function logUserAction($userId, $action, $details = null) {
        global $objCon;
        
        $sql = "INSERT INTO user_logs (user_id, action, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
                
        $stmt = $objCon->prepare($sql);
        $stmt->bind_param("issss", 
            $userId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );
        
        return $stmt->execute();
    }
} 