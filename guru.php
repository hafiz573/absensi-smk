<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') { header('Location: index.php'); exit; }
$nama_guru = $_SESSION['nama_lengkap'];

$kelas = $koneksi->query('SELECT * FROM kelas')->fetchAll();

$selected_kelas = $_GET['kelas_id'] ?? null;
$siswa_list = [];
if ($selected_kelas) {
    $stmt = $koneksi->prepare('SELECT s.id, u.nama_lengkap FROM siswa s JOIN users u ON s.user_id=u.id WHERE s.kelas_id = :k ORDER BY u.nama_lengkap');
    $stmt->execute([':k'=>$selected_kelas]);
    $siswa_list = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard Guru</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>Dashboard Guru</h1>
    <p>Selamat Datang, <?=htmlspecialchars($nama_guru)?></p>
    <hr>
    <h3>Input Absensi Siswa (Hadir/Sakit/Alpa)</h3>
    <form method="GET" action="guru.php">
        <label>Pilih Kelas
            <select name="kelas_id" onchange="this.form.submit()">
                <option value="">-- Pilih --</option>
                <?php foreach ($kelas as $k): ?>
                    <option value="<?=$k['id']?>" <?=($selected_kelas==$k['id'])? 'selected' : ''?>><?=htmlspecialchars($k['nama_kelas'])?></option>
                <?php endforeach;?>
            </select>
        </label>
    </form>

    <?php if ($selected_kelas): ?>
        <form method="POST" action="proses_absen.php">
            <input type="hidden" name="_kelas_id" value="<?=htmlspecialchars($selected_kelas)?>">
            <table class="table">
                <thead><tr><th>No</th><th>Nama Siswa</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($siswa_list as $i => $s): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?=htmlspecialchars($s['nama_lengkap'])?></td>
                            <td>
                                <input type="hidden" name="siswa_id[]" value="<?= $s['id'] ?>">
                                <select name="status[]">
                                    <option value="hadir">Hadir</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="alpa">Alpa</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit">Simpan Absensi</button>
        </form>
    <?php endif; ?>

    <hr>
    <h3>Rekap Absensi Harian</h3>
    <form action="rekap_pdf.php" method="GET" target="_blank">
        <label>Pilih Kelas:
            <select name="kelas_id">
                <?php foreach ($kelas as $k): ?>
                    <option value="<?=$k['id']?>"><?=htmlspecialchars($k['nama_kelas'])?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Pilih Tanggal:<input type="date" name="tanggal" required></label>
        <button type="submit">Cetak PDF</button>
    </form>

    <br><a href="logout.php">Logout</a>
</div>
</body>
</html>
