<?php
require_once('../../vendor/tecnickcom/tcpdf/tcpdf.php');

class PDFReport extends TCPDF {
    public function Header() {
        // Logo
        $image_file = '../../assets/images/logo.png';
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 10, 30);
        }
        
        // Set font
        $this->SetFont('thsarabun', 'B', 20);
        
        // Title
        $this->Cell(0, 15, 'ระบบเลือกตั้งออนไลน์', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Line break
        $this->Ln(20);
    }

    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('thsarabun', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'หน้า '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

class ReportGenerator {
    private $pdf;
    private $objCon;
    
    public function __construct($objCon) {
        $this->objCon = $objCon;
        
        // สร้าง PDF object
        $this->pdf = new PDFReport(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // ตั้งค่าเอกสาร
        $this->pdf->SetCreator(PDF_CREATOR);
        $this->pdf->SetAuthor('ระบบเลือกตั้งออนไลน์');
        $this->pdf->SetTitle('รายงานผลการเลือกตั้ง');
        
        // ตั้งค่าขอบกระดาษ
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // ตั้งค่าการแบ่งหน้าอัตโนมัติ
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // ตั้งค่าฟอนต์
        $this->pdf->SetFont('thsarabun', '', 14);
    }
    
    public function generateElectionReport($electionId) {
        // เพิ่มหน้าใหม่
        $this->pdf->AddPage();
        
        // ดึงข้อมูลการเลือกตั้ง
        $sql = "SELECT * FROM voting WHERE vote_id = ?";
        $stmt = $this->objCon->prepare($sql);
        $stmt->bind_param("i", $electionId);
        $stmt->execute();
        $election = $stmt->get_result()->fetch_assoc();
        
        if (!$election) {
            return false;
        }
        
        // หัวข้อรายงาน
        $this->pdf->SetFont('thsarabun', 'B', 18);
        $this->pdf->Cell(0, 10, 'รายงานผลการเลือกตั้ง: ' . $election['vote_name'], 0, 1, 'C');
        $this->pdf->Ln(5);
        
        // ข้อมูลทั่วไป
        $this->pdf->SetFont('thsarabun', '', 14);
        $this->pdf->Cell(0, 10, 'วันที่: ' . date('d/m/Y', strtotime($election['date'])), 0, 1);
        $this->pdf->Cell(0, 10, 'เวลา: ' . $election['start_time'] . ' - ' . $election['end_time'], 0, 1);
        $this->pdf->Ln(5);
        
        // ดึงข้อมูลผู้สมัคร
        $sql = "SELECT c.*, COUNT(v.id) as vote_count 
                FROM candidates c 
                LEFT JOIN votes v ON c.candidate_id = v.candidate_id 
                WHERE c.vote_id = ? 
                GROUP BY c.candidate_id 
                ORDER BY vote_count DESC";
        $stmt = $this->objCon->prepare($sql);
        $stmt->bind_param("i", $electionId);
        $stmt->execute();
        $candidates = $stmt->get_result();
        
        // สร้างตารางผลการเลือกตั้ง
        $this->pdf->SetFont('thsarabun', 'B', 16);
        $this->pdf->Cell(0, 10, 'ผลการเลือกตั้ง', 0, 1, 'L');
        
        // หัวตาราง
        $this->pdf->SetFont('thsarabun', 'B', 14);
        $this->pdf->Cell(20, 10, 'ลำดับ', 1, 0, 'C');
        $this->pdf->Cell(40, 10, 'หมายเลข', 1, 0, 'C');
        $this->pdf->Cell(80, 10, 'ชื่อผู้สมัคร', 1, 0, 'C');
        $this->pdf->Cell(40, 10, 'คะแนน', 1, 1, 'C');
        
        // ข้อมูลในตาราง
        $this->pdf->SetFont('thsarabun', '', 14);
        $i = 1;
        while ($row = $candidates->fetch_assoc()) {
            $this->pdf->Cell(20, 10, $i, 1, 0, 'C');
            $this->pdf->Cell(40, 10, $row['candidate_number'], 1, 0, 'C');
            $this->pdf->Cell(80, 10, $row['description'], 1, 0, 'L');
            $this->pdf->Cell(40, 10, $row['vote_count'], 1, 1, 'C');
            $i++;
        }
        
        // สรุปผล
        $this->pdf->Ln(10);
        $sql = "SELECT COUNT(DISTINCT user_id) as total_voters FROM votes WHERE vote_id = ?";
        $stmt = $this->objCon->prepare($sql);
        $stmt->bind_param("i", $electionId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $this->pdf->SetFont('thsarabun', 'B', 14);
        $this->pdf->Cell(0, 10, 'สรุปผลการเลือกตั้ง', 0, 1);
        $this->pdf->SetFont('thsarabun', '', 14);
        $this->pdf->Cell(0, 10, 'จำนวนผู้มาใช้สิทธิ์: ' . $result['total_voters'] . ' คน', 0, 1);
        
        return $this->pdf;
    }
    
    public function generateOverallReport($startDate = null, $endDate = null) {
        // เพิ่มหน้าใหม่
        $this->pdf->AddPage();
        
        // หัวข้อรายงาน
        $this->pdf->SetFont('thsarabun', 'B', 18);
        $this->pdf->Cell(0, 10, 'รายงานสรุปการเลือกตั้งทั้งหมด', 0, 1, 'C');
        if ($startDate && $endDate) {
            $this->pdf->SetFont('thsarabun', '', 14);
            $this->pdf->Cell(0, 10, 'ระหว่างวันที่ ' . date('d/m/Y', strtotime($startDate)) . 
                                   ' ถึง ' . date('d/m/Y', strtotime($endDate)), 0, 1, 'C');
        }
        $this->pdf->Ln(5);
        
        // สถิติรวม
        $where = "";
        if ($startDate && $endDate) {
            $where = " WHERE date BETWEEN ? AND ?";
        }
        
        $sql = "SELECT 
                COUNT(*) as total_elections,
                (SELECT COUNT(*) FROM votes) as total_votes,
                (SELECT COUNT(DISTINCT user_id) FROM votes) as total_voters
                FROM voting" . $where;
                
        if ($startDate && $endDate) {
            $stmt = $this->objCon->prepare($sql);
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            $stats = $stmt->get_result()->fetch_assoc();
        } else {
            $stats = $this->objCon->query($sql)->fetch_assoc();
        }
        
        $this->pdf->SetFont('thsarabun', 'B', 16);
        $this->pdf->Cell(0, 10, 'สถิติรวม', 0, 1);
        $this->pdf->SetFont('thsarabun', '', 14);
        $this->pdf->Cell(0, 10, 'จำนวนการเลือกตั้งทั้งหมด: ' . $stats['total_elections'] . ' ครั้ง', 0, 1);
        $this->pdf->Cell(0, 10, 'จำนวนผู้มาใช้สิทธิ์ทั้งหมด: ' . $stats['total_voters'] . ' คน', 0, 1);
        $this->pdf->Cell(0, 10, 'จำนวนคะแนนเสียงทั้งหมด: ' . $stats['total_votes'] . ' คะแนน', 0, 1);
        
        return $this->pdf;
    }
    
    public function output($filename = 'report.pdf') {
        // Output the PDF
        $this->pdf->Output($filename, 'I');
    }
} 