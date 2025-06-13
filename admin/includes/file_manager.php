<?php
class FileManager {
    private $upload_path;
    private $allowed_types;
    private $max_size;
    
    public function __construct($upload_path = 'uploads', $allowed_types = ['jpg', 'jpeg', 'png'], $max_size = 5242880) {
        $this->upload_path = rtrim($upload_path, '/');
        $this->allowed_types = $allowed_types;
        $this->max_size = $max_size; // 5MB default
        
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!file_exists($this->upload_path)) {
            mkdir($this->upload_path, 0755, true);
        }
    }
    
    public function upload($file, $custom_name = null) {
        try {
            // ตรวจสอบว่ามีไฟล์ถูกอัพโหลดหรือไม่
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                throw new Exception('ไม่พบไฟล์ที่อัพโหลด');
            }
            
            // ตรวจสอบขนาดไฟล์
            if ($file['size'] > $this->max_size) {
                throw new Exception('ขนาดไฟล์ใหญ่เกินไป (สูงสุด ' . ($this->max_size / 1024 / 1024) . 'MB)');
            }
            
            // ตรวจสอบประเภทไฟล์
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $this->allowed_types)) {
                throw new Exception('ประเภทไฟล์ไม่ได้รับอนุญาต');
            }
            
            // สร้างชื่อไฟล์
            $filename = $custom_name ? $custom_name . '.' . $ext : uniqid() . '_' . time() . '.' . $ext;
            $filepath = $this->upload_path . '/' . $filename;
            
            // ย้ายไฟล์
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('ไม่สามารถบันทึกไฟล์ได้');
            }
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function delete($filename) {
        $filepath = $this->upload_path . '/' . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    public function getFileList() {
        $files = [];
        if (is_dir($this->upload_path)) {
            $items = scandir($this->upload_path);
            foreach ($items as $item) {
                if ($item != '.' && $item != '..') {
                    $filepath = $this->upload_path . '/' . $item;
                    $files[] = [
                        'name' => $item,
                        'size' => filesize($filepath),
                        'modified' => filemtime($filepath),
                        'type' => mime_content_type($filepath)
                    ];
                }
            }
        }
        return $files;
    }
} 