<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'guru')) {
    header('Location: index.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

// Siswa self-absen (single)
if (isset($_POST['hadir']) && isset($_POST['siswa_id']) && $_SESSION['role']==='siswa') {
    $siswa_id = (int)$_POST['siswa_id'];
    $tanggal = date('Y-m-d');
    $jam_masuk = date('H:i:s');

    // cek sudah absen
    $stmt = $koneksi->prepare('SELECT COUNT(*) FROM absensi WHERE siswa_id=:sid AND tanggal=:tgl');
    $stmt->execute([':sid'=>$siswa_id, ':tgl'=>$tanggal]);
    if ($stmt->fetchColumn() > 0) {
        echo 'Anda sudah melakukan absensi hari ini. <a href="siswa.php">Kembali</a>';
        exit;
    }

    $ins = $koneksi->prepare('INSERT INTO absensi (siswa_id,tanggal,jam_masuk,status) VALUES (:sid,:tgl,:jam,:st)');
    $ins->execute([':sid'=>$siswa_id, ':tgl'=>$tanggal, ':jam'=>$jam_masuk, ':st'=>'hadir']);
    echo 'Absensi berhasil dicatat. <a href="siswa.php">Kembali</a>';
    exit;
}

// Guru bulk input: siswa_id[] dan status[]
if ($_SESSION['role'] === 'guru' && isset($_POST['siswa_id']) && is_array($_POST['siswa_id'])) {
    $tanggal = date('Y-m-d');
    $jam_masuk = date('H:i:s');
    $k = 0;
    $ins = $koneksi->prepare('INSERT INTO absensi (siswa_id,tanggal,jam_masuk,status) VALUES (:sid,:tgl,:jam,:st)');
    $check = $koneksi->prepare('SELECT COUNT(*) FROM absensi WHERE siswa_id=:sid AND tanggal=:tgl');
    foreach ($_POST['siswa_id'] as $idx => $sid) {
        $sid = (int)$sid;
        $status = $_POST['status'][$idx] ?? 'alpa';
        $check->execute([':sid'=>$sid, ':tgl'=>$tanggal]);
        if ($check->fetchColumn() > 0) continue; // skip jika sudah ada
        $ins->execute([':sid'=>$sid, ':tgl'=>$tanggal, ':jam'=>$jam_masuk, ':st'=>$status]);
        $k++;
    }
    echo "Absensi guru: $k siswa diproses. <a href='guru.php'>Kembali</a>";
    exit;
}

header('Location: index.php');
