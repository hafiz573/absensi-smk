<?php
require 'koneksi.php';
require 'fpdf/fpdf.php';

if (!isset($_SESSION['role'])) { header('Location: index.php'); exit; }

$bulan_tahun = $_GET['bulan'] ?? date('Y-m');
$kelas_id = $_GET['kelas_id'] ?? 'semua';
list($tahun, $bulan) = explode('-', $bulan_tahun);

function namaBulan($m) { return date('F', mktime(0,0,0,$m,1)); }

class PDF extends FPDF {
    function Header() {
        global $kelas_nama, $bulan, $tahun;
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Rekap Absensi Siswa',0,1,'C');
        $this->SetFont('Arial','',10);
        $txt = 'Bulan: '.namaBulan((int)$bulan).' '.$tahun;
        $txt .= ' | Tanggal: '.date('d-m-Y');
        if (!empty($kelas_nama)) $txt .= ' | Kelas: ' . $kelas_nama;
        $this->Cell(0,8, $txt,0,1,'C');
        $this->Ln(3);
        $this->SetFont('Arial','B',8);
        $this->Cell(10,6,'No',1);
        $this->Cell(60,6,'Nama Siswa',1);
        $this->Cell(20,6,'Hadir',1,0,'C');
        $this->Cell(20,6,'Sakit',1,0,'C');
        $this->Cell(20,6,'Alpa',1,0,'C');
        $this->Ln();
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo(),0,0,'C');
    }
}

$kelas_nama = '';
if ($kelas_id !== 'semua') {
    $q = $koneksi->prepare("SELECT nama_kelas FROM kelas WHERE id=:id");
    $q->execute([':id'=>$kelas_id]);
    $d = $q->fetch();
    $kelas_nama = $d ? $d['nama_kelas'] : '';
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',8);

if ($kelas_id === 'semua') {
    $stmt = $koneksi->query("SELECT s.id, u.nama_lengkap FROM siswa s JOIN users u ON s.user_id=u.id ORDER BY u.nama_lengkap");
} else {
    $stmt = $koneksi->prepare("SELECT s.id, u.nama_lengkap FROM siswa s JOIN users u ON s.user_id=u.id WHERE s.kelas_id=:k ORDER BY u.nama_lengkap");
    $stmt->execute([':k'=>$kelas_id]);
}
$siswa = $stmt->fetchAll();

$no=1;
foreach ($siswa as $s) {
    $sid = $s['id'];
    $hadir = $koneksi->prepare("SELECT COUNT(*) FROM absensi WHERE siswa_id=:sid AND status='hadir' AND MONTH(tanggal)=:m AND YEAR(tanggal)=:y");
    $hadir->execute([':sid'=>$sid, ':m'=>$bulan, ':y'=>$tahun]); $h = $hadir->fetchColumn();
    $sakit = $koneksi->prepare("SELECT COUNT(*) FROM absensi WHERE siswa_id=:sid AND status='sakit' AND MONTH(tanggal)=:m AND YEAR(tanggal)=:y");
    $sakit->execute([':sid'=>$sid, ':m'=>$bulan, ':y'=>$tahun]); $s2 = $sakit->fetchColumn();
    $alpa = $koneksi->prepare("SELECT COUNT(*) FROM absensi WHERE siswa_id=:sid AND status='alpa' AND MONTH(tanggal)=:m AND YEAR(tanggal)=:y");
    $alpa->execute([':sid'=>$sid, ':m'=>$bulan, ':y'=>$tahun]); $a = $alpa->fetchColumn();

    $pdf->SetFont('Arial','',8);
    $pdf->Cell(10,6,$no++,1);
    $pdf->Cell(60,6,$s['nama_lengkap'],1);
    $pdf->Cell(20,6,$h,1,0,'C');
    $pdf->Cell(20,6,$s2,1,0,'C');
    $pdf->Cell(20,6,$a,1,0,'C');
    $pdf->Ln();
}

$pdf->Output('D', "Rekap_Absensi_{$kelas_id}_{$bulan}_{$tahun}.pdf");
