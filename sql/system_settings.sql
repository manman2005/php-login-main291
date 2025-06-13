-- สร้างตาราง system_settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255) NOT NULL,
    site_description TEXT,
    admin_email VARCHAR(255) NOT NULL,
    items_per_page INT NOT NULL DEFAULT 10,
    max_candidates INT NOT NULL DEFAULT 10,
    voting_duration INT NOT NULL DEFAULT 60,
    allow_multiple_votes TINYINT(1) NOT NULL DEFAULT 0,
    require_verification TINYINT(1) NOT NULL DEFAULT 1,
    enable_email_notifications TINYINT(1) NOT NULL DEFAULT 0,
    notification_before_end INT NOT NULL DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- เพิ่มข้อมูลเริ่มต้น
INSERT INTO system_settings (
    site_name,
    site_description,
    admin_email,
    items_per_page,
    max_candidates,
    voting_duration,
    allow_multiple_votes,
    require_verification,
    enable_email_notifications,
    notification_before_end
) VALUES (
    'ระบบเลือกตั้งออนไลน์',
    'ระบบจัดการการเลือกตั้งออนไลน์',
    'admin@example.com',
    10,
    10,
    60,
    0,
    1,
    0,
    30
) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP; 