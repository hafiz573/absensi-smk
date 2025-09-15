<?php
date_default_timezone_set('Asia/Jakarta'); // Tambahkan ini di baris paling atas
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header('Location: index.php'); exit;
}
$nama_siswa = $_SESSION['nama_lengkap'];
$user_id = $_SESSION['user_id'];

$stmt = $koneksi->prepare('SELECT id FROM siswa WHERE user_id = :u LIMIT 1');
$stmt->execute([':u'=>$user_id]);
$s = $stmt->fetch();
$siswa_id = $s ? $s['id'] : null;

// Cek status absensi hari ini
$status_hari_ini = null;
if ($siswa_id) {
    $stmt = $koneksi->prepare('SELECT status, jam_masuk FROM absensi WHERE siswa_id = :sid AND tanggal = CURDATE()');
    $stmt->execute([':sid'=>$siswa_id]);
    $absen = $stmt->fetch();
    if ($absen) {
        $status_hari_ini = $absen['status'] . ' (Jam: ' . $absen['jam_masuk'] . ')';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard Siswa</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="center-card">
    <h2>Selamat Datang, <?=htmlspecialchars($nama_siswa)?>!</h2>
    <p>Silakan lakukan absensi untuk hari ini.</p>
    <?php if ($status_hari_ini): ?>
        <div class="info">Status Absensi Hari Ini: <b><?=htmlspecialchars($status_hari_ini)?></b></div>
    <?php else: ?>
        <form id="absenForm" action="proses_absen.php" method="POST">
            <label>Nama Lengkap
                <input type="text" name="nama" value="<?=htmlspecialchars($nama_siswa)?>" readonly>
            </label>
            <label>Waktu Saat Ini
                <input type="text" id="waktu" name="waktu" readonly>
            </label>
            <input type="hidden" name="siswa_id" value="<?=htmlspecialchars($siswa_id)?>">
            <button type="submit" name="hadir">Catat Kehadiran</button>
        </form>
    <?php endif; ?>
    <br>
    <a href="logout.php">Logout</a>
</div>
<script src="js/script.js"></script>
</body>
</html>
