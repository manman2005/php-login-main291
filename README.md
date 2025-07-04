# ระบบเลือกตั้งออนไลน์ด้วย PHP

## ภาพรวม

โปรเจกต์นี้คือระบบเลือกตั้งออนไลน์ (Online Voting System) พัฒนาด้วย PHP และ MySQL เหมาะสำหรับสถาบันการศึกษา องค์กร หรือหน่วยงานที่ต้องการจัดการเลือกตั้งผ่านเว็บไซต์อย่างปลอดภัยและใช้งานง่าย

## คุณสมบัติ

- **สมัครสมาชิกและเข้าสู่ระบบ** สำหรับผู้ใช้และผู้ดูแลระบบ
- **จัดการบทบาท (Role)** แยกสิทธิ์ผู้ดูแลและผู้ใช้ทั่วไป
- **จัดการการเลือกตั้ง** เพิ่ม แก้ไข ลบ รายการเลือกตั้ง
- **จัดการผู้สมัคร** เพิ่มและแก้ไขข้อมูลผู้สมัครในแต่ละการเลือกตั้ง
- **ลงคะแนน** ผู้ใช้สามารถเลือกผู้สมัครในรายการเลือกตั้งที่เปิดอยู่
- **ดูผลการเลือกตั้ง** แสดงผลคะแนนและรายงาน
- **ประกาศข่าวสาร** ผู้ดูแลสามารถแจ้งข่าวให้ผู้ใช้ทราบ
- **ระบบข้อความ** ผู้ใช้ติดต่อผู้ดูแลและรับการตอบกลับ
- **จัดการไฟล์** อัปโหลดและจัดการไฟล์ที่เกี่ยวข้องกับการเลือกตั้ง
- **สำรอง/กู้คืนข้อมูล** สำรองและกู้คืนฐานข้อมูลและไฟล์จากแผงผู้ดูแล
- **รองรับมือถือ** ดีไซน์สวยงามด้วย Bootstrap 5

## โครงสร้างโฟลเดอร์

- `admin/` - ส่วนจัดการหลังบ้าน (Admin)
- `includes/` - ไฟล์ PHP ที่ใช้ร่วมกัน (เช่น การเชื่อมต่อฐานข้อมูล)
- `css/`, `js/`, `bootstrap523/` - ไฟล์สำหรับหน้าตาเว็บไซต์
- `uploads/`, `vote_img/` - ไฟล์และรูปภาพที่อัปโหลด
- `database/`, `sql/`, `backups/` - ไฟล์ฐานข้อมูลและสำรองข้อมูล

## วิธีเริ่มต้นใช้งาน

1. **แตกไฟล์หรือโคลนโปรเจกต์นี้**
2. **นำเข้าไฟล์ฐานข้อมูล**  
   ใช้ไฟล์ SQL ที่อยู่ในโฟลเดอร์ `database/` หรือในโปรเจกต์
3. **ตั้งค่าการเชื่อมต่อฐานข้อมูล**  
   แก้ไขไฟล์ `includes/config.php` ให้ตรงกับข้อมูลฐานข้อมูลของคุณ
4. **วางโปรเจกต์ในโฟลเดอร์เว็บเซิร์ฟเวอร์**  
   เช่น `htdocs` สำหรับ XAMPP
5. **เปิดใช้งานผ่านเบราว์เซอร์**  
   ไปที่ `http://localhost/php-login-main291/`

## ความต้องการระบบ

- PHP 7.4 ขึ้นไป
- MySQL หรือ MariaDB
- Web Server (แนะนำ Apache)
- Composer (ถ้ามีการใช้ไลบรารีเพิ่มเติม)

## ความปลอดภัย

- รหัสผ่านถูกเข้ารหัสด้วย `password_hash`
- มีระบบป้องกัน CSRF ในฟอร์มต่าง ๆ
- ตรวจสอบและกรองข้อมูลผู้ใช้

## License

โปรเจกต์นี้จัดทำเพื่อการศึกษา สามารถนำไปปรับใช้และพัฒนาต่อได้ตามความเหมาะสม

---

*README นี้สร้างอัตโนมัติ สามารถแก้ไขเพิ่มเติมได้ตามต้องการ*
