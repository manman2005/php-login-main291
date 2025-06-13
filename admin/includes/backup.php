<?php
require_once(__DIR__ . '/../../includes/config.php');

class BackupSystem {
    private $db_host;
    private $db_name;
    private $db_user;
    private $db_pass;
    private $backup_path;
    
    public function __construct() {
        $this->db_host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->db_user = DB_USER;
        $this->db_pass = DB_PASS;
        $this->backup_path = __DIR__ . '/../../backups';
        
        // สร้างโฟลเดอร์สำรองข้อมูลถ้ายังไม่มี
        if (!file_exists($this->backup_path)) {
            mkdir($this->backup_path, 0755, true);
        }
    }
    
    public function backupDatabase() {
        try {
            $date = date('Y-m-d_H-i-s');
            $filename = $this->backup_path . "/db_backup_{$date}.sql";
            
            // สร้างคำสั่ง mysqldump
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s',
                escapeshellarg($this->db_host),
                escapeshellarg($this->db_user),
                escapeshellarg($this->db_pass),
                escapeshellarg($this->db_name),
                escapeshellarg($filename)
            );
            
            // ทำการ backup
            exec($command, $output, $return_var);
            
            if ($return_var !== 0) {
                throw new Exception("การสำรองฐานข้อมูลล้มเหลว");
            }
            
            // บีบอัดไฟล์
            $zip = new ZipArchive();
            $zipname = $filename . '.zip';
            
            if ($zip->open($zipname, ZipArchive::CREATE) === TRUE) {
                $zip->addFile($filename, basename($filename));
                $zip->close();
                
                // ลบไฟล์ .sql
                unlink($filename);
                
                return [
                    'success' => true,
                    'filename' => basename($zipname),
                    'path' => $zipname
                ];
            } else {
                throw new Exception("ไม่สามารถสร้างไฟล์ ZIP ได้");
            }
            
        } catch (Exception $e) {
            error_log("Backup Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function backupFiles() {
        try {
            $date = date('Y-m-d_H-i-s');
            $filename = $this->backup_path . "/files_backup_{$date}.zip";
            
            $zip = new ZipArchive();
            if ($zip->open($filename, ZipArchive::CREATE) === TRUE) {
                // เพิ่มไฟล์ในโฟลเดอร์ uploads
                $this->addFolderToZip($zip, UPLOAD_PATH, 'uploads');
                
                $zip->close();
                
                return [
                    'success' => true,
                    'filename' => basename($filename),
                    'path' => $filename
                ];
            } else {
                throw new Exception("ไม่สามารถสร้างไฟล์ ZIP ได้");
            }
            
        } catch (Exception $e) {
            error_log("Backup Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function addFolderToZip($zip, $folder, $base_folder) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $base_folder . '/' . substr($filePath, strlen($folder) + 1);
                
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    public function restoreDatabase($backup_file) {
        try {
            // ตรวจสอบไฟล์
            if (!file_exists($backup_file)) {
                throw new Exception("ไม่พบไฟล์สำรองข้อมูล");
            }
            
            // แตกไฟล์ ZIP
            $zip = new ZipArchive();
            if ($zip->open($backup_file) === TRUE) {
                $sql_file = $this->backup_path . '/temp_restore.sql';
                $zip->extractTo($this->backup_path);
                $zip->close();
                
                // คำสั่งกู้คืนฐานข้อมูล
                $command = sprintf(
                    'mysql --host=%s --user=%s --password=%s %s < %s',
                    escapeshellarg($this->db_host),
                    escapeshellarg($this->db_user),
                    escapeshellarg($this->db_pass),
                    escapeshellarg($this->db_name),
                    escapeshellarg($sql_file)
                );
                
                exec($command, $output, $return_var);
                
                // ลบไฟล์ชั่วคราว
                unlink($sql_file);
                
                if ($return_var !== 0) {
                    throw new Exception("การกู้คืนฐานข้อมูลล้มเหลว");
                }
                
                return [
                    'success' => true,
                    'message' => 'กู้คืนฐานข้อมูลสำเร็จ'
                ];
            } else {
                throw new Exception("ไม่สามารถแตกไฟล์ ZIP ได้");
            }
            
        } catch (Exception $e) {
            error_log("Restore Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function restoreFiles($backup_file) {
        try {
            // ตรวจสอบไฟล์
            if (!file_exists($backup_file)) {
                throw new Exception("ไม่พบไฟล์สำรองข้อมูล");
            }
            
            // แตกไฟล์ ZIP
            $zip = new ZipArchive();
            if ($zip->open($backup_file) === TRUE) {
                $zip->extractTo(dirname(UPLOAD_PATH));
                $zip->close();
                
                return [
                    'success' => true,
                    'message' => 'กู้คืนไฟล์สำเร็จ'
                ];
            } else {
                throw new Exception("ไม่สามารถแตกไฟล์ ZIP ได้");
            }
            
        } catch (Exception $e) {
            error_log("Restore Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getBackupList() {
        $backups = [];
        
        if (is_dir($this->backup_path)) {
            $files = scandir($this->backup_path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $filepath = $this->backup_path . '/' . $file;
                    $backups[] = [
                        'name' => $file,
                        'size' => filesize($filepath),
                        'date' => filemtime($filepath),
                        'type' => pathinfo($file, PATHINFO_EXTENSION)
                    ];
                }
            }
        }
        
        // เรียงตามวันที่ล่าสุด
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });
        
        return $backups;
    }
    
    public function deleteBackup($filename) {
        $filepath = $this->backup_path . '/' . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
} 