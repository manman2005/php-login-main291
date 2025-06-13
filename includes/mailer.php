<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . '/../vendor/autoload.php');

class Mailer {
    private $mail;
    private $settings;
    
    public function __construct() {
        global $objCon;
        
        // ดึงการตั้งค่าจากฐานข้อมูล
        $sql = "SELECT * FROM system_settings LIMIT 1";
        $result = mysqli_query($objCon, $sql);
        $this->settings = mysqli_fetch_assoc($result);
        
        // สร้าง PHPMailer instance
        $this->mail = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = $_ENV['SMTP_HOST'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $_ENV['SMTP_USERNAME'];
            $this->mail->Password = $_ENV['SMTP_PASSWORD'];
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = $_ENV['SMTP_PORT'];
            $this->mail->CharSet = 'UTF-8';
            
            // Default settings
            $this->mail->setFrom($this->settings['admin_email'], $this->settings['site_name']);
            
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
        }
    }
    
    public function sendVoteConfirmation($userId, $electionId) {
        global $objCon;
        
        try {
            // ดึงข้อมูลผู้ใช้
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $objCon->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            // ดึงข้อมูลการเลือกตั้ง
            $sql = "SELECT * FROM voting WHERE vote_id = ?";
            $stmt = $objCon->prepare($sql);
            $stmt->bind_param("i", $electionId);
            $stmt->execute();
            $election = $stmt->get_result()->fetch_assoc();
            
            // ตั้งค่าผู้รับ
            $this->mail->addAddress($user['email'], $user['fullname']);
            
            // เนื้อหาอีเมล
            $this->mail->isHTML(true);
            $this->mail->Subject = 'ยืนยันการลงคะแนน - ' . $election['vote_name'];
            
            $body = "
                <h2>ยืนยันการลงคะแนน</h2>
                <p>เรียน {$user['fullname']}</p>
                <p>ระบบได้รับการลงคะแนนของท่านในการเลือกตั้ง \"{$election['vote_name']}\" เรียบร้อยแล้ว</p>
                <p>รายละเอียด:</p>
                <ul>
                    <li>วันที่: " . date('d/m/Y', strtotime($election['date'])) . "</li>
                    <li>เวลา: " . date('H:i', strtotime($election['start_time'])) . " - " . date('H:i', strtotime($election['end_time'])) . "</li>
                </ul>
                <p>ขอบคุณที่ร่วมใช้สิทธิ์ในการเลือกตั้งครั้งนี้</p>
                <br>
                <p>ขอแสดงความนับถือ<br>{$this->settings['site_name']}</p>
            ";
            
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);
            
            return $this->mail->send();
            
        } catch (Exception $e) {
            error_log("Mail Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendElectionReminder($userId, $electionId) {
        global $objCon;
        
        try {
            // ดึงข้อมูลผู้ใช้
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $objCon->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            // ดึงข้อมูลการเลือกตั้ง
            $sql = "SELECT * FROM voting WHERE vote_id = ?";
            $stmt = $objCon->prepare($sql);
            $stmt->bind_param("i", $electionId);
            $stmt->execute();
            $election = $stmt->get_result()->fetch_assoc();
            
            // ตั้งค่าผู้รับ
            $this->mail->addAddress($user['email'], $user['fullname']);
            
            // เนื้อหาอีเมล
            $this->mail->isHTML(true);
            $this->mail->Subject = 'แจ้งเตือนการเลือกตั้ง - ' . $election['vote_name'];
            
            $timeLeft = strtotime($election['end_time']) - time();
            $hoursLeft = floor($timeLeft / 3600);
            $minutesLeft = floor(($timeLeft % 3600) / 60);
            
            $body = "
                <h2>แจ้งเตือนการเลือกตั้ง</h2>
                <p>เรียน {$user['fullname']}</p>
                <p>การเลือกตั้ง \"{$election['vote_name']}\" กำลังจะสิ้นสุดในอีก {$hoursLeft} ชั่วโมง {$minutesLeft} นาที</p>
                <p>รายละเอียด:</p>
                <ul>
                    <li>วันที่: " . date('d/m/Y', strtotime($election['date'])) . "</li>
                    <li>เวลา: " . date('H:i', strtotime($election['start_time'])) . " - " . date('H:i', strtotime($election['end_time'])) . "</li>
                </ul>
                <p>หากท่านยังไม่ได้ลงคะแนน กรุณาเข้าระบบและลงคะแนนก่อนหมดเวลา</p>
                <br>
                <p>ขอแสดงความนับถือ<br>{$this->settings['site_name']}</p>
            ";
            
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);
            
            return $this->mail->send();
            
        } catch (Exception $e) {
            error_log("Mail Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendElectionResults($electionId) {
        global $objCon;
        
        try {
            // ดึงข้อมูลการเลือกตั้ง
            $sql = "SELECT * FROM voting WHERE vote_id = ?";
            $stmt = $objCon->prepare($sql);
            $stmt->bind_param("i", $electionId);
            $stmt->execute();
            $election = $stmt->get_result()->fetch_assoc();
            
            // ดึงข้อมูลผู้สมัครและผลคะแนน
            $sql = "SELECT c.*, COUNT(v.id) as vote_count 
                    FROM candidates c 
                    LEFT JOIN votes v ON c.candidate_id = v.candidate_id 
                    WHERE c.vote_id = ? 
                    GROUP BY c.candidate_id 
                    ORDER BY vote_count DESC";
            $stmt = $objCon->prepare($sql);
            $stmt->bind_param("i", $electionId);
            $stmt->execute();
            $candidates = $stmt->get_result();
            
            // ดึงรายชื่อผู้มีสิทธิ์เลือกตั้ง
            $sql = "SELECT * FROM users WHERE role_id = 2";
            $voters = mysqli_query($objCon, $sql);
            
            while ($voter = $voters->fetch_assoc()) {
                // ตั้งค่าผู้รับ
                $this->mail->addAddress($voter['email'], $voter['fullname']);
                
                // เนื้อหาอีเมล
                $this->mail->isHTML(true);
                $this->mail->Subject = 'ประกาศผลการเลือกตั้ง - ' . $election['vote_name'];
                
                $body = "
                    <h2>ประกาศผลการเลือกตั้ง</h2>
                    <p>เรียน {$voter['fullname']}</p>
                    <p>การเลือกตั้ง \"{$election['vote_name']}\" ได้สิ้นสุดลงแล้ว</p>
                    <p>ผลการเลือกตั้งมีดังนี้:</p>
                    <table border='1' cellpadding='5' style='border-collapse: collapse;'>
                        <tr>
                            <th>หมายเลข</th>
                            <th>ชื่อผู้สมัคร</th>
                            <th>คะแนน</th>
                        </tr>
                ";
                
                while ($candidate = $candidates->fetch_assoc()) {
                    $body .= "
                        <tr>
                            <td>{$candidate['candidate_number']}</td>
                            <td>{$candidate['description']}</td>
                            <td>{$candidate['vote_count']}</td>
                        </tr>
                    ";
                }
                
                $body .= "
                    </table>
                    <br>
                    <p>ขอขอบคุณทุกท่านที่ร่วมใช้สิทธิ์ในการเลือกตั้งครั้งนี้</p>
                    <br>
                    <p>ขอแสดงความนับถือ<br>{$this->settings['site_name']}</p>
                ";
                
                $this->mail->Body = $body;
                $this->mail->AltBody = strip_tags($body);
                
                $this->mail->send();
                $this->mail->clearAddresses();
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Mail Error: " . $e->getMessage());
            return false;
        }
    }
} 