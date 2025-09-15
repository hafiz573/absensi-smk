<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: index.php'); exit; }
$nama_admin = $_SESSION['nama_lengkap'];
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard Admin</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>Dashboard Admin</h1>
    <p>Selamat Datang, <?=htmlspecialchars($nama_admin)?></p>
    <ul>
        <li><a href="admin_kelas.php">Kelola Kelas</a></li>
        <li><a href="admin_users.php">Kelola Users (Admin/Guru/Siswa)</a></li>
        <li><a href="rekap_pdf.php?kelas_id=semua&bulan=<?=date('Y-m')?>">Cetak Rekap Semua Kelas (Bulan Ini)</a></li>
    </ul>
    <br><a href="logout.php">Logout</a>
</div>
</body>
</html>
